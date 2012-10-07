<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanelAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Calls default action(MainMenu)
     *
     * @access public
     * @return string template content
     */
    function DefaultAction()
    {
        return $this->MainMenu();
    }

    /**
     * Displays the Control Panel main menu
     *
     * @access       public
     * @return       template content
     */
    function MainMenu()
    {
        $this->AjaxMe('script.js');

        // Load the template
        $tpl = new Jaws_Template('gadgets/ControlPanel/templates/');
        $tpl->Load('MainMenu.html');

        $gadgetsections = array();
        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadgets = $jms->GetGadgetsList(null, true, true);
        unset($gadgets['ControlPanel']);

        foreach ($gadgets as $gadget => $gInfo) {
            if ($this->GetPermission('default_admin', $gadget)) {
                $section = $gInfo['section'];
                if (!isset($gadgetsections[$section])) {
                    $gadgetsections[$section] = array();
                }

                $gadgetsections[$section][] = array('name'  => $gadget,
                                                    'tname' => $gInfo['name'],
                                                    'desc'  => $gInfo['description']);
            }
        }

        if ($GLOBALS['app']->Registry->Get('/config/show_viewsite') == 'true') {
            $gadgetsections['general'][] = array('name'  => 'Index',
                                                 'tname' => _t('CONTROLPANEL_GENERAL_VIEWSITE'),
                                                 'desc'  => _t('CONTROLPANEL_GENERAL_VIEWSITE'));
        }

        foreach ($gadgetsections as $section  => $gadgets) {
            $tpl->SetBlock('main');
            $tpl->SetVariable('title', _t('GLOBAL_GI_' . strtoupper($section)));
            foreach ($gadgets as $gadget) {
                $tpl->SetBlock('main/item');
                $tpl->SetVariable('name', $gadget['tname']);
                $tpl->SetVariable('desc', $gadget['desc']);
                if ($gadget['name'] === 'Index') {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/ControlPanel/images/view_site.png'));
                    $tpl->SetVariable('url', $GLOBALS['app']->getSiteURL('/'));
                    $tpl->SetBlock('main/item/target');
                    $tpl->ParseBlock('main/item/target');
                } else {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['name'].'/images/logo.png'));
                    $tpl->SetVariable('url', BASE_SCRIPT . '?gadget='.$gadget['name']);
                }
                $tpl->ParseBlock('main/item');
            }
            $tpl->ParseBlock('main');
        }

        if ($this->GetPermission('default_admin', 'Jms')) {
            $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
            //Count non-installed gadgets..
            $noninstalled = $jms->GetGadgetsList(null, false);
            //Count out date gadgets..
            $nonupdated   = $jms->GetGadgetsList(null, true, false);
            $jms = null;
            if ((count($noninstalled) + count($nonupdated)) > 0) {
                $tpl->SetBlock('sidebar');
                if (count($noninstalled) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('notify-title', _t('JMS_SIDEBAR_DISABLED_GADGETS'));
                    $tpl->SetVariable('notify_desc', _t('JMS_SIDEBAR_GADGETS_WAITING'));
                    foreach ($noninstalled as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT . '?gadget=Jms&amp;action=EnableGadget&amp;comp='.
                                          $key . '&amp;location=sidebar');
                        $tpl->SetVariable('install', _t('JMS_INSTALL'));
                        $tpl->ParseBlock('sidebar/notifications/item');
                    }
                    $tpl->ParseBlock('sidebar/notifications');
                }

                if (count($nonupdated) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('notify-title', _t('JMS_SIDEBAR_NOTUPDATED_GADGETS'));
                    $tpl->SetVariable('notify_desc', _t('JMS_SIDEBAR_NOTUPDATED_SUGESTION'));
                    foreach ($nonupdated as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT . '?gadget=Jms&amp;action=UpdateGadget&amp;comp='.
                                          $key . '&amp;location=sidebar');
                        $tpl->SetVariable('install', _t('JMS_UPDATE'));
                        $tpl->ParseBlock('sidebar/notifications/item');
                    }
                    $tpl->ParseBlock('sidebar/notifications');
                }
                $tpl->ParseBlock('sidebar');
            }
        }

        return $tpl->Get();
    }

    /**
     * Get HTML login form
     *
     * @access public
     * @param  string  $message If a message is needed
     * @return string  HTML of the form
     */
    function ShowLoginForm($message = '')
    {
        $use_crypt = ($GLOBALS['app']->Registry->Get('/crypt/enabled') == 'true')? true : false;
        if ($use_crypt) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $use_crypt = $JCrypt->Init();
        }

        $tpl = new Jaws_Template('gadgets/ControlPanel/templates/');
        $tpl->Load('Login.html');
        $tpl->SetBlock('login');

        $tpl->SetVariable('BASE_URL', $GLOBALS['app']->GetSiteURL('/'.BASE_SCRIPT));
        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('site-name', $GLOBALS['app']->Registry->Get('/config/site_name'));
        $tpl->SetVariable('site-slogan', $GLOBALS['app']->Registry->Get('/config/site_slogan'));
        $tpl->SetVariable('control-panel', _t('CONTROLPANEL_NAME'));

        $request =& Jaws_Request::getInstance();
        $reqpost = $request->get(array('username', 'auth_method', 'remember', 'usecrypt'), 'post');
        if (empty($reqpost['auth_method'])) {
            $reqpost['auth_method'] = $request->get('auth_method', 'get');
        }

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->setID('login_form');
        $form->shouldValidate($use_crypt, $use_crypt);

        $redirectTo = '';
        if (isset($_SERVER['QUERY_STRING'])) {
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $queryString = $xss->parse($_SERVER['QUERY_STRING']);
            if (!empty($queryString)) {
                $redirectTo  = '?' . $queryString;
                $redirectTo  = $xss->filter($redirectTo);
            }
        }

        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'ControlPanel'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'Login'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'redirect_to', $redirectTo));

        if ($use_crypt) {
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'modulus',  $JCrypt->math->bin2int($JCrypt->pub_key->getModulus())));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'exponent', $JCrypt->math->bin2int($JCrypt->pub_key->getExponent())));
        }

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('CONTROLPANEL_LOGIN_TITLE'));
        $fieldset->SetDirection('vertical');
        $fieldset->SetStyle('width: 100%;');

        $usernameEntry =& Piwi::CreateWidget('Entry', 'username', (string) $reqpost['username']);
        $usernameEntry->SetTitle(_t('GLOBAL_USERNAME'));
        $fieldset->Add($usernameEntry);
        $tpl->SetVariable('loadObject', $usernameEntry->GetID());

        $passEntry =& Piwi::CreateWidget('PasswordEntry', 'password', '');
        $passEntry->SetTitle(_t('GLOBAL_PASSWORD'));
        $fieldset->Add($passEntry);

        $auth_method = $GLOBALS['app']->Registry->Get('/config/auth_method');
        if (!empty($reqpost['auth_method']) || $auth_method !== 'Default') {
            $authmethod =& Piwi::CreateWidget('Combo', 'auth_method');
            $authmethod->SetTitle(_t('CONTROLPANEL_AUTH_METHOD'));
            foreach ($GLOBALS['app']->GetAuthMethods() as $method) {
                $authmethod->AddOption($method, $method);
            }
            if (!empty($reqpost['auth_method'])) {
                $authmethod->SetDefault($reqpost['auth_method']);
            } else {
                $authmethod->SetDefault($auth_method);
            }
            $fieldset->Add($authmethod);
        }

        $rememberMe =& Piwi::CreateWidget('CheckButtons', 'remember');
        $rememberMe->setID('remember');
        $rememberMe->setColumns(1);
        $rememberMe->AddOption(_t('GLOBAL_REMEMBER_ME'), 'true');
        if (!empty($reqpost['remember'])) {
            $rememberMe->SetDefault('true');
        }
        $fieldset->Add($rememberMe);

        if ($use_crypt) {
            $useCrypt =& Piwi::CreateWidget('CheckButtons', 'usecrypt');
            $useCrypt->setID('usecrypt');
            $useCrypt->setColumns(1);
            $useCrypt->AddOption(_t('GLOBAL_LOGIN_SECURE'), 'true');
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $useCrypt->SetDefault('true');
            }
            $fieldset->Add($useCrypt);
        }

        $submit =& Piwi::CreateWidget('Button', 'loginButton', _t('GLOBAL_LOGIN'), STOCK_OK);
        $submit->SetSubmit();
        $fieldset->Add($submit);

        $form->Add($fieldset);

        $tpl->SetVariable('form', $form->Get());
        $tpl->SetVariable('back', _t('CONTROLPANEL_LOGIN_BACK_TO_SITE'));

        $prefix = '.' . strtolower(_t('GLOBAL_LANG_DIRECTION'));
        if ($prefix !== '.rtl') {
            $prefix = '';
        }

        $hLinks = $GLOBALS['app']->Layout->AddHeadLink(
                                    'gadgets/ControlPanel/resources/public.css',
                                    'stylesheet', 'text/css', '',
                                    null, false, '', true);
        $sLinks = $GLOBALS['app']->Layout->AddScriptLink('libraries/js/rsa.lib.js', 'text/javascript', true);
        $tmpArray = array();
        $headContent = $GLOBALS['app']->Layout->GetHeaderContent($hLinks, $sLinks, $tmpArray, $tmpArray);

        $tpl->SetBlock('login/head');
        $tpl->SetVariable('ELEMENT', $headContent);
        $tpl->ParseBlock('login/head');

        if (!empty($message)) {
            $tpl->SetBlock('login/message');
            $tpl->SetVariable('message', $message);
            $tpl->ParseBlock('login/message');
        }

        $tpl->ParseBlock('login');

        return $tpl->Get();
    }

    /**
     * Terminates Control Panel session and redirects to website
     *
     * @access public
     */
    function Logout()
    {
        $GLOBALS['app']->Session->Logout();
        Jaws_Header::Location(BASE_SCRIPT);
    }

    /**
     * Returns downloadable backup file
     *
     * @access public
     * @return string template content
     */
    function Backup()
    {
        $this->CheckPermission('Backup');
        $tmpDir = sys_get_temp_dir();
        $domain = preg_replace("/^(www.)|(:{$_SERVER['SERVER_PORT']})$|[^a-z0-9-.]/", '', strtolower($_SERVER['HTTP_HOST']));
        $nameArchive = $domain . '-' . date('Y-m-d') . '.tar.gz';
        $pathArchive = $tmpDir . DIRECTORY_SEPARATOR . $nameArchive;

        //Dump database data
        $dbFileName = 'dbdump.xml';
        $dbFilePath = $tmpDir . DIRECTORY_SEPARATOR . $dbFileName;
        $GLOBALS['db']->Dump($dbFilePath);

        $files = array();
        require_once "File/Archive.php"; 
        $files[] = File_Archive::read(JAWS_DATA);
        $files[] = File_Archive::read($dbFilePath , $dbFileName);
        File_Archive::extract($files, File_Archive::toArchive($pathArchive, File_Archive::toFiles()));
        Jaws_Utils::Delete($dbFilePath);

        // browser must download file from server instead of cache
        header("Expires: 0");
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        // force download dialog
        header("Content-Type: application/force-download");
        // set data type, size and filename
        header("Content-Disposition: attachment; filename=\"$nameArchive\"");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: '.@filesize($pathArchive));
        @readfile($pathArchive);
        Jaws_Utils::Delete($pathArchive);
    }
}
