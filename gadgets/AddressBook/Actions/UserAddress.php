<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_UserAddress extends Jaws_Gadget_Action
{
    /**
     * Displays the list of Public Address Book items for selected user
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function UserAddress()
    {
        if (!$GLOBALS['app']->Session->Logged() || !(jaws()->request->fetch('uid'))) {
            return Jaws_HTTPError::Get(403);
        }

        $uid = jaws()->request->fetch('uid');
        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($uid, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        $model = $this->gadget->model->load('AddressBook');
        $addressItems = $model->GetAddressList($user['id'], 0, true);
        if (Jaws_Error::IsError($addressItems) || !isset($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->template->load('UserAddress.html');
        $tpl->SetBlock("address_list");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_USER_ADDRESS_TITLE', $user['nickname']));

        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));

        $tpl->SetVariable('back_to_my_adr', _t('ADDRESSBOOK_BACK_TO_MY_ADDRESS'));
        $tpl->SetVariable('back_to_my_adr_link', $this->gadget->urlMap('AddressBook'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");
            $names = explode(';', $addressItem['name']);
            foreach ($names as $key => $name) {
                $tpl->SetVariable('name' . $key, $name);
            }
            $tpl->SetVariable('name', str_replace(';' , ' ', $addressItem['name']));
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('view_url', $this->gadget->urlMap('View', array('id' => $addressItem['id'])));
            $tpl->ParseBlock("address_list/item1");
        }

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
    }
}