<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class PolicyAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Calls default admin action (IPBlocking)
     *
     * @access  public
     * @return  string  Template content
     */
    function Admin()
    {
        if ($this->GetPermission('IPBlocking')) {
            return $this->IPBlocking();
        } elseif ($this->GetPermission('AgentBlocking')) {
            return $this->AgentBlocking();
        } elseif ($this->GetPermission('Encryption')) {
            return $this->Encryption();
        } elseif ($this->GetPermission('AntiSpam')) {
            return $this->AntiSpam();
        }

        $this->CheckPermission('AdvancedPolicies');
        return $this->AdvancedPolicies();
    }

    /**
     * Display the sidebar
     *
     * @access  public
     * @param   string  $action Selected Action
     * @return  template content
     */
    function SideBar($action)
    {
        $actions = array('IPBlocking', 'AgentBlocking', 'Encryption', 'AntiSpam',
                         'AdvancedPolicies');
        if (!in_array($action, $actions)) {
            $action = 'IPBlocking';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Sidebar.php';
        $sidebar = new Jaws_Widgets_Sidebar('policy');

        if ($this->GetPermission('IPBlocking')) {
            $sidebar->AddOption('IPBlocking', _t('POLICY_IP_BLOCKING'), 
                                BASE_SCRIPT . '?gadget=Policy&amp;action=IPBlocking',
                                'images/stock/stop.png');
        }
        if ($this->GetPermission('AgentBlocking')) {
            $sidebar->AddOption('AgentBlocking', _t('POLICY_AGENT_BLOCKING'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AgentBlocking',
                                'images/stock/stop.png');
        }
        if ($this->GetPermission('Encryption')) {
            $sidebar->AddOption('Encryption', _t('POLICY_ENCRYPTION'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=Encryption',
                                'gadgets/Policy/images/encryption.png');
        }
        if ($this->GetPermission('AntiSpam')) {
            $sidebar->AddOption('AntiSpam', _t('POLICY_ANTISPAM'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AntiSpam',
                                'gadgets/Policy/images/antispam.png');
        }
        if ($this->GetPermission('AdvancedPolicies')) {
            $sidebar->AddOption('AdvancedPolicies', _t('POLICY_ADVANCED_POLICIES'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AdvancedPolicies',
                                'gadgets/Policy/images/policies.png');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }

    /**
     * Returns an array with all the blocked IP ranges available
     *
     * @access  public
     * @param   int     $offset  offset of data needed
     * @return  array   Array of blocked IPs
     */
    function GetBlockedIPRanges($offset = null)
    {
        $model  = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $ipRanges = $model->GetBlockedIPs(12, $offset);
        if (Jaws_Error::IsError($ipRanges)) {
            return array();
        }

        $newData = array();
        foreach ($ipRanges as $ipRange) {
            $ipData = array();
            $ipData['from_ip'] = long2ip($ipRange['from_ip']);
            $ipData['to_ip']   = long2ip($ipRange['to_ip']);

            $actions = '';
            if ($this->GetPermission('ManageIPs')) {
                $ipWidget =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                                  "javascript: editIPRange(this, '".$ipRange['id']."');",
                                                  STOCK_EDIT);
                $actions.= $ipWidget->Get().'&nbsp;';

                $ipWidget =& Piwi::CreateWidget('Link',
                                                  _t('GLOBAL_DELETE', _t('POLICY_IP_RANGE')),
                                                  "javascript: deleteIPRange(this, '".$ipRange['id']."');",
                                                  STOCK_DELETE);
                $actions.= $ipWidget->Get();
            }
            $ipData['actions'] = $actions;
            $newData[] = $ipData;
        }
        return $newData;
    }

    /**
     * Returns the Blocked IPs Datagrid
     *
     * @access  public
     * @return  XHTML content
     */
    function IPsDatagrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $totalIPs = $model->GetTotalOfBlockedIPs();

        $grid =& Piwi::CreateWidget('DataGrid', array(), null);
        $grid->SetID('blocked_ips_datagrid');
        $grid->TotalRows($totalIPs);
        $grid->pageBy(12);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_FROM')));
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_TO'), null, false);
        $column2->SetStyle('width: 120px;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width: 60px;');
        $grid->AddColumn($column3);

        return $grid->Get();
    }

    /**
     * IPBlokcing action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function IPBlocking()
    {
        $this->CheckPermission('IPBlocking');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Policy/templates/');
        $tpl->Load('IPBlocking.html');
        $tpl->SetBlock('ipblocking');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('IPBlocking'));
        $tpl->SetVariable('blocked_ips_datagrid', $this->IPsDatagrid());

        $default = $GLOBALS['app']->Registry->Get('/gadgets/Policy/block_undefined_ip') == 'true';
        $blockUndefined =& Piwi::CreateWidget('CheckButtons', 'ipblocking');
        $blockUndefined->AddOption(_t('POLICY_IP_BLOCK_UNDEFINED'),
                              'true',
                              'block_undefined_ip',
                              $default);
        $blockUndefined->AddEvent(ON_CLICK, 'javascript: setBlockUndefinedIP();');
        $tpl->SetVariable('enabled_option', $blockUndefined->Get());

        $tpl->SetVariable('legend_title', _t('POLICY_IP_RANGE'));
        $fromIPAddress =& Piwi::CreateWidget('Entry', 'from_ipaddress', '');
        $fromIPAddress->setSize(24);
        $tpl->SetVariable('lbl_from_ipaddress', _t('GLOBAL_FROM'));
        $tpl->SetVariable('from_ipaddress', $fromIPAddress->Get());

        $toIPAddress =& Piwi::CreateWidget('Entry', 'to_ipaddress', '');
        $toIPAddress->setSize(24);
        $tpl->SetVariable('lbl_to_ipaddress', _t('GLOBAL_TO'));
        $tpl->SetVariable('to_ipaddress', $toIPAddress->Get());

        $blocked =& Piwi::CreateWidget('Combo', 'blocked');
        $blocked->SetID('blocked');
        $blocked->setStyle('width: 120px;');
        $blocked->AddOption(_t('GLOBAL_NO'),  0);
        $blocked->AddOption(_t('GLOBAL_YES'), 1);
        $blocked->SetDefault('1');
        $tpl->SetVariable('lbl_blocked', _t('POLICY_BLOCKED'));
        $tpl->SetVariable('blocked', $blocked->Get());

        if ($this->GetPermission('ManageIPs')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, 'javascript: saveIPRange();');
            $tpl->SetVariable('btn_save', $btnSave->Get());

            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());

            $tpl->SetVariable('incompleteFields',     _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
            $tpl->SetVariable('confirmIPRangeDelete', _t('POLICY_RESPONSE_CONFIRM_DELETE_IP'));
        }

        $tpl->ParseBlock('ipblocking');

        return $tpl->Get();
    }

    /**
     * Returns an array with all the blocked agents
     *
     * @access  public
     * @param   int     $offset  offset of data needed
     * @return  array   Array of blocked agents
     */
    function GetBlockedAgents($offset = 0)
    {
        $model  = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $agents = $model->GetBlockedAgents(12, $offset);
        if (Jaws_Error::IsError($agents)) {
            return array();
        }

        $newData = array();
        foreach ($agents as $agent) {
            $agentData = array();
            $agentData['agent'] = $agent['agent'];

            $actions = '';
            if ($this->GetPermission('ManageAgents')) {
                $ipWidget =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                                  "javascript: editAgent(this, '".$agent['id']."');",
                                                  STOCK_EDIT);
                $actions.= $ipWidget->Get().'&nbsp;';

                $agWidget =& Piwi::CreateWidget('Link',
                                                _t('GLOBAL_DELETE' ,_t('POLICY_AGENT')),
                                                  "javascript: deleteAgent(this, '".$agent['id']."');",
                                                STOCK_DELETE);
                $actions .= $agWidget->Get();
            }
            $agentData['actions'] = $actions;
            $newData[] = $agentData;
        }

        return $newData;
    }

    /**
     * Returns the Blocked Agents datagrid
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AgentsDatagrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $totalAgents = $model->GetTotalOfBlockedAgents();

        $grid =& Piwi::CreateWidget('DataGrid', array(), null);
        $grid->SetID('blocked_agents_datagrid');
        $grid->TotalRows($totalAgents);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('POLICY_AGENT'));
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'));
        $column2->SetStyle('width: 60px; white-space:nowrap;');
        $grid->AddColumn($column2);

        return $grid->Get();
    }

    /**
     * AgentBlokcing action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AgentBlocking()
    {
        $this->CheckPermission('AgentBlocking');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Policy/templates/');
        $tpl->Load('AgentBlocking.html');
        $tpl->SetBlock('agentblocking');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AgentBlocking'));
        $tpl->SetVariable('blocked_agents_datagrid', $this->AgentsDatagrid());

        $default = $GLOBALS['app']->Registry->Get('/gadgets/Policy/block_undefined_agent') == 'true';
        $blockUndefined =& Piwi::CreateWidget('CheckButtons', 'agentblocking');
        $blockUndefined->AddOption(_t('POLICY_AGENT_BLOCK_UNDEFINED'),
                              'true',
                              'block_undefined_agent',
                              $default);
        $blockUndefined->AddEvent(ON_CLICK, 'javascript: setBlockUndefinedAgent();');
        $tpl->SetVariable('enabled_option', $blockUndefined->Get());

        $tpl->SetVariable('legend_title', _t('POLICY_AGENT'));
        $agentEntry =& Piwi::CreateWidget('Entry', 'agent', '');
        $agentEntry->setSize(24);
        $tpl->SetVariable('lbl_agent', _t('POLICY_AGENT'));
        $tpl->SetVariable('agent', $agentEntry->Get());

        $blocked =& Piwi::CreateWidget('Combo', 'blocked');
        $blocked->SetID('blocked');
        $blocked->setStyle('width: 120px;');
        $blocked->AddOption(_t('GLOBAL_NO'),  0);
        $blocked->AddOption(_t('GLOBAL_YES'), 1);
        $blocked->SetDefault('1');
        $tpl->SetVariable('lbl_blocked', _t('POLICY_BLOCKED'));
        $tpl->SetVariable('blocked', $blocked->Get());

        if ($this->GetPermission('ManageAgents')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, 'javascript: saveAgent();');
            $tpl->SetVariable('btn_save', $btnSave->Get());

            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());

            $tpl->SetVariable('incompleteFields',   _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
            $tpl->SetVariable('confirmAgentDelete', _t('POLICY_RESPONSE_CONFIRM_DELETE_AGENT'));
        }

        $tpl->ParseBlock('agentblocking');

        return $tpl->Get();
    }

    /**
     * Encryption action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function Encryption()
    {
        $this->CheckPermission('Encryption');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Policy/templates/');
        $tpl->Load('Encryption.html');
        $tpl->SetBlock('encryption');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('Encryption'));
        $tpl->SetVariable('legend_title', _t('POLICY_ENCRYPTION'));

        $useEncryption =& Piwi::CreateWidget('Combo', 'enabled');
        $useEncryption->setID('enabled');
        $useEncryption->AddOption(_t('GLOBAL_NO'),  'false');
        $useEncryption->AddOption(_t('GLOBAL_YES'), 'true');
        $useEncryption->SetDefault($GLOBALS['app']->Registry->Get('/crypt/enabled'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('enabled', $useEncryption->Get());

        $keyAge =& Piwi::CreateWidget('Combo', 'key_age');
        $keyAge->setID('key_age');
        $keyAge->AddOption(_t('GLOBAL_DATE_MINUTES', 10),   600);
        $keyAge->AddOption(_t('GLOBAL_DATE_HOURS',   1),   3600);
        $keyAge->AddOption(_t('GLOBAL_DATE_HOURS',   5),  18000);
        $keyAge->AddOption(_t('GLOBAL_DATE_DAYS',    1),  86400);
        $keyAge->AddOption(_t('GLOBAL_DATE_WEEKS',   1), 604800);
        $keyAge->SetDefault($GLOBALS['app']->Registry->Get('/crypt/key_age'));
        $keyAge->SetEnabled($this->GetPermission('ManageEncryptionKey'));
        $tpl->SetVariable('lbl_key_age', _t('POLICY_ENCRYPTION_KEY_AGE'));
        $tpl->SetVariable('key_age', $keyAge->Get());

        $keyLen =& Piwi::CreateWidget('Combo', 'key_len');
        $keyLen->setID('key_len');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_128BIT'),  '128');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_256BIT'),  '256');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_512BIT'),  '512');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_1024BIT'), '1024');
        $keyLen->SetDefault($GLOBALS['app']->Registry->Get('/crypt/key_len'));
        $keyLen->SetEnabled($this->GetPermission('ManageEncryptionKey'));
        $tpl->SetVariable('lbl_key_len', _t('POLICY_ENCRYPTION_KEY_LEN'));
        $tpl->SetVariable('key_len', $keyLen->Get());

        $date = $GLOBALS['app']->loadDate();
        $keyStartDate =& Piwi::CreateWidget('Entry', 'key_start_date',
                                            $date->Format((int)$GLOBALS['app']->Registry->Get('/crypt/key_start_date')));
        $keyStartDate->setID('key_start_date');
        $keyStartDate->setSize(30);
        $keyStartDate->SetEnabled(false);
        $tpl->SetVariable('lbl_key_start_date', _t('POLICY_ENCRYPTION_KEY_START_DATE'));
        $tpl->SetVariable('key_start_date', $keyStartDate->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: saveEncryptionSettings();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('encryption');
        return $tpl->Get();
    }

    /**
     * AntiSpam action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AntiSpam()
    {
        $this->CheckPermission('AntiSpam');
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $tpl = new Jaws_Template('gadgets/Policy/templates/');
        $tpl->Load('AntiSpam.html');
        $tpl->SetBlock('AntiSpam');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AntiSpam'));
        $tpl->SetVariable('legend_title', _t('POLICY_ANTISPAM'));

        //Allow duplicate
        $allowDuplicate =& Piwi::CreateWidget('Combo', 'allow_duplicate');
        $allowDuplicate->AddOption(_t('GLOBAL_YES'), 'yes');
        $allowDuplicate->AddOption(_t('GLOBAL_NO'),  'no');
        $allowDuplicate->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Policy/allow_duplicate'));
        $tpl->SetVariable('lbl_allow_duplicate', _t('POLICY_ANTISPAM_ALLOWDUPLICATE'));
        $tpl->SetVariable('allow_duplicate', $allowDuplicate->Get());

        //Filter
        $filters =& Piwi::CreateWidget('Combo', 'filter');
        $filters->AddOption(_t('GLOBAL_DISABLED'), 'DISABLED');
        $fs = $model->GetFilters();
        foreach ($fs as $f) {
            $filters->AddOption($f, $f);
        }
        $filters->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Policy/filter'));
        $tpl->SetVariable('lbl_filter', _t('POLICY_ANTISPAM_FILTER'));
        $tpl->SetVariable('filter', $filters->Get());

        //Captcha
        $captcha =& Piwi::CreateWidget('Combo', 'captcha');
        $captcha->AddOption(_t('GLOBAL_DISABLED'), 'DISABLED');
        $captcha->AddOption(_t('POLICY_ANTISPAM_CAPTCHA_ALWAYS'), 'ALWAYS');
        $captcha->AddOption(_t('POLICY_ANTISPAM_CAPTCHA_ANONYMOUS'), 'ANONYMOUS');
        $captchaValue = $GLOBALS['app']->Registry->Get('/gadgets/Policy/captcha');
        $captcha->SetDefault($captchaValue);
        $captcha->AddEvent(ON_CHANGE, "javascript: toggleCaptcha();");
        $tpl->SetVariable('lbl_captcha', _t('POLICY_ANTISPAM_CAPTCHA'));
        $tpl->SetVariable('captcha', $captcha->Get());

        //Captcha driver
        $captchaDriver =& Piwi::CreateWidget('Combo', 'captcha_driver');
        $dCaptchas = $model->GetCaptchas();
        foreach ($dCaptchas as $dCaptcha) {
            $captchaDriver->AddOption($dCaptcha, $dCaptcha);
        }
        $captchaDriver->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Policy/captcha_driver'));
        if ($captchaValue === 'DISABLED') {
            $captchaDriver->SetEnabled(false);
        }
        $tpl->SetVariable('captcha_driver', $captchaDriver->Get());

        //Email Protector
        $useEmailProtector =& Piwi::CreateWidget('Combo', 'obfuscator');
        $useEmailProtector->AddOption(_t('GLOBAL_DISABLED'), 'DISABLED');
        $os = $model->GetObfuscators();
        foreach ($os as $o) {
            $useEmailProtector->AddOption($o, $o);
        }
        $useEmailProtector->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Policy/obfuscator'));
        $tpl->SetVariable('lbl_obfuscator', _t('POLICY_ANTISPAM_PROTECTEMAIL'));
        $tpl->SetVariable('obfuscator', $useEmailProtector->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: saveAntiSpamSettings();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('AntiSpam');
        return $tpl->Get();
    }

    /**
     * AdvancedPolicies action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function AdvancedPolicies()
    {
        $this->CheckPermission('AntiSpam');
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
        $tpl = new Jaws_Template('gadgets/Policy/templates/');
        $tpl->Load('AdvancedPolicies.html');
        $tpl->SetBlock('AdvancedPolicies');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('AdvancedPolicies'));
        $tpl->SetVariable('legend_title', _t('POLICY_ADVANCED_POLICIES'));

        $complexity =& Piwi::CreateWidget('Combo', 'passwd_complexity');
        $complexity->AddOption(_t('GLOBAL_YES'), 'yes');
        $complexity->AddOption(_t('GLOBAL_NO'),  'no');
        $complexity->SetDefault($GLOBALS['app']->Registry->Get('/policy/passwd_complexity'));
        $tpl->SetVariable('lbl_passwd_complexity', _t('POLICY_PASSWD_COMPLEXITY'));
        $tpl->SetVariable('passwd_complexity', $complexity->Get());

        $badCount =& Piwi::CreateWidget('Combo', 'passwd_bad_count');
        $badCount->setID('passwd_bad_count');
        $badCount->AddOption(_t('GLOBAL_TIMES', 1), '1');
        $badCount->AddOption(_t('GLOBAL_TIMES', 3), '3');
        $badCount->AddOption(_t('GLOBAL_TIMES', 5), '5');
        $badCount->AddOption(_t('GLOBAL_TIMES', 7), '7');
        $badCount->SetDefault($GLOBALS['app']->Registry->Get('/policy/passwd_bad_count'));
        $tpl->SetVariable('lbl_passwd_bad_count', _t('POLICY_PASSWD_BAD_COUNT'));
        $tpl->SetVariable('passwd_bad_count', $badCount->Get());

        $lockedout =& Piwi::CreateWidget('Combo', 'passwd_lockedout_time');
        $lockedout->setID('passwd_lockedout_time');
        $lockedout->AddOption(_t('GLOBAL_DISABLED'), '0');
        $lockedout->AddOption(_t('GLOBAL_DATE_MINUTES',  1),  60);
        $lockedout->AddOption(_t('GLOBAL_DATE_MINUTES',  3), 180);
        $lockedout->AddOption(_t('GLOBAL_DATE_MINUTES',  5), 300);
        $lockedout->AddOption(_t('GLOBAL_DATE_MINUTES', 10), 600);
        $lockedout->AddOption(_t('GLOBAL_DATE_MINUTES', 15), 900);
        $lockedout->SetDefault($GLOBALS['app']->Registry->Get('/policy/passwd_lockedout_time'));
        $tpl->SetVariable('lbl_passwd_lockedout_time', _t('POLICY_PASSWD_LOCKEDOUT_TIME'));
        $tpl->SetVariable('passwd_lockedout_time', $lockedout->Get());

        $maxAge =& Piwi::CreateWidget('Combo', 'passwd_max_age');
        $maxAge->setID('passwd_max_age');
        $maxAge->AddOption(_t('POLICY_PASSWD_RESISTANT'), 0);
        $maxAge->AddOption(_t('GLOBAL_DATE_DAYS',  1),    1);
        $maxAge->AddOption(_t('GLOBAL_DATE_DAYS',  3),    3);
        $maxAge->AddOption(_t('GLOBAL_DATE_WEEKS', 1),    7);
        $maxAge->AddOption(_t('GLOBAL_DATE_WEEKS', 2),   14);
        $maxAge->AddOption(_t('GLOBAL_DATE_MONTH', 1),   30);
        $maxAge->AddOption(_t('GLOBAL_DATE_MONTH', 3),   90);
        $maxAge->SetDefault($GLOBALS['app']->Registry->Get('/policy/passwd_max_age'));
        $tpl->SetVariable('lbl_passwd_max_age', _t('POLICY_PASSWD_MAX_AGE'));
        $tpl->SetVariable('passwd_max_age', $maxAge->Get());

        $minLen =& Piwi::CreateWidget('Combo', 'passwd_min_length');
        $minLen->setID('passwd_min_length');
        $minLen->AddOption('0',   0);
        $minLen->AddOption('3',   3);
        $minLen->AddOption('6',   6);
        $minLen->AddOption('8',   8);
        $minLen->AddOption('10', 10);
        $minLen->AddOption('15', 15);
        $minLen->SetDefault($GLOBALS['app']->Registry->Get('/policy/passwd_min_length'));
        $tpl->SetVariable('lbl_passwd_min_length', _t('POLICY_PASSWD_MIN_LEN'));
        $tpl->SetVariable('passwd_min_length', $minLen->Get());

        $parsingLevel =& Piwi::CreateWidget('Combo', 'xss_parsing_level');
        $parsingLevel->AddOption(_t('POLICY_XSS_PARSING_NORMAL'),   'normal');
        $parsingLevel->AddOption(_t('POLICY_XSS_PARSING_PARANOID'), 'paranoid');
        $parsingLevel->SetDefault($GLOBALS['app']->Registry->Get('/policy/xss_parsing_level'));
        $tpl->SetVariable('lbl_xss_parsing_level', _t('POLICY_XSS_PARSING_LEVEL'));
        $tpl->SetVariable('xss_parsing_level', $parsingLevel->Get());

        $idleTimeout =& Piwi::CreateWidget('Combo', 'session_idle_timeout');
        $idleTimeout->setID('session_idle_timeout');
        $idleTimeout->AddOption(_t('GLOBAL_DATE_MINUTES',  5),  5);
        $idleTimeout->AddOption(_t('GLOBAL_DATE_MINUTES', 10), 10);
        $idleTimeout->AddOption(_t('GLOBAL_DATE_MINUTES', 15), 15);
        $idleTimeout->AddOption(_t('GLOBAL_DATE_MINUTES', 30), 30);
        $idleTimeout->AddOption(_t('GLOBAL_DATE_HOURS',    1), 60);
        $idleTimeout->SetDefault($GLOBALS['app']->Registry->Get('/policy/session_idle_timeout'));
        $tpl->SetVariable('lbl_session_idle_timeout', _t('POLICY_SESSION_IDLE_TIMEOUT'));
        $tpl->SetVariable('session_idle_timeout', $idleTimeout->Get());

        $rememberTimeout =& Piwi::CreateWidget('Combo', 'session_remember_timeout');
        $rememberTimeout->setID('session_remember_timeout');
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_DAYS',   1),   24);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_DAYS',   3),   72);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_WEEKS',  1),  168);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_WEEKS',  2),  336);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_MONTH',  1),  720);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_MONTH',  6), 4320);
        $rememberTimeout->AddOption(_t('GLOBAL_DATE_MONTH', 12), 8640);
        $rememberTimeout->SetDefault($GLOBALS['app']->Registry->Get('/policy/session_remember_timeout'));
        $tpl->SetVariable('lbl_session_remember_timeout', _t('POLICY_SESSION_REMEMBER_TIMEOUT'));
        $tpl->SetVariable('session_remember_timeout', $rememberTimeout->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: saveAdvancedPolicies();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('AdvancedPolicies');
        return $tpl->Get();
    }
}