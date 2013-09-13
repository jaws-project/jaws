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
        $gModel = $this->gadget->load('Model')->load('Model', 'Groups');
        $gInfo = $gModel->GetGroupInfo($gid);
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_MEMBERS_TITLE', $gInfo['name']));

        $tpl->SetVariable('lbl_name0', _t('ADDRESSBOOK_ITEMS_LASTNAME'));
        $tpl->SetVariable('lbl_name1', _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));

        $tpl->SetVariable('back_to_groups', _t('ADDRESSBOOK_GROUP_BACK_TO_GROUPS_LIST'));
        $tpl->SetVariable('back_to_groups_link', $this->gadget->urlMap('ManageGroups'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");
            $names = explode(';', $addressItem['name']);
            foreach ($names as $key => $name) {
                $tpl->SetVariable('name' . $key, $name);
            }
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->ParseBlock("address_list/item1");
        }

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
    }
}