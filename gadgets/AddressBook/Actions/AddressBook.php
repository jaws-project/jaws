<?php
/**
 * AddressBook Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class AddressBook_Actions_AddressBook extends Jaws_Gadget_HTML
{
    /**
     * Displays the list of Address Book items, this items can filter by $gid(group ID) param.
     *
     * @access  public
     * $gid     Group ID
     * @return  string HTML content with menu and menu items
     */
    function AddressList($gid = null)
    {
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $addressItems = $model->GetAddressList($gid);
        if (Jaws_Error::IsError($addressItems) || !isset($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('AddressList.html');

        $tpl->SetBlock("address_list");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_NAME'));
        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_tel', _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item");
            $tpl->SetVariable('name', $addressItem['name']);
            $tpl->SetVariable('company', $addressItem['company']);
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('email', $addressItem['email']);
            $tpl->SetVariable('tel', $addressItem['phone_number']);
            $tpl->ParseBlock("address_list/item");
        }

        $tpl->SetBlock("address_list/actions");
        $tpl->SetVariable('add_address', _t('ADDRESSBOOK_ITEMS_ADD'));
        $link = $GLOBALS['app']->Map->GetURLFor('AddressBook', 'AddAddress');
        $tpl->SetVariable('add_address_link', $link);
        $tpl->ParseBlock("address_list/actions");

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
    }

    /**
     * Displays form for add new AddressBook item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function AddAddress()
    {
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_ADD_NEW'));
        $tpl = $this->gadget->loadTemplate('AddAddress.html');

        $tpl->SetBlock("address");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_ITEMS_ADD_NEW'));
        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_tel', _t('ADDRESSBOOK_ITEMS_TEL'));
        $tpl->SetVariable('lbl_fax', _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('lbl_mobile', _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('lbl_address', _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('lbl_url', _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes', _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }
}