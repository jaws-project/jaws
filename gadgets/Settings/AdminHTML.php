<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SettingsAdminHTML extends Jaws_GadgetHTML
{
    /**
     */
    function Admin()
    {
        if ($this->GetPermission('BasicSettings')) {
            return $this->BasicSettings();
        } elseif ($this->GetPermission('AdvancedSettings')) {
            return $this->AdvancedSettings();
        } elseif ($this->GetPermission('MetaSettings')) {
            return $this->MetaSettings();
        } elseif ($this->GetPermission('MailSettings')) {
            return $this->MailSettings();
        } elseif ($this->GetPermission('FTPSettings')) {
            return $this->FTPSettings();
        }

        $this->CheckPermission('ProxySettings');
        return $this->ProxySettings();
    }

    /**
     * Builds the settings Sidebar
     *
     * @access  private
     * @param   string  $action  Current action
     * @return  string  XHTML of sidebar
     */
    function SideBar($action)
    {
        $actions = array('Basic', 'Advanced', 'Meta', 'Mail', 'FTP', 'Proxy');
        if (!in_array($action, $actions)) {
            $action = 'Basic';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Sidebar.php';
        $sidebar = new Jaws_Widgets_Sidebar('settings');

        if ($this->GetPermission('BasicSettings')) {
            $sidebar->AddOption('Basic', _t('SETTINGS_BASIC_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=Admin');
        }

        if ($this->GetPermission('AdvancedSettings')) {
            $sidebar->AddOption('Advanced', _t('SETTINGS_ADVANCED_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=AdvancedSettings');
        }

        if ($this->GetPermission('MetaSettings')) {
            $sidebar->AddOption('Meta', _t('SETTINGS_META_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=MetaSettings');
        }

        if ($this->GetPermission('MailSettings')) {
            $sidebar->AddOption('Mail', _t('SETTINGS_MAIL_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=MailSettings');
        }

        if ($this->GetPermission('FTPSettings')) {
            $sidebar->AddOption('FTP', _t('SETTINGS_FTP_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=FTPSettings');
        }

        if ($this->GetPermission('ProxySettings')) {
            $sidebar->AddOption('Proxy', _t('SETTINGS_PROXY_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=ProxySettings');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }

    /**
     * Display general/basic settings form
     *
     * @access  public
     * @return  string  Template content
     */
    function BasicSettings()
    {
        $this->CheckPermission('BasicSettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Basic'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitBasicForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // site status
        $site_status =& Piwi::CreateWidget('Combo', 'site_status');
        $site_status->setID('site_status');
        $tpl->SetBlock('settings/item');
        $site_status->AddOption(_t('GLOBAL_DISABLED'), 'disabled');
        $site_status->AddOption(_t('GLOBAL_ENABLED'), 'enabled');
        $site_status->SetDefault($GLOBALS['app']->Registry->Get('/config/site_status'));
        $tpl->SetVariable('field-name', 'site_status');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_STATUS'));
        $tpl->SetVariable('field', $site_status->Get());
        $tpl->ParseBlock('settings/item');

        // Site name
        $tpl->SetBlock('settings/item');
        $sitename =& Piwi::CreateWidget('Entry', 'site_name', $GLOBALS['app']->Registry->Get('/config/site_name'));
        $sitename->setSize(40);
        $sitename->setID('site_name');
        $tpl->SetVariable('field-name', 'site_name');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_NAME'));
        $tpl->SetVariable('field', $sitename->Get());
        $tpl->ParseBlock('settings/item');

        // Site slogan
        $tpl->SetBlock('settings/item');
        $sitedesc =& Piwi::CreateWidget('Entry', 'site_slogan',
                                        $GLOBALS['app']->Registry->Get('/config/site_slogan'));
        $sitedesc->setSize(40);
        $sitedesc->setID('site_slogan');
        $tpl->SetVariable('field-name', 'site_slogan');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_SLOGAN'));
        $tpl->SetVariable('field', $sitedesc->Get());
        $tpl->ParseBlock('settings/item');

        // site language
        $lang =& Piwi::CreateWidget('Combo', 'site_language');
        $lang->setID('site_language');
        $tpl->SetBlock('settings/item');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($GLOBALS['app']->Registry->Get('/config/site_language'));
        $tpl->SetVariable('field-name', 'site_language');
        $tpl->SetVariable('label', _t('SETTINGS_DEFAULT_SITE_LANGUAGE'));
        $tpl->SetVariable('field', $lang->Get());
        $tpl->ParseBlock('settings/item');

        // admin language
        $lang =& Piwi::CreateWidget('Combo', 'admin_language');
        $lang->setID('admin_language');
        $tpl->SetBlock('settings/item');
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($GLOBALS['app']->Registry->Get('/config/admin_language'));
        $tpl->SetVariable('field-name', 'admin_language');
        $tpl->SetVariable('label', _t('SETTINGS_ADMIN_LANGUAGE'));
        $tpl->SetVariable('field', $lang->Get());
        $tpl->ParseBlock('settings/item');

        // Main gadget
        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $installedgadgets = $jms->GetGadgetsList(null, true, true, null, true);
        $gdt =& Piwi::CreateWidget('Combo', 'main_gadget');
        $gdt->setID('main_gadget');

        $tpl->SetBlock('settings/item');
        $gdt->AddOption(_t('GLOBAL_NOGADGET'),'');
        foreach ($installedgadgets as $g => $tg) {
            $gdt->AddOption($tg['name'], $g);
        }
        $gdt->SetDefault($GLOBALS['app']->Registry->Get('/config/main_gadget'));
        $tpl->SetVariable('field-name', 'main_gadget');
        $tpl->SetVariable('label', _t('SETTINGS_MAIN_GADGET'));
        $tpl->SetVariable('field', $gdt->Get());
        $tpl->ParseBlock('settings/item');

        // Site comment
        $tpl->SetBlock('settings/item');
        $sitecomment =& Piwi::CreateWidget('TextArea', 'site_comment',
                                           $GLOBALS['app']->Registry->Get('/config/site_comment'));
        $sitecomment->SetRows(4);
        $sitecomment->SetStyle('width: 252px;');
        $sitecomment->setID('site_comment');
        $tpl->SetVariable('field-name', 'site_comment');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_COMMENT'));
        $tpl->SetVariable('field', $sitecomment->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

    /**
     * Display advanced settings
     *
     * @access  public
     * @return  string  Template content
     */
    function AdvancedSettings()
    {
        $this->CheckPermission('AdvancedSettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Advanced'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitAdvancedForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        $model = $GLOBALS['app']->LoadGadget('Settings', 'AdminModel');
        // Date Format
        $date_format =& Piwi::CreateWidget('Combo', 'date_format');
        $date_format->setID('date_format');
        $date_format->SetStyle('width: 250px;');
        $dtfmts = $model->GetDateFormatList();
        foreach ($dtfmts as $k => $v) {
            $date_format->AddOption($v, $k);
        }
        $date_format->SetDefault($GLOBALS['app']->Registry->Get('/config/date_format'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'date_format');
        $tpl->SetVariable('label', _t('SETTINGS_DATE_FORMAT'));
        $tpl->SetVariable('field', $date_format->Get());
        $tpl->ParseBlock('settings/item');

        // Calendar
        $date_calendar =& Piwi::CreateWidget('Combo', 'calendar_type');
        $date_calendar->setID('calendar_type');
        $calendars = $model->GetCalendarList();
        foreach ($calendars as $calendar) {
            $date_calendar->AddOption($calendar, $calendar);
        }
        $current_cal = $GLOBALS['app']->Registry->Get('/config/calendar_type');
        if (Jaws_Error::isError($current_cal)) {
            $date_calendar->SetDefault('Gregorian');
        } else {
            $date_calendar->SetDefault($current_cal);
        }
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'calendar_type');
        $tpl->SetVariable('label', _t('SETTINGS_CALENDAR_TYPE'));
        $tpl->SetVariable('field', $date_calendar->Get());
        $tpl->ParseBlock('settings/item');

        // calendar language
        $lang =& Piwi::CreateWidget('Combo', 'calendar_language');
        $lang->setID('calendar_language');
        $tpl->SetBlock('settings/item');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetStyle('width: 250px;');
        $lang->SetDefault($GLOBALS['app']->Registry->Get('/config/calendar_language'));
        $tpl->SetVariable('field-name', 'calendar_language');
        $tpl->SetVariable('label', _t('SETTINGS_CALENDAR_LANGUAGE'));
        $tpl->SetVariable('field', $lang->Get());
        $tpl->ParseBlock('settings/item');

        // Use gravatar? or local images?
        $use_gravatar = $GLOBALS['app']->Registry->Get('/config/use_gravatar');
        $gravatar =& Piwi::CreateWidget('Combo', 'use_gravatar');
        $gravatar->setID('use_gravatar');
        $gravatar->AddOption(_t('GLOBAL_YES'), 'yes');
        $gravatar->AddOption(_t('GLOBAL_NO'), 'no');
        $gravatar->SetDefault($use_gravatar);
        $gravatar->AddEvent(ON_CHANGE, 'javascript: toggleGR();');
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'use_gravatar');
        $tpl->SetVariable('label', _t('SETTINGS_USE_GRAVATAR'));
        $tpl->SetVariable('field', $gravatar->Get());
        $tpl->ParseBlock('settings/item');

        // Gravatar rating
        $gravatar =& Piwi::CreateWidget('Combo', 'gravatar_rating');
        $gravatar->setID('gravatar_rating');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_G'), 'G');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_PG'), 'PG');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_R'), 'R');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_X'), 'X');
        $gravatar->SetStyle('width: 250px;');
        $gravatar->SetDefault($GLOBALS['app']->Registry->Get('/config/gravatar_rating'));
        $gravatar->SetEnabled($use_gravatar == 'yes');
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'gravatar_rating');
        $tpl->SetVariable('label', '<a href="http://www.gravatar.com/rating.php" rel="external" target="_blank">' . _t('SETTINGS_GRAVATAR') . '</a>');
        $tpl->SetVariable('field', $gravatar->Get());
        $tpl->ParseBlock('settings/item');

        // comments site wide
        $comments =& Piwi::CreateWidget('Combo', 'allow_comments');
        $comments->setID('allow_comments');
        $comments->AddOption(_t('GLOBAL_YES'), 'true');
        $comments->AddOption(_t('SETTINGS_ALLOW_COMMENTS_RESTRICTED'), 'restricted');
        $comments->AddOption(_t('GLOBAL_NO'), 'false');
        $comments->SetDefault($GLOBALS['app']->Registry->Get('/config/allow_comments'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'allow_comments');
        $tpl->SetVariable('label', _t('SETTINGS_ALLOW_COMMENTS'));
        $tpl->SetVariable('field', $comments->Get());
        $tpl->ParseBlock('settings/item');

        // show view site icon on CP
        $viewSite =& Piwi::CreateWidget('Combo', 'show_viewsite');
        $viewSite->setID('show_viewsite');
        $viewSite->AddOption(_t('GLOBAL_YES'), 'true');
        $viewSite->AddOption(_t('GLOBAL_NO'), 'false');
        $viewSite->SetDefault($GLOBALS['app']->Registry->Get('/config/show_viewsite'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'show_viewsite');
        $tpl->SetVariable('label', _t('SETTINGS_SHOW_VIEWSITE'));
        $tpl->SetVariable('field', $viewSite->Get());
        $tpl->ParseBlock('settings/item');

        // default title
        $defaultTitle =& Piwi::CreateWidget('Combo', 'title_separator');
        $defaultTitle->setID('title_separator');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_SLASH'), '/');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_PIPE'), '|');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_DASH'), '-');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_DOUBLECOLON'), '::');
        $defaultTitle->SetDefault($GLOBALS['app']->Registry->Get('/config/title_separator'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'title_separator');
        $tpl->SetVariable('label', _t('SETTINGS_TITLE_SEPARATOR'));
        $tpl->SetVariable('field', $defaultTitle->Get());
        $tpl->ParseBlock('settings/item');

        // editor
        $editorCombo =& Piwi::CreateWidget('Combo', 'editor');
        $editorCombo->setID('editor');
        $editors = $model->GetEditorList();
        foreach ($editors as $k => $v) {
            $editorCombo->AddOption($v, $k);
        }
        $editorCombo->SetStyle('width: 250px;');
        $editorCombo->SetDefault($GLOBALS['app']->Registry->Get('/config/editor'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'editor');
        $tpl->SetVariable('label', _t('SETTINGS_EDITOR'));
        $tpl->SetVariable('field', $editorCombo->Get());
        $tpl->ParseBlock('settings/item');

        //Time Zones
        $timezone =& Piwi::CreateWidget('Combo', 'timezone');
        $timezone->setID('timezone');
        $timezones = $model->GetTimeZonesList();
        foreach($timezones as $k => $v) {
            $timezone->AddOption($v, $k);
        }
        $timezone->SetStyle('direction: ltr; width: 250px;');
        $timezone->SetDefault($GLOBALS['app']->Registry->Get('/config/timezone'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'timezone');
        $tpl->SetVariable('label', _t('GLOBAL_TIMEZONE'));
        $tpl->SetVariable('field', $timezone->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');

        return $tpl->Get();
    }

    /**
     * Display meta settings form
     *
     * @access  public
     * @return  string  Template content
     */
    function MetaSettings()
    {
        $this->CheckPermission('MetaSettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Meta'));
        $tpl->SetVariable('custom_meta', _t('SETTINGS_META_CUSTOM'));

        // Add Button
        $addButton =& Piwi::CreateWidget('Button', 'add', _t('SETTINGS_META_ADD_CUSTOM'), STOCK_ADD);
        $addButton->AddEvent(ON_CLICK, 'javascript:addCustomMeta();');
        $tpl->SetVariable('addButton', $addButton->Get());

        // Save Button
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript:submitMetaForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Site description
        $tpl->SetBlock('settings/item');
        $sitedesc =& Piwi::CreateWidget('TextArea', 'site_description',
                                        $GLOBALS['app']->Registry->Get('/config/site_description'));
        $sitedesc->SetRows(5);
        $sitedesc->setStyle('width: 24em;');
        $sitedesc->setID('site_description');
        $tpl->SetVariable('field-name', 'site_description');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_DESCRIPTION'));
        $tpl->SetVariable('field', $sitedesc->Get());
        $tpl->ParseBlock('settings/item');

        // Site keywords
        $tpl->SetBlock('settings/item');
        $sitekeys =& Piwi::CreateWidget('Entry', 'site_keywords',
                                        $GLOBALS['app']->Registry->Get('/config/site_keywords'));
        $sitekeys->setID('site_keywords');
        $sitekeys->setStyle('direction: ltr; width: 24em;');
        $tpl->SetVariable('field-name', 'site_keywords');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_KEYWORDS'));
        $tpl->SetVariable('field', $sitekeys->Get());
        $tpl->ParseBlock('settings/item');

        // Site author
        $tpl->SetBlock('settings/item');
        $author =& Piwi::CreateWidget('Entry', 'site_author', $GLOBALS['app']->Registry->Get('/config/site_author'));
        $author->setID('site_author');
        $author->setStyle('width: 24em;');
        $tpl->SetVariable('field-name', 'site_author');
        $tpl->SetVariable('label',_t('SETTINGS_SITE_AUTHOR'));
        $tpl->SetVariable('field',$author->Get());
        $tpl->ParseBlock('settings/item');

        // License
        $tpl->SetBlock('settings/item');
        $license =& Piwi::CreateWidget('Entry', 'site_license', $GLOBALS['app']->Registry->Get('/config/site_license'));
        $license->setID('site_license');
        $license->setStyle('width: 24em;');
        $tpl->SetVariable('field-name', 'site_license');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_LICENSE'));
        $tpl->SetVariable('field', $license->Get());
        $tpl->ParseBlock('settings/item');

        // Copyright
        $tpl->SetBlock('settings/item');
        $copyright =& Piwi::CreateWidget('Entry', 'copyright', $GLOBALS['app']->Registry->Get('/config/copyright'));
        $copyright->setID('copyright');
        $copyright->setStyle('width: 24em;');
        $tpl->SetVariable('field-name', 'copyright');
        $tpl->SetVariable('label', _t('SETTINGS_COPYRIGHT'));
        $tpl->SetVariable('field', $copyright->Get());
        $tpl->ParseBlock('settings/item');

        // Custom META
        $Metas = @unserialize($GLOBALS['app']->Registry->Get('/config/custom_meta'));
        if (!empty($Metas)) {
            foreach ($Metas as $meta) {
                $tpl->SetBlock('settings/custom');
                $tpl->SetVariable('label', _t('SETTINGS_META_CUSTOM'));
                // name
                $nMeta =& Piwi::CreateWidget('Entry', 'meta_name', $meta[0]);
                $nMeta->setClass('meta-name');
                $tpl->SetVariable('name', $nMeta->Get());
                // value
                $vMeta =& Piwi::CreateWidget('Entry', 'meta_value', $meta[1]);
                $vMeta->setClass('meta-value');
                $tpl->SetVariable('value', $vMeta->Get());
                $tpl->ParseBlock('settings/custom');
            }
        }

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

    /**
     * Display general/mailserver settings form
     *
     * @access  public
     * @return  string  Template content
     */
    function MailSettings()
    {
        $this->CheckPermission('MailSettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Mail'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitMailSettingsForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Mailer
        $mailer =& Piwi::CreateWidget('Combo', 'mailer');
        $mailer->setID('mailer');
        $mailer->AddOption(_t('GLOBAL_DISABLED'), 'DISABLED');
        $mailer->AddOption('PHP mail()', 'phpmail');
        $mailer->AddOption('sendmail',   'sendmail');
        $mailer->AddOption('SMTP',       'smtp');
        $mailer->AddEvent(ON_CHANGE, 'javascript: changeMailer();');
        $mailer->SetDefault($GLOBALS['app']->Registry->Get('/network/mailer'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'mailer');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_MAILER'));
        $tpl->SetVariable('field', $mailer->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // Site email
        $tpl->SetBlock('settings/item');
        $siteEmail =& Piwi::CreateWidget('Entry', 'gate_email', $GLOBALS['app']->Registry->Get('/network/gate_email'));
        $siteEmail->setID('gate_email');
        $siteEmail->setSize(24);
        $siteEmail->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'gate_email');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_GATE_EMAIL'));
        $tpl->SetVariable('field', $siteEmail->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // Email title
        $tpl->SetBlock('settings/item');
        $emailName =& Piwi::CreateWidget('Entry', 'gate_title', $GLOBALS['app']->Registry->Get('/network/gate_title'));
        $emailName->setID('gate_title');
        $emailName->setSize(24);
        $tpl->SetVariable('field-name', 'gate_title');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_GATE_TITLE'));
        $tpl->SetVariable('field', $emailName->Get());
        $tpl->ParseBlock('settings/item');

        // SMTP Verification
        $smtpVrfy =& Piwi::CreateWidget('Combo', 'smtp_vrfy');
        $smtpVrfy->setID('smtp_vrfy');
        $smtpVrfy->AddOption(_t('GLOBAL_NO'),  'false');
        $smtpVrfy->AddOption(_t('GLOBAL_YES'), 'true');
        $smtpVrfy->SetDefault($GLOBALS['app']->Registry->Get('/network/smtp_vrfy'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'smtp_vrfy');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_VRFY'));
        $tpl->SetVariable('field', $smtpVrfy->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // sendmail path
        $tpl->SetBlock('settings/item');
        $sendmailPath =& Piwi::CreateWidget('Entry', 'sendmail_path', $GLOBALS['app']->Registry->Get('/network/sendmail_path'));
        $sendmailPath->setID('sendmail_path');
        $sendmailPath->setSize(24);
        $sendmailPath->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'sendmail_path');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SENDMAIL_PATH'));
        $tpl->SetVariable('field', $sendmailPath->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // SMTP Host
        $tpl->SetBlock('settings/item');
        $smtpHost =& Piwi::CreateWidget('Entry', 'smtp_host', $GLOBALS['app']->Registry->Get('/network/smtp_host'));
        $smtpHost->setID('smtp_host');
        $smtpHost->setSize(24);
        $smtpHost->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'smtp_host');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_HOST'));
        $tpl->SetVariable('field', $smtpHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // SMTP Port
        $tpl->SetBlock('settings/item');
        $smtpPort =& Piwi::CreateWidget('Entry', 'smtp_port', $GLOBALS['app']->Registry->Get('/network/smtp_port'));
        $smtpPort->setID('smtp_port');
        $smtpPort->setSize(10);
        $smtpPort->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'smtp_port');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_PORT'));
        $tpl->SetVariable('field', $smtpPort->Get());
        $tpl->ParseBlock('settings/item');

        // SMTP Auth
        $smtpAuth =& Piwi::CreateWidget('Combo', 'smtp_auth');
        $smtpAuth->setID('smtp_auth');
        $smtpAuth->AddOption(_t('GLOBAL_NO'),  'false');
        $smtpAuth->AddOption(_t('GLOBAL_YES'), 'true');
        $smtpAuth->SetDefault($GLOBALS['app']->Registry->Get('/network/smtp_auth'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'smtp_auth');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_AUTH'));
        $tpl->SetVariable('field', $smtpAuth->Get());
        $tpl->ParseBlock('settings/item');

        // SMTPAuth Username
        $tpl->SetBlock('settings/item');
        $smtpUser =& Piwi::CreateWidget('Entry', 'smtp_user', $GLOBALS['app']->Registry->Get('/network/smtp_user'));
        $smtpUser->setID('smtp_user');
        $smtpUser->setSize(24);
        $smtpUser->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'smtp_user');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_USER'));
        $tpl->SetVariable('field', $smtpUser->Get());
        $tpl->ParseBlock('settings/item');

        // SMTPAuth Password
        $tpl->SetBlock('settings/item');
        $smtpPass =& Piwi::CreateWidget('PasswordEntry', 'smtp_pass', '');
        $smtpPass->setID('smtp_pass');
        $smtpPass->setSize(24);
        $smtpPass->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'smtp_pass');
        $tpl->SetVariable('label', _t('SETTINGS_MAIL_SMTP_PASS'));
        $tpl->SetVariable('field', $smtpPass->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

    /**
     * Display general/ftpserver settings form
     *
     * @access  public
     * @return  string  Template content
     */
    function FTPSettings()
    {
        $this->CheckPermission('FTPSettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('FTP'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitFTPSettingsForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Use Enabled?
        $useFTP =& Piwi::CreateWidget('Combo', 'ftp_enabled');
        $useFTP->setID('ftp_enabled');
        $useFTP->AddOption(_t('GLOBAL_NO'),  'false');
        $useFTP->AddOption(_t('GLOBAL_YES'), 'true');
        $useFTP->SetDefault($GLOBALS['app']->Registry->Get('/network/ftp_enabled'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_enabled');
        $tpl->SetVariable('label', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('field', $useFTP->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // FTP Host
        $tpl->SetBlock('settings/item');
        $ftpHost =& Piwi::CreateWidget('Entry', 'ftp_host', $GLOBALS['app']->Registry->Get('/network/ftp_host'));
        $ftpHost->setID('ftp_host');
        $ftpHost->setSize(24);
        $ftpHost->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'ftp_host');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_HOST'));
        $tpl->SetVariable('field', $ftpHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // FTP Port
        $tpl->SetBlock('settings/item');
        $ftpPort =& Piwi::CreateWidget('Entry', 'ftp_port', $GLOBALS['app']->Registry->Get('/network/ftp_port'));
        $ftpPort->setID('ftp_port');
        $ftpPort->setSize(10);
        $ftpPort->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'ftp_port');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_PORT'));
        $tpl->SetVariable('field', $ftpPort->Get());
        $tpl->ParseBlock('settings/item');

        // FTP mode (active/passive)
        $ftpMode =& Piwi::CreateWidget('Combo', 'ftp_mode');
        $ftpMode->setID('ftp_mode');
        $ftpMode->AddOption(_t('SETTINGS_FTP_MODE_ACTIVE'),  'active');
        $ftpMode->AddOption(_t('SETTINGS_FTP_MODE_PASSIVE'), 'passive');
        $ftpMode->SetDefault($GLOBALS['app']->Registry->Get('/network/ftp_mode'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'ftp_mode');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_MODE'));
        $tpl->SetVariable('field', $ftpMode->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Username
        $tpl->SetBlock('settings/item');
        $ftpUser =& Piwi::CreateWidget('Entry', 'ftp_user', $GLOBALS['app']->Registry->Get('/network/ftp_user'));
        $ftpUser->setID('ftp_user');
        $ftpUser->setSize(24);
        $ftpUser->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'ftp_user');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_USER'));
        $tpl->SetVariable('field', $ftpUser->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Password
        $tpl->SetBlock('settings/item');
        $ftpPass =& Piwi::CreateWidget('PasswordEntry', 'ftp_pass', '');
        $ftpPass->setID('ftp_pass');
        $ftpPass->setSize(24);
        $ftpPass->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'ftp_pass');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_PASS'));
        $tpl->SetVariable('field', $ftpPass->Get());
        $tpl->ParseBlock('settings/item');

        // FTP Root Path
        $tpl->SetBlock('settings/item');
        $ftpRoot =& Piwi::CreateWidget('Entry', 'ftp_root', $GLOBALS['app']->Registry->Get('/network/ftp_root'));
        $ftpRoot->setID('ftp_root');
        $ftpRoot->setSize(24);
        $ftpRoot->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'ftp_root');
        $tpl->SetVariable('label', _t('SETTINGS_FTP_ROOT'));
        $tpl->SetVariable('field', $ftpRoot->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

    /**
     * Display general/proxy settings form
     *
     * @access  public
     * @return  string  Template content
     */
    function ProxySettings()
    {
        $this->CheckPermission('ProxySettings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Settings/templates/');
        $tpl->Load('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Proxy'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitProxySettingsForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Use Enabled?
        $useProxy =& Piwi::CreateWidget('Combo', 'proxy_enabled');
        $useProxy->setID('proxy_enabled');
        $useProxy->AddOption(_t('GLOBAL_NO'),  'false');
        $useProxy->AddOption(_t('GLOBAL_YES'), 'true');
        $useProxy->SetDefault($GLOBALS['app']->Registry->Get('/network/proxy_enabled'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'proxy_enabled');
        $tpl->SetVariable('label', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('field', $useProxy->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // Proxy Host
        $tpl->SetBlock('settings/item');
        $proxyHost =& Piwi::CreateWidget('Entry', 'proxy_host', $GLOBALS['app']->Registry->Get('/network/proxy_host'));
        $proxyHost->setID('proxy_host');
        $proxyHost->setSize(24);
        $proxyHost->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'proxy_host');
        $tpl->SetVariable('label', _t('SETTINGS_PROXY_HOST'));
        $tpl->SetVariable('field', $proxyHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // Proxy Port
        $tpl->SetBlock('settings/item');
        $proxyPort =& Piwi::CreateWidget('Entry', 'proxy_port', $GLOBALS['app']->Registry->Get('/network/proxy_port'));
        $proxyPort->setID('proxy_port');
        $proxyPort->setSize(10);
        $proxyPort->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'proxy_port');
        $tpl->SetVariable('label', _t('SETTINGS_PROXY_PORT'));
        $tpl->SetVariable('field', $proxyPort->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Auth
        $proxyAuth =& Piwi::CreateWidget('Combo', 'proxy_auth');
        $proxyAuth->setID('proxy_auth');
        $proxyAuth->AddOption(_t('GLOBAL_NO'),  'false');
        $proxyAuth->AddOption(_t('GLOBAL_YES'), 'true');
        $proxyAuth->SetDefault($GLOBALS['app']->Registry->Get('/network/proxy_auth'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'proxy_auth');
        $tpl->SetVariable('label', _t('SETTINGS_PROXY_AUTH'));
        $tpl->SetVariable('field', $proxyAuth->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Username
        $tpl->SetBlock('settings/item');
        $proxyUser =& Piwi::CreateWidget('Entry', 'proxy_user', $GLOBALS['app']->Registry->Get('/network/proxy_user'));
        $proxyUser->setID('proxy_user');
        $proxyUser->setSize(24);
        $proxyUser->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'proxy_user');
        $tpl->SetVariable('label', _t('SETTINGS_PROXY_USER'));
        $tpl->SetVariable('field', $proxyUser->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Password
        $tpl->SetBlock('settings/item');
        $proxyPass =& Piwi::CreateWidget('PasswordEntry', 'proxy_pass', '');
        $proxyPass->setID('proxy_pass');
        $proxyPass->setSize(24);
        $proxyPass->setStyle('direction: ltr');
        $tpl->SetVariable('field-name', 'proxy_pass');
        $tpl->SetVariable('label', _t('SETTINGS_PROXY_PASS'));
        $tpl->SetVariable('field', $proxyPass->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}