<?php
/*
 * load reCaptcha library
 */
require_once ROOT_JAWS_PATH . 'libraries/php/ReCaptcha.php';

/**
 * reCaptcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_ReCaptcha extends Jaws_Captcha
{
    /**
     * Captcha driver type
     *
     * @var     int
     * @access  protected
     */
    protected $type = Jaws_Captcha::CAPTCHA_BLOCK;

    /**
     * Install captcha driver
     *
     * @access  public
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function install()
    {
        if (is_null($this->app->registry->fetch('reCAPTCHA_public_key', 'Policy'))) {
            $this->app->registry->insert('reCAPTCHA_public_key', '', false, 'Policy');
            $this->app->registry->insert('reCAPTCHA_private_key', '', false, 'Policy');
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
        $publickey = $this->app->registry->fetch('reCAPTCHA_public_key', 'Policy');
        $reCAPTCHA = $objReCaptcha->recaptcha_get_html($publickey);

        $res = array();
        $res['key']   = 0;
        $res['type']  = $this->type;
        $res['text']  = $reCAPTCHA;
        $res['label'] = Jaws::t($this->_label);
        $res['title'] = Jaws::t($this->_label);
        $res['description'] = Jaws::t($this->_description);
        return $res;
    }

    /**
     * Check if a captcha value is valid
     *
     * @access  public
     * @param   bool    $cleanup    Delete captcha key after check
     * @return  bool    return validity of captcha value
     */
    function check($cleanup = true)
    {
        $recaptcha = $this->app->request->fetch(
            array('recaptcha_challenge_field', 'recaptcha_response_field'),
            'post'
        );
        if ($recaptcha['recaptcha_response_field']) {
            $privatekey = $this->app->registry->fetch('reCAPTCHA_private_key', 'Policy');
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