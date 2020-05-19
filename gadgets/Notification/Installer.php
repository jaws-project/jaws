<?php
/**
 * Notification Installer
 *
 * @category    GadgetModel
 * @package     Notification
 */
class Notification_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('webpush_enabled', false),
        array('webpush_pvt_key', ''),
        array('webpush_pub_key', ''),
        array('webpush_anonymouse', false),
        array('processing', 'false'),
        array('last_update', '0'),
        array('queue_max_time', '1800'), // maximum time to execution an queue (seconds)
        array('eml_fetch_limit', '100'),
        array('sms_fetch_limit', '100'),
        array('web_fetch_limit', '100'),
        array('configuration', ''), // array(gadget_name=>(0,1, driver_name))
    );

    /**
     * Default ACL value of the gadget front-end
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'NotificationDrivers',
        'Messages',
        'DeleteMessage',
        'Settings',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //
        $keyPair = $this->Generate_ECDSA_KeyPair();

        // registry keys
        $this->gadget->registry->update('webpush_pvt_key', $keyPair['private']);
        $this->gadget->registry->update('webpush_pub_key', $keyPair['public']);

        // Add listeners
        $this->gadget->event->insert('Notify');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array(
            'notification_message', 'notification_recipient',
            'notification_driver'
        );
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
            }
        }

        return true;
    }

    /**
     * Generate new ECDSA key pair
     *
     * @access  private
     * @return  mixed   ECDSA key pair
     */
    private function Generate_ECDSA_KeyPair()
    {
        $new_key_pair = openssl_pkey_new(
            array(
                "digest_alg" => OPENSSL_ALGO_SHA256,
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_EC,
                "curve_name" => "prime256v1"
            )
        );
        openssl_pkey_export($new_key_pair, $privateKeyPEM);
        $pkeyDetails = openssl_pkey_get_details($new_key_pair);

        return array(
            'private' => $privateKeyPEM,
            'public'  => Jaws_JWT::base64URLEncode(chr(4) . $pkeyDetails['ec']['x'].$pkeyDetails['ec']['y']),
        );
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.1.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // registry keys
            $this->gadget->registry->delete('email_pop_count');
            $this->gadget->registry->delete('mobile_pop_count');
            $this->gadget->registry->insert('eml_fetch_limit', '100');
            $this->gadget->registry->insert('sms_fetch_limit', '100');
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->registry->insert('wp_fetch_limit', '100');
        }

        if (version_compare($old, '1.4.0', '<')) {
            $result = $this->installSchema('1.4.0.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.5.0', '<')) {
            //
            $keyPair = $this->Generate_ECDSA_KeyPair();

            // registry keys
            $this->gadget->registry->insert('webpush_pvt_key', $keyPair['private']);
            $this->gadget->registry->insert('webpush_pub_key', $keyPair['public']);
        }

        if (version_compare($old, '1.6.0', '<')) {
            // Add listener for login user
            $this->gadget->event->insert('LoginUser');
        }

        if (version_compare($old, '1.7.0', '<')) {
            // Add listener for login user
            $this->gadget->event->delete('LoginUser');
        }

        if (version_compare($old, '1.8.0', '<')) {
            // registry keys
            $this->gadget->registry->insert('webpush_enabled', false);
        }

        if (version_compare($old, '2.0.0', '<')) {
            $result = $this->installSchema('2.0.0.xml', array(), '1.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // registry keys
            $this->gadget->registry->delete('wp_fetch_limit');
            $this->gadget->registry->insert('web_fetch_limit', '100');
            $this->gadget->registry->insert('webpush_anonymouse', false);
        }

        if (version_compare($old, '2.1.0', '<')) {
            $result = $this->installSchema('2.1.0.xml', array(), '2.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.2.0', '<')) {
            $result = $this->installSchema('2.2.0.xml', array(), '2.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.3.0', '<')) {
            $result = $this->installSchema('2.3.0.xml', array(), '2.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $dropTables = array(
                'notification_messages', 'notification_email',
                'notification_mobile', 'notification_webpush'
            );
            foreach ($dropTables as $table) {
                $result = Jaws_DB::getInstance()->dropTable($table);
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }
            }
        }

        if (version_compare($old, '2.4.0', '<')) {
            $result = $this->installSchema('2.4.0.xml', array(), '2.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.5.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '2.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // registry keys
            $this->gadget->registry->delete('processing');
        }

        //FIXME add new ACLs (Message, DeleteMessage)

        return true;
    }

}