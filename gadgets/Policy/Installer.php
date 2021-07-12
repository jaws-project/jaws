<?php
/**
 * Policy Installer
 *
 * @category    GadgetModel
 * @package     Policy
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2021 Jaws Development Group
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
        array('blocked_domains', ''),
        array('crypt_enabled', 'false'),
        array('crypt_pub_key', ''),
        array('crypt_pvt_key', ''),
        array('crypt_key_len', '512'),
        array('crypt_key_age', '86400'),
        array('crypt_key_start_date', '0'),
        array('password_bad_count', '7'),
        array('password_lockedout_time', '60'),    // per second
        array('password_max_age', '0'),            // per hours 0 = resistant
        array('password_min_length', '0'),
        array('password_complexity', '/^[[:print:]]{1,24}$/'),
        array('login_captcha_status', '3'),
        array('login_captcha_driver', 'Math'),
        array('xss_parsing_level', 'paranoid'),
        array('max_active_sessions', '0'),       // 0 for unlimited
        array('session_online_timeout', 30),     // per minute
        array('session_anony_remember_timeout', 1440),  // per minute 1440  = 1 day
        array('session_login_remember_timeout', 43200), // per minute 43200 = 1 month
        array('session_ip_sensitive', false),
        array('session_agent_sensitive', false),
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
     * @return    boolean Returns true on a successful attempt and Jaws Error otherwise
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
        $this->gadget->registry->update(
            'blocked_domains',
            @file_get_contents(ROOT_JAWS_PATH. 'gadgets/Policy/Resources/blocked.domains.txt')
        );

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
            $this->gadget->registry->rename('passwd_bad_count',      'password_bad_count');
            $this->gadget->registry->rename('passwd_lockedout_time', 'password_lockedout_time');
            $this->gadget->registry->rename('passwd_max_age',        'password_max_age');
            $this->gadget->registry->rename('passwd_min_length',     'password_min_length');
            $this->gadget->registry->rename('passwd_complexity',     'password_complexity');
        }

        if (version_compare($old, '1.1.0', '<')) {
            $this->gadget->registry->update('crypt_key_len', 512);
            $this->gadget->registry->update('crypt_key_start_date', 0);
            $this->gadget->registry->insert(
                'blocked_domains',
                @file_get_contents(ROOT_JAWS_PATH. 'gadgets/Policy/Resources/blocked.domains.txt')
            );
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $objTable = Jaws_ORM::getInstance()->table('policy_ipblock');
            $result = $objTable->update(array('script' => 'index'))->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $objTable = Jaws_ORM::getInstance()->table('policy_agentblock');
            $result = $objTable->update(array('script' => 'index'))->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            if ($this->gadget->registry->fetch('password_complexity') == 'yes') {
                $this->gadget->registry->update(
                    'password_complexity',
                    '/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/'
                );
            } else {
                $this->gadget->registry->update(
                    'password_complexity',
                    '/^[[:print:]]{1,24}$/'
                );
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            $result = $this->installSchema('1.4.0.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.5.0', '<')) {
            // add/rename/delete some registry key related to session timeout management
            $old_session_remember_timeout = (int)$this->gadget->registry->fetch('session_remember_timeout');
            $this->gadget->registry->insert('session_anony_remember_timeout', 1440);
            $this->gadget->registry->insert(
                'session_login_remember_timeout',
                $old_session_remember_timeout * 60
            );
            $this->gadget->registry->delete('session_remember_timeout');
            $this->gadget->registry->rename('session_idle_timeout', 'session_online_timeout');
        }

        if (version_compare($old, '1.6.0', '<')) {
            $this->gadget->registry->insert('session_ip_sensitive', false);
            $this->gadget->registry->insert('session_agent_sensitive', false);
        }

        if (version_compare($old, '1.7.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '1.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}