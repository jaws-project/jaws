<?php
/**
 * Authentication Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Authentication extends JawsUpgraderStage
{
    /**
     * Constructor
     *
     * @access public
     */
    function Upgrader_Authentication()
    {
        if (!isset($_SESSION['upgrade']['Authentication'])) {
            $_SESSION['secure']= false;
            $_SESSION['upgrade']['Authentication'] = array('key' => md5(uniqid('ugprader')) . time() . floor(microtime()*1000));
        }
    }

    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $request = Jaws_Request::getInstance();
        $use_log = $request->fetch('use_log', 'post');
        //Set main session-log vars
        if (isset($use_log)) {
            $_SESSION['use_log'] = $use_log === 'yes'? JAWS_LOG_DEBUG : false;
        }
        _log(JAWS_LOG_DEBUG,"Generating new installation key");

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Authentication/templates');
        $tpl->SetBlock('Authentication');

        $tpl->SetVariable('key_path_info', _t('UPGRADE_AUTH_PATH_INFO', 'key.txt', UPGRADE_PATH));
        $tpl->SetVariable('rsa_security',  _t('UPGRADE_AUTH_ENABLE_SECURITY'));
        $tpl->SetVariable('auth_upload',   _t('UPGRADE_AUTH_UPLOAD'));
        $tpl->SetVariable('key_file_info', _t('UPGRADE_AUTH_KEY_INFO'));
        $tpl->SetVariable('next',          _t('GLOBAL_NEXT'));
        $tpl->SetVariable('key', $_SESSION['upgrade']['Authentication']['key']);
        $tpl->SetVariable('checked',  $_SESSION['secure']? 'checked="checked"' : '');

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
        $request = Jaws_Request::getInstance();
        $secure = $request->fetch('secure', 'post');
        $_SESSION['secure'] = !empty($secure);

        // try to entering to secure transformation mode 
        if ($_SESSION['secure'] && (!isset($_SESSION['pub_key']) || empty($_SESSION['pub_key']))) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $result = $JCrypt->Generate_RSA_KeyPair(128);
            if (!Jaws_Error::isError($result)) {
                $pub_key = $JCrypt->pub_key;
                $pvt_key = $JCrypt->pvt_key;
                if (Crypt_RSA_Key::isValid($pub_key) && Crypt_RSA_Key::isValid($pvt_key)) {
                    $_SESSION['pub_mod'] = $JCrypt->math->bin2int($pub_key->getModulus());
                    $_SESSION['pub_exp'] = $JCrypt->math->bin2int($pub_key->getExponent());
                    $_SESSION['pub_key'] = $pub_key->toString();
                    $_SESSION['pvt_key'] = $pvt_key->toString();
                }
            } elseif (CRYPT_RSA_ERROR_NO_WRAPPERS == $result->GetCode()) {
                return new Jaws_Error(_t('UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION'), 0, JAWS_ERROR_WARNING);
            }

            if (!isset($_SESSION['pub_key'])) {
                return new Jaws_Error(_t('UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION'), 0, JAWS_ERROR_WARNING);
            }
        }

        $key_file = UPGRADE_PATH . 'key.txt';
        if (file_exists($key_file)) {
            $key = trim(file_get_contents($key_file));
            if ($key === $_SESSION['upgrade']['Authentication']['key']) {
                _log(JAWS_LOG_DEBUG,"Input log and session key match");
                return true;
            }
            _log(JAWS_LOG_DEBUG,"The key found doesn't match the one below, please check that you entered the key correctly.");
            return new Jaws_Error(_t('UPGRADE_AUTH_ERROR_KEY_MATCH', 'key.txt'), 0, JAWS_ERROR_WARNING);
        }
        _log(JAWS_LOG_DEBUG,"Your key file was not found, please make sure you created it, and the web server is able to read it.");
        return new Jaws_Error(_t('UPGRADE_AUTH_ERROR_KEY_FILE', 'key.txt'), 0, JAWS_ERROR_WARNING);
    }
}