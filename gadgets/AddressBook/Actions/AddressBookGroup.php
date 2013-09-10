<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class AddressBook_Actions_AddressBookGroup extends Jaws_Gadget_HTML
{
    /**
     * Displays the list of Address Book items for selected group
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function GroupMembers()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
        $gid = (int) jaws()->request->fetch('id');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $addressItems = $model->GetAddressList($gid, $user);
        if (Jaws_Error::IsError($addressItems) || !isset($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('GroupMembers.html');

        $tpl->SetBlock("address_list");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_MEMBERS_TITLE'));

        $tpl->SetVariable('lbl_firstname', _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lastname', _t('ADDRESSBOOK_ITEMS_LASTNAME'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");

            $names = explode(';', $addressItem['name']);
            if (count($names) > 1) {
                $tpl->SetVariable('lastname', $names[0]);
                $tpl->SetVariable('firstname', $names[1]);
            }

            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->ParseBlock("address_list/item1");
        }

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
    }
}