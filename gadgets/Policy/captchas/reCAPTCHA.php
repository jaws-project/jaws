<?php
/**
 * reCAPTCHA for Jaws 
 * A captcha that protects and help digitizing books
 * More info: http://recaptcha.net/
 *
 * Note: You need to set private and public keys in
 *       Jaws registry, get your keys from:
 *       https://admin.recaptcha.net/recaptcha/createsite/
 * 
 *
 * @category   Captcha
 * @package    Policy
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once('reCAPTCHA/recaptchalib.php');

class reCAPTCHA
{
    var $_error;
    
    function reCAPTCHA()
    {
        // If not installed try to install it ;-)
        if ($GLOBALS['app']->Registry->Get('reCAPTCHA', 'Policy', JAWS_COMPONENT_GADGET) != 'installed') {
            $GLOBALS['app']->Registry->NewKey('reCAPTCHA', 'installed', 'Policy', JAWS_COMPONENT_GADGET);
            $GLOBALS['app']->Registry->NewKey('reCAPTCHA_public_key', 'UNDEFINED', 'Policy', JAWS_COMPONENT_GADGET);
            $GLOBALS['app']->Registry->NewKey('reCAPTCHA_private_key', 'UNDEFINED', 'Policy', JAWS_COMPONENT_GADGET);
        }
    }

    function Get()
    {
        $res = array();
        $publickey = $this->GetRegistry('reCAPTCHA_public_key');
        $reCAPTCHA = recaptcha_get_html($publickey, $this->_error);
        $res['label'] = _t('GLOBAL_CAPTCHA_CODE');
        $res['captcha'] =& Piwi::CreateWidget('StaticEntry', $reCAPTCHA);
        $res['captcha']->setTitle(_t('GLOBAL_CAPTCHA'));
        $res['entry'] = null;
        $res['description'] = _t('GLOBAL_CAPTCHA_CODE_DESC');
        return $res;
    }

    function Check()
    {
        $request =& Jaws_Request::getInstance();
        if ($request->get('recaptcha_response_field','post')) {
            $privatekey = $this->GetRegistry('reCAPTCHA_private_key');
            $resp = recaptcha_check_answer ($privatekey,
                                            $_SERVER["REMOTE_ADDR"],
                                            $request->get('recaptcha_challenge_field', 'post'),
                                            $request->get('recaptcha_response_field', 'post'));

            if ($resp->is_valid) {
                return true;
            } else {
                $this->_error = $resp->error;
                // TODO: Need to pass this error to the recaptcha html
                return false;
            }
        }
        return false;
    }
}