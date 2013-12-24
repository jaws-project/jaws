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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('block_undefined_ip', 'false'),
        array('block_undefined_agent', 'false'),
        array('filter', 'DISABLED'),
        array('default_captcha_status', 'DISABLED'),
        array('default_captcha_driver', 'Math'),
        array('obfuscator', 'DISABLED'),
        array('akismet_key', ''),
        array('typepad_key', ''),
        array('crypt_enabled', 'false'),
        array('crypt_pub_key', ''),
        array('crypt_pvt_key', ''),
        array('crypt_key_len', '128'),
        array('crypt_key_age', '86400'),
        array('crypt_key_start_date', '0'),
        array('passwd_bad_count', '7'),
        array('passwd_lockedout_time', '60'),      // per second
        array('passwd_max_age', '0'),              // per day  0 = resistant
        array('passwd_min_length', '0'),
        array('passwd_complexity', 'no'),
        array('login_captcha_status', '1'),
        array('login_captcha_driver', 'Math'),
        array('xss_parsing_level', 'paranoid'),
        array('max_active_sessions', '0'),         // 0 for unlimited
        array('session_idle_timeout', '30'),       // per minute
        array('session_remember_timeout', '720'),  // hours = 1 month
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'IPBlocking',
        'ManageIPs',
        'AgentBlocking',
        'ManageAgents',
        'Encryption',
        'ManageEncryptionKey',
        'AntiSpam',
        'AdvancedPolicies',
    );

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
        $this->gadget->registry->update('crypt_enabled', $_SESSION['secure']? 'true' : 'false');
        $this->gadget->registry->update('crypt_pub_key', $_SESSION['pub_key']);
        $this->gadget->registry->update('crypt_pvt_key', $_SESSION['pvt_key']);
        $this->gadget->registry->update('crypt_key_start_date', $_SESSION['secure']? time() : '0');

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
        if (version_compare($old, '1.0.0', '<')) {
            $this->gadget->registry->insert('max_active_sessions', '0');
        }

        return true;
    }

}