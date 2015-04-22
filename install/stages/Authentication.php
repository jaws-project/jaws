<?php
/**
 * Authentication Stage
 *
 * @category   Application
 * @package    InstallStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Authentication extends JawsInstallerStage
{
    /**
     * Constructor
     *
     * @access public
     */
    function Installer_Authentication()
    {
        if (!isset($_SESSION['install']['Authentication']) && 
           (!isset($_SESSION['install']['predefined']) || !$_SESSION['install']['predefined']))
        {
            $_SESSION['secure'] = false;
            $_SESSION['customize'] = false;
            $_SESSION['install']['Authentication'] = array(
                'key' => md5(uniqid('installer')) . time() . floor(microtime()*1000)
            );
        }
    }

    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Authentication/templates');
        $tpl->SetBlock('Authentication');

        $tpl->SetVariable('key_path_info', _t('INSTALL_AUTH_PATH_INFO', 'key.txt', INSTALL_PATH));
        $tpl->SetVariable('rsa_security',  _t('INSTALL_AUTH_ENABLE_SECURITY'));
        $tpl->SetVariable('auth_upload',   _t('INSTALL_AUTH_UPLOAD'));
        $tpl->SetVariable('key_file_info', _t('INSTALL_AUTH_KEY_INFO'));
        $tpl->SetVariable('next',          _t('GLOBAL_NEXT'));
        $tpl->SetVariable('key', $_SESSION['install']['Authentication']['key']);
        $tpl->SetVariable('checked_secure', $_SESSION['secure']? 'checked="checked"' : '');
        $tpl->SetVariable('checked_customize', $_SESSION['customize']? 'checked="checked"' : '');
        $tpl->SetVariable('custom_installation', _t('INSTALL_AUTH_CUSTOM_INSTALL'));

        $tpl->ParseBlock('Authentication');
        return $tpl->Get();
    }

    /**
     * Validates any data provided to the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Returns either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Validate()
    {
        if ($_SESSION['install']['predefined']) {
            return true;
        }
 
        $request = Jaws_Request::getInstance();
        $postReq = $request->fetch(array('secure', 'customize'), 'post');
        $_SESSION['secure'] = !empty($postReq['secure']);
        $_SESSION['customize'] = !empty($postReq['customize']);

        // try to entering to secure transformation mode 
        if ($_SESSION['secure'] && (!isset($_SESSION['pub_key']) || empty($_SESSION['pub_key']))) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $pkey = Jaws_Crypt::Generate_RSA_KeyPair(512);
            if (!Jaws_Error::isError($pkey)) {
                $_SESSION['pub_key'] = $pkey['pub_key'];
                $_SESSION['pvt_key'] = $pkey['pvt_key'];
            } else {
                return new Jaws_Error(_t('INSTALL_AUTH_ERROR_RSA_KEY_GENERATION'), 0, JAWS_ERROR_WARNING);
            }
        }

        $key_file = INSTALL_PATH . 'key.txt';
        if (file_exists($key_file)) {
            $key = trim(file_get_contents($key_file));
            if ($key === $_SESSION['install']['Authentication']['key']) {
                _log(JAWS_LOG_DEBUG,"Input log and session key match");
                return true;
            }
            _log(JAWS_LOG_DEBUG,"The key found doesn't match the one below, please check that you entered the key correctly");
            return new Jaws_Error(_t('INSTALL_AUTH_ERROR_KEY_MATCH', 'key.txt'), 0, JAWS_ERROR_WARNING);
        }
        _log(JAWS_LOG_DEBUG,"Your key file was not found, please make sure you created it, and the web server is able to read it.");
        return new Jaws_Error(_t('INSTALL_AUTH_ERROR_KEY_FILE', 'key.txt'), 0, JAWS_ERROR_WARNING);
    }

    /**
     * Does any actions required to finish the stage
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        if (empty($_SESSION['customize'])) {
            $_SESSION['install']['stage']++;
        }

        return true;
    }

}