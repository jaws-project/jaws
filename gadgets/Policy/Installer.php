<?php
/**
 * Policy Installer
 *
 * @category    GadgetModel
 * @package     Policy
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
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
            'captcha' => 'DISABLED',
            'captcha_driver' => 'MathCaptcha',
            'obfuscator' => 'DISABLED',
            'akismet_key' => '',
            'typepad_key' => '',
            'crypt_enabled' => 'false',
            'crypt_pub_key' => '',
            'crypt_pvt_key' => '',
            'crypt_key_len' => '128',
            'crypt_key_age' => '86400',
            'crypt_key_start_date' => '0',
            'passwd_bad_count' => '7',
            'passwd_lockedout_time' => '60',      // per second
            'passwd_max_age' => '0',              // per day  0 = resistant
            'passwd_min_length' => '0',
            'passwd_complexity' => 'no',
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
        if (version_compare($old, '0.1.1', '<')) {
            // Registry keys
            $obfuscator = $this->gadget->GetRegistry('obfuscator');
            if ($obfuscator == 'HideEmail') {
                $this->gadget->SetRegistry('obfuscator', 'EmailEncoder');
            }

            $tables = array('complexcaptcha',
                            'mathcaptcha',
                            'simplecaptcha');
            foreach ($tables as $table) {
                $result = $GLOBALS['db']->dropTable($table);
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }
            }

            $this->gadget->DelRegistry('complex_captcha');
            $this->gadget->DelRegistry('math_captcha');
            $this->gadget->DelRegistry('simple_captcha');
            $this->gadget->DelRegistry('hkcaptcha');
        }

        if (version_compare($old, '0.1.2', '<')) {
            $this->gadget->AddRegistry('typepad_key', '');
        }

        if (version_compare($old, '0.1.3', '<')) {
            $old_captch = $this->gadget->GetRegistry('captcha');
            if ($old_captch !== 'DISABLED') {
                $this->gadget->SetRegistry('captcha', 'ANONYMOUS');
                $this->gadget->AddRegistry('captcha_driver', $old_captch);
            } else {
                $this->gadget->AddRegistry('captcha_driver', 'MathCaptcha');
            }

            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Policy/ManageEncryptionKey', 'false');
        }

        if (version_compare($old, '0.2.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->AddRegistry('block_by_ip');
            $this->gadget->AddRegistry('block_by_agent');
            $this->gadget->AddRegistry('block_undefined_ip',    'false');
            $this->gadget->AddRegistry('block_undefined_agent', 'false');
        }

        return true;
    }

}