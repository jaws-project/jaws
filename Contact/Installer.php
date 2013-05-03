<?php
/**
 * Contact Installer
 *
 * @category    GadgetModel
 * @package     Contact
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Installer extends Jaws_Gadget_Installer
{
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

        $new_dir = JAWS_DATA . 'contact';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('CONTACT_NAME'));
        }

        //registry keys.
        $this->gadget->registry->insert(
            array(
                'use_antispam' => 'true',
                'email_format' => 'html',
                'enable_attachment' => 'false',
                'comments' => '',
                'default_items' => 'name,email,url,recipient,subject,attachment,message',
            )
        );

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
        $tables = array('contacts',
                        'contacts_recipients');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('CONTACT_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->registry->delete('use_antispam');
        $this->gadget->registry->delete('email_format');
        $this->gadget->registry->delete('enable_attachment');
        $this->gadget->registry->delete('comments');
        $this->gadget->registry->delete('default_items');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.3.0', '<')) {
            $result = $this->installSchema('0.3.0.xml', '', '0.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/EditSentMessage',  'true');

            // Registry keys.
            $send_html = $this->gadget->registry->fetch('send_html') == 'true';
            $this->gadget->registry->insert('use_captcha', 'true');
            $this->gadget->registry->insert('email_format', $send_html? 'html' : 'text');
            $this->gadget->registry->insert('enable_attachment', 'false');
            $this->gadget->registry->delete('send_html');
        }

        if (version_compare($old, '0.3.1', '<')) {
            $this->gadget->registry->insert('comments', '');
            $this->gadget->registry->delete('comment');
        }

        if (version_compare($old, '0.3.2', '<')) {
            $result = $this->installSchema('0.3.2.xml', '', '0.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->registry->delete('use_captcha');
        }

        if (version_compare($old, '0.3.3', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/AccessToMailer', 'false');
        }

        if (version_compare($old, '0.3.4', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.3.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/AllowAttachment', 'false');

            // Registry keys
            $this->gadget->registry->insert('default_items',
                                              'name,email,url,recipient,subject,attachment,message');

            $new_dir = JAWS_DATA . 'contact';
            if (!Jaws_Utils::mkdir($new_dir)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('CONTACT_NAME'));
            }
        }

        return true;
    }

}