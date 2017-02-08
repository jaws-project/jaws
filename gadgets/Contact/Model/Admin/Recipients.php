<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Model_Admin_Recipients extends Jaws_Gadget_Model
{
    /**
     * Insert the information of a Recipient
     *
     * @access  public
     * @param   string  $name           Name of the recipient
     * @param   string  $email          Email of recipient
     * @param   string  $tel            Phone number of recipient
     * @param   string  $fax            Fax number of recipient
     * @param   string  $mobile         Mobile number of recipient
     * @param   string  $inform_type
     * @param   string  $visible        The visible of the recipient
     * @param   int     $group          Jaws User's group Id
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible, $group)
    {
        $data = array();
        $data['name']        = $name;
        $data['email']       = $email;
        $data['tel']         = $tel;
        $data['fax']         = $fax;
        $data['mobile']      = $mobile;
        $data['inform_type'] = (int)$inform_type;
        $data['visible']     = (int)$visible;
        $data['group']       = (int)$group;

        $rcptTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $result = $rcptTable->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_ADDED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update the information of a Recipient
     *
     * @access  public
     * @param   string  $id             ID of the recipient
     * @param   string  $name           Name of the recipient
     * @param   string  $email          Email of recipient
     * @param   string  $tel            Phone number of recipient
     * @param   string  $fax            Fax number of recipient
     * @param   string  $mobile         Mobile number of recipient
     * @param   string  $inform_type    
     * @param   string  $visible        The visible of the recipient
     * @param   int     $group          Jaws User's group Id
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible, $group)
    {
        $data = array();
        $data['name']        = $name;
        $data['email']       = $email;
        $data['tel']         = $tel;
        $data['fax']         = $fax;
        $data['mobile']      = $mobile;
        $data['inform_type'] = (int)$inform_type;
        $data['visible']     = (int)$visible;
        $data['group']     = (int)$group;

        $rcptTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $result = $rcptTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a Recipient
     *
     * @access  public
     * @param   string  $id  ID of the Recipient
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteRecipient($id)
    {
        $objORM = Jaws_ORM::getInstance();
        $result = $objORM->delete()->table('contacts_recipients')->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}
