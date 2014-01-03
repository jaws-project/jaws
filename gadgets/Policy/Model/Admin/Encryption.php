<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Admin_Encryption extends Jaws_Gadget_Model
{
    /**
     * Update  Encryption Settings
     *
     * @access  public
     * @param   bool    $enabled   Enable/Disable encryption
     * @param   bool    $key_age   Key age
     * @param   bool    $key_len   Key length
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateEncryptionSettings($enabled, $key_age, $key_len)
    {
        $this->gadget->registry->update('crypt_enabled', ($enabled? 'true' : 'false'));
        if ($this->gadget->GetPermission('ManageEncryptionKey')) {
            $this->gadget->registry->update('crypt_key_age', (int)$key_age);
            if ($this->gadget->registry->fetch('crypt_key_len') != $key_len) {
                $this->gadget->registry->update('crypt_key_len', (int)$key_len);
                $this->gadget->registry->update('crypt_key_start_date', 0);
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ENCRYPTION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}