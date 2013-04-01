<?php
/**
 * Policy Installer
 *
 * @category    GadgetModel
 * @package     Policy
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access    public
     * @return    boolean Returns true on a successfull attempt and Jaws Error otherwise
    */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry(array(
            'block_undefined_ip' => 'false',
            'block_undefined_agent' => 'false',
            'allow_duplicate' => 'no',
            'filter' => 'DISABLED',
            'default_captcha' => 'DISABLED',
            'default_captcha_driver' => 'MathCaptcha',
            'obfuscator' => 'DISABLED',
            'akismet_key' => '',
            'typepad_key' => '',
            'crypt_enabled' => $_SESSION['secure']? 'true' : 'false',
            'crypt_pub_key' => $_SESSION['pub_key'],
            'crypt_pvt_key' => $_SESSION['pvt_key'],
            'crypt_key_len' => '128',
            'crypt_key_age' => '86400',
            'crypt_key_start_date' => $_SESSION['secure']? time() : '0',
            'passwd_bad_count' => '7',
            'passwd_lockedout_time' => '60',      // per second
            'passwd_max_age' => '0',              // per day  0 = resistant
            'passwd_min_length' => '0',
            'passwd_complexity' => 'no',
            'login_captcha' => '1',
            'login_captcha_driver' => 'MathCaptcha',
            'xss_parsing_level' => 'paranoid',
            'session_idle_timeout' => '30',       // per minute
            'session_remember_timeout' => '720',  // hours = 1 month
        ));

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.3.0', '<')) {
            $this->gadget->AddRegistry('default_captcha', $this->gadget->GetRegistry('captcha'));
            $this->gadget->AddRegistry('default_captcha_driver', $this->gadget->GetRegistry('captcha_driver'));
            $this->gadget->AddRegistry('login_captcha', '1');
            $this->gadget->AddRegistry('login_captcha_driver', 'MathCaptcha');
            $this->gadget->DelRegistry('captcha');
            $this->gadget->DelRegistry('captcha_driver');
        }

        return true;
    }

}