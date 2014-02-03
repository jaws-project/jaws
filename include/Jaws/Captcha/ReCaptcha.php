<?php
/*
 * load reCaptcha library
 */
require_once JAWS_PATH . 'libraries/php/ReCaptcha.php';

/**
 * reCaptcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_ReCaptcha extends Jaws_Captcha
{
    /**
     * Install captcha driver
     *
     * @access  public
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function install()
    {
        if (is_null($GLOBALS['app']->Registry->fetch('reCAPTCHA_public_key', 'Policy'))) {
            $GLOBALS['app']->Registry->insert('reCAPTCHA_public_key', '', false, 'Policy');
            $GLOBALS['app']->Registry->insert('reCAPTCHA_private_key', '', false, 'Policy');
        }

        return true;
    }

    /**
     * Returns an array with the captcha text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the text entry) and entry (the input)
     */
    function get()
    {
        $res = array();
        $objReCaptcha = new ReCaptcha();
        $publickey = $GLOBALS['app']->Registry->fetch('reCAPTCHA_public_key', 'Policy');
        $reCAPTCHA = $objReCaptcha->recaptcha_get_html($publickey);

        $res = array();
        $res['key']   = 0;
        $res['text']  = $reCAPTCHA;
        $res['label'] = _t($this->_label);
        $res['title'] = _t($this->_label);
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Check if a captcha value is valid
     *
     * @access  public
     * @return  bool    return validity of captcha value
     */
    function check()
    {
        $recaptcha = jaws()->request->fetch(array('recaptcha_challenge_field', 'recaptcha_response_field'), 'post');
        if ($recaptcha['recaptcha_response_field']) {
            $privatekey = $GLOBALS['app']->Registry->fetch('reCAPTCHA_private_key', 'Policy');
            $objReCaptcha = new ReCaptcha();
            $objReCaptcha->recaptcha_check_answer(
                $privatekey,
                $_SERVER["REMOTE_ADDR"],
                $recaptcha['recaptcha_challenge_field'],
                $recaptcha['recaptcha_response_field']
            );

            return $objReCaptcha->is_valid;
        }

        return false;
    }

}