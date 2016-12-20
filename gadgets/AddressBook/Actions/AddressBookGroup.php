<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_AddressBookGroup extends AddressBook_Actions_Default
{
    /**
     * Displays the list of Address Book items for selected group
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function GroupMembers()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->model->load('AddressBookGroup');
        $gid = (int) jaws()->request->fetch('id');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $addressItems = $model->GetAddressList($gid, $user);
        if (Jaws_Error::IsError($addressItems) || !isset($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $this->AjaxMe('site_script.js');
        $this->SetTitle($this->gadget->title);
        $tpl = $this->gadget->template->load('GroupMembers.html');
        $tpl->SetBlock("address_list");

        $response = $GLOBALS['app']->Session->PopResponse('AddressBook.AdrGroups');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $gModel = $this->gadget->model->load('Groups');
        $gInfo = $gModel->GetGroupInfo($gid);
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_MEMBERS_TITLE', $gInfo['name']));
        $tpl->SetVariable('group', $gid);
        $tpl->SetVariable('lbl_action', _t('GLOBAL_ACTIONS'));

        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_address', _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('add_icon', STOCK_ADD);

        $tpl->SetVariable('menubar', $this->MenuBar(''));
        $tpl->SetVariable('edit_group', _t('ADDRESSBOOK_GROUPS_EDIT'));
        $tpl->SetVariable('edit_group_link', $this->gadget->urlMap('EditGroup', array('id' => $gid)));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('icon_ok', STOCK_OK);

        $notInGroupAddress = $model->GetAddressListNotInGroup($gid, $user);
        if (!Jaws_Error::IsError($notInGroupAddress) || !empty($notInGroupAddress)) {
            foreach ($notInGroupAddress as $addressItem) {
                $tpl->SetBlock("address_list/address_item");
                $tpl->SetVariable('aid', $addressItem['id']);
                $tpl->SetVariable('address_name', str_replace(';' , ' ', $addressItem['name']));
                $tpl->ParseBlock("address_list/address_item");
            }
        }

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");
            $tpl->SetVariable('name', str_replace(';' , ' ', $addressItem['name']));
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('index', $addressItem['id']);
            $tpl->ParseBlock("address_list/item1");
        }

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
    }

    /**
     * Add relation between an address and a group
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function BondAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }
        // TODO: Check this group and addres is for current Login user
        $rqst = jaws()->request->fetch(array('address:int', 'group:int'));
        if (!empty($rqst['address']) && !empty($rqst['group'])) {
            $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
            $model = $this->gadget->model->load('AddressBookGroup');
            $result = $model->AddGroupToAddress($rqst['address'], $rqst['group'], $user);
            $link = $this->gadget->urlMap('GroupMembers', array('id' => $rqst['group']));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook.AdrGroups', RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_ADD_ADDRESS_RELATION_COMPLETE'), 'AddressBook.AdrGroups');
            }
            Jaws_Header::Location($link);
        }
    }

    /**
     * Remove relation of address from one group
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function UnbondAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->model->load('AddressBookGroup');
        $adrs = jaws()->request->fetch('adr:array');
        $gid = (int) jaws()->request->fetch('group');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $result = $model->DeleteAddressBooksGroup($adrs, $gid, $user);

        $link = $this->gadget->urlMap('GroupMembers', array('id' => $gid));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook.AdrGroups', RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_DELETE_ADDRESS_RELATION_COMPLETE'), 'AddressBook.AdrGroups');
        }
        Jaws_Header::Location($link);
    }
}