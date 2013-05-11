<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(MainMenu)
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DefaultAction()
    {
        return $this->MainMenu();
    }

    /**
     * Displays the Control Panel main menu
     *
     * @access  public
     * @return  string  XHTML menu template content
     */
    function MainMenu()
    {
        $this->AjaxMe('script.js');

        // Load the template
        $tpl = $this->gadget->loadTemplate('MainMenu.html');

        $gadgetsections = array();
        $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'AdminModel');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        unset($gadgets['ControlPanel']);

        foreach ($gadgets as $gadget => $gInfo) {
            if ($this->gadget->GetPermission('default_admin', '', $gadget)) {
                $section = $gInfo['section'];
                if (!isset($gadgetsections[$section])) {
                    $gadgetsections[$section] = array();
                }

                $gadgetsections[$section][] = array('name'  => $gadget,
                                                    'tname' => $gInfo['name'],
                                                    'desc'  => $gInfo['description']);
            }
        }

        if ($this->gadget->registry->fetch('show_viewsite', 'Settings') == 'true') {
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
                } else {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['name'].'/images/logo.png'));
                    $tpl->SetVariable('url', BASE_SCRIPT . '?gadget='.$gadget['name']);
                }
                $tpl->ParseBlock('main/item');
            }
            $tpl->ParseBlock('main');
        }

        if ($this->gadget->GetPermission('default_admin', '', 'Components')) {
            $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'AdminModel');
            //Count non-installed gadgets..
            $noninstalled = $cmpModel->GetGadgetsList(null, false);
            //Count out date gadgets..
            $nonupdated   = $cmpModel->GetGadgetsList(null, true, false);
            if ((count($noninstalled) + count($nonupdated)) > 0) {
                $tpl->SetBlock('sidebar');
                if (count($noninstalled) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('notify-title', _t('COMPONENTS_GADGETS_NOTINSTALLED'));
                    $tpl->SetVariable('notify_desc', _t('COMPONENTS_GADGETS_NOTINSTALLED_DESC'));
                    foreach ($noninstalled as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=InstallGadget&amp;comp='. $key);
                        $tpl->SetVariable('install', _t('COMPONENTS_INSTALL'));
                        $tpl->ParseBlock('sidebar/notifications/item');
                    }
                    $tpl->ParseBlock('sidebar/notifications');
                }

                if (count($nonupdated) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('notify-title', _t('COMPONENTS_GADGETS_OUTDATED'));
                    $tpl->SetVariable('notify_desc', _t('COMPONENTS_GADGETS_OUTDATED_DESC'));
                    foreach ($nonupdated as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=UpgradeGadget&amp;comp='. $key);
                        $tpl->SetVariable('install', _t('COMPONENTS_UPDATE'));
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
     * @access  public
     * @param   string  $message If a message is needed
     * @return  string  XHTML template of the login form
     */
    function ShowLoginForm($message = '')
    {
        $use_crypt = $this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true';
        if ($use_crypt) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $use_crypt = $JCrypt->Init();
        }

        // Init layout
        $GLOBALS['app']->Layout->Load('gadgets/ControlPanel/templates/',
                                      'Login.html');
        $ltpl =& $GLOBALS['app']->Layout->_Template;
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/ControlPanel/resources/public.css');
        $ltpl->SetVariable('admin_script', BASE_SCRIPT);
        $ltpl->SetVariable('control-panel', _t('CONTROLPANEL_NAME'));

        $request =& Jaws_Request::getInstance();
        $reqpost = $request->get(array('username', 'authtype', 'remember', 'usecrypt', 'redirect_to'), 'post');
        if (is_null($reqpost['authtype'])) {
            $reqpost['authtype'] = $request->get('authtype', 'get');
        }

        // referrer page link
        $redirect_to = is_null($reqpost['redirect_to'])?
            bin2hex(Jaws_Utils::getRequestURL()) : $reqpost['redirect_to'];
        $ltpl->SetVariable('redirect_to', $redirect_to);

        if ($use_crypt) {
            $GLOBALS['app']->Layout->AddScriptLink('libraries/js/rsa.lib.js');
            $ltpl->SetBlock('layout/onsubmit');
            $ltpl->ParseBlock('layout/onsubmit');
            $ltpl->SetBlock('layout/encryption');
            $ltpl->SetVariable('modulus',  $JCrypt->math->bin2int($JCrypt->pub_key->getModulus()));
            $ltpl->SetVariable('exponent', $JCrypt->math->bin2int($JCrypt->pub_key->getExponent()));
            $ltpl->ParseBlock('layout/encryption');

            // usecrypt
            $ltpl->SetBlock('layout/usecrypt');
            $ltpl->SetVariable('lbl_usecrypt', _t('GLOBAL_LOGIN_SECURE'));
            if (empty($reqpost['username']) || !empty($reqpost['usecrypt'])) {
                $ltpl->SetBlock('layout/usecrypt/selected');
                $ltpl->ParseBlock('layout/usecrypt/selected');
            }
            $ltpl->ParseBlock('layout/usecrypt');
        }

        $ltpl->SetVariable('legend_title', _t('CONTROLPANEL_LOGIN_TITLE'));
        $ltpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $ltpl->SetVariable('username', $reqpost['username']);
        $ltpl->SetVariable('lbl_password', _t('GLOBAL_PASSWORD'));

        $authtype = $this->gadget->registry->fetch('authtype', 'Users');
        if (!is_null($reqpost['authtype']) || $authtype !== 'Default') {
            $authtype = is_null($reqpost['authtype'])? $authtype : $reqpost['authtype'];
            $ltpl->SetBlock('layout/authtype');
            $ltpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
            foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
                $ltpl->SetBlock('layout/authtype/item');
                $ltpl->SetVariable('method', $method);
                if ($method == $authtype) {
                    $ltpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $ltpl->SetVariable('selected', '');
                }
                $ltpl->ParseBlock('layout/authtype/item');
            }
            $ltpl->ParseBlock('layout/authtype');
        }

        // remember
        $ltpl->SetBlock('layout/remember');
        $ltpl->SetVariable('lbl_remember', _t('GLOBAL_REMEMBER_ME'));
        if (!empty($reqpost['remember'])) {
            $ltpl->SetBlock('layout/remember/selected');
            $ltpl->ParseBlock('layout/remember/selected');
        }
        $ltpl->ParseBlock('layout/remember');

        //captcha
        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'HTML');
        $mPolicy->loadCaptcha($ltpl, 'layout', 'login');

        $ltpl->SetVariable('login', _t('GLOBAL_LOGIN'));
        $ltpl->SetVariable('back', _t('CONTROLPANEL_LOGIN_BACK_TO_SITE'));

        if (!empty($message)) {
            $ltpl->SetBlock('layout/message');
            $ltpl->SetVariable('message', $message);
            $ltpl->ParseBlock('layout/message');
        }

        return $GLOBALS['app']->Layout->Get();
    }

    /**
     * Terminates Control Panel session and redirects to website
     *
     * @access  public
     */
    function Logout()
    {
        $GLOBALS['app']->Session->Logout();
        Jaws_Header::Location(BASE_SCRIPT);
    }

    /**
     * Returns downloadable backup file
     *
     * @access  public
     * @return void
     */
    function Backup()
    {
        $this->gadget->CheckPermission('Backup');
        $tmpDir = sys_get_temp_dir();
        $domain = preg_replace("/^(www.)|(:{$_SERVER['SERVER_PORT']})$|[^a-z0-9-.]/", '', strtolower($_SERVER['HTTP_HOST']));
        $nameArchive = $domain . '-' . date('Y-m-d') . '.tar.gz';
        $pathArchive = $tmpDir . DIRECTORY_SEPARATOR . $nameArchive;

        //Dump database data
        $dbFileName = 'dbdump.xml';
        $dbFilePath = $tmpDir . DIRECTORY_SEPARATOR . $dbFileName;
        $GLOBALS['db']->Dump($dbFilePath);

        $files = array();
        require_once PEAR_PATH. "File/Archive.php"; 
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