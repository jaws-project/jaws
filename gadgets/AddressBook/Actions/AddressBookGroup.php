<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/resources/site_style.css');
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

        $this->AjaxMe('site_script.js');
        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('GroupMembers.html');
        $tpl->SetBlock("address_list");

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address_list/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address_list/response');
        }

        $gModel = $this->gadget->load('Model')->load('Model', 'Groups');
        $gInfo = $gModel->GetGroupInfo($gid);
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_MEMBERS_TITLE', $gInfo['name']));
        $tpl->SetVariable('group', $gid);
        $tpl->SetVariable('lbl_action', _t('GLOBAL_ACTIONS'));

        $tpl->SetVariable('lbl_name0', _t('ADDRESSBOOK_ITEMS_LASTNAME'));
        $tpl->SetVariable('lbl_name1', _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_address', _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('icon_add', STOCK_ADD);

        $tpl->SetVariable('address_list_link', $this->gadget->urlMap('AddressBook'));
        $tpl->SetVariable('address_list',    _t('ADDRESSBOOK_ADDRESSBOOK_MANAGE'));
        $tpl->SetVariable('groups_link', $this->gadget->urlMap('ManageGroups'));
        $tpl->SetVariable('groups', _t('ADDRESSBOOK_GROUPS_MANAGE'));

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
            $names = explode(';', $addressItem['name']);
            foreach ($names as $key => $name) {
                $tpl->SetVariable('name' . $key, $name);
            }
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('unbond', _t('ADDRESSBOOK_ADDRESS_REMOVE_FROM_GROUP'));
            $tpl->SetVariable('unbond_url', $this->gadget->urlMap('UnbondAddress', array('aid' => $addressItem['id'], 'gid' => $gid)));
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
            $model = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
            $result = $model->AddGroupToAddress($rqst['address'], $rqst['group'], $user);
            $link = $this->gadget->urlMap('GroupMembers', array('id' => $rqst['group']));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
            } else {
                $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_ADD_ADDRESS_RELATION_COMPLETE'), 'AddressBook');
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

        $model = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
        $aid = (int) jaws()->request->fetch('aid');
        $gid = (int) jaws()->request->fetch('gid');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $result = $model->DeleteAddressBookGroup($aid, $gid, $user);
        $link = $this->gadget->urlMap('GroupMembers', array('id' => $gid));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_DELETE_ADDRESS_RELATION_COMPLETE'), 'AddressBook');
        }
        Jaws_Header::Location($link);
    }
}