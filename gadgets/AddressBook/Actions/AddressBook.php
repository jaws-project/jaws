<?php
/**
 * AddressBook Gadget
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
    function AddressList()
    {
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $addressItems = $model->GetAddressList(null);
        if (Jaws_Error::IsError($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('AddressList.html');

        $tpl->SetBlock("address_list");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_NAME'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address_list/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address_list/response');
        }
        $link = $this->gadget->urlMap('ManageGroups');
        $tpl->SetVariable('manage_groups_link', $link);
        $tpl->SetVariable('manage_groups', _t('ADDRESSBOOK_GROUPS_MANAGE'));

        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_phone', _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item");
            $tpl->SetVariable('name', $addressItem['name']);
            $tpl->SetVariable('company', $addressItem['company']);
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('email', $addressItem['email']);
            $tpl->SetVariable('phone', $addressItem['phone_number']);

            //Edite Item, TODO: Check user can do this action
            $tpl->SetBlock('address_list/item/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_EDIT'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('EditAddress', array('id' => $addressItem['id'])));
            $tpl->ParseBlock('address_list/item/action');

            //Delete Item, TODO: Check user can do this action
            $tpl->SetBlock('address_list/item/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_DELETE'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('DeleteAddress', array('id' => $addressItem['id'])));
            $tpl->ParseBlock('address_list/item/action');

            $tpl->ParseBlock("address_list/item");
        }

        // Add New
        $tpl->SetBlock("address_list/actions");
        $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_ITEMS_ADD'));
        $link = $this->gadget->urlMap('AddAddress');
        $tpl->SetVariable('action_url', $link);
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
        $tpl = $this->gadget->loadTemplate('EditAddress.html');

        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_ADD_NEW'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', 0);
        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_phone', _t('ADDRESSBOOK_ITEMS_TEL'));
        $tpl->SetVariable('lbl_fax', _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('lbl_mobile', _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('lbl_address', _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('lbl_pstcode', _t('ADDRESSBOOK_ITEMS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_url', _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes', _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressList');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }

    /**
     * Displays form for edit AddressBook item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function EditAddress()
    {
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_ADD_NEW'));
        $tpl = $this->gadget->loadTemplate('EditAddress.html');

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('id'));
        if (empty($rqst['id'])) {
            return false;
        }

        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_ADD_NEW'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $info = $model->GetAddressInfo((int) $rqst['id']);
        if (Jaws_Error::IsError($info)) {
            return $info->getMessage(); // TODO: Show intelligible message
        }

        if (!isset($info)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('lbl_name',       _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('name',           $info['name']);
        $tpl->SetVariable('lbl_company',    _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('company',        $info['company']);
        $tpl->SetVariable('lbl_title',      _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('title',          $info['title']);
        $tpl->SetVariable('lbl_email',      _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('email',          $info['email']);
        $tpl->SetVariable('lbl_phone',      _t('ADDRESSBOOK_ITEMS_TEL'));
        $tpl->SetVariable('phone',          $info['phone_number']);
        $tpl->SetVariable('lbl_fax',        _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('fax',            $info['fax_number']);
        $tpl->SetVariable('lbl_mobile',     _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('mobile',         $info['mobile_number']);
        $tpl->SetVariable('lbl_address',    _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('address',        $info['address']);
        $tpl->SetVariable('lbl_pstcode',    _t('ADDRESSBOOK_ITEMS_POSTAL_CODE'));
        $tpl->SetVariable('pstcode',        $info['postal_code']);
        $tpl->SetVariable('lbl_url',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('url',            $info['url']);
        $tpl->SetVariable('lbl_notes',      _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('notes',          $info['notes']);

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressList');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }

    /**
     * Save New/Edit Address Book Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function SaveItem()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('addressbook_name', 'addressbook_company', 'addressbook_title',
                                    'addressbook_email', 'addressbook_phone', 'addressbook_mobile',
                                    'addressbook_fax', 'addressbook_address', 'addressbook_pstcode',
                                    'addressbook_url', 'addressbook_notes', 'id'),
                              'post');

        $post['user'] = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

        if ((int) $post['id'] == 0) {
            $result = $model->InsertAddress($post['user'], $post['addressbook_name'], 
                                            $post['addressbook_company'], $post['addressbook_title'], 
                                            $post['addressbook_email'], $post['addressbook_phone'], 
                                            $post['addressbook_mobile'], $post['addressbook_fax'], 
                                            $post['addressbook_address'], $post['addressbook_pstcode'],
                                            $post['addressbook_url'], $post['addressbook_notes'], 0
                                            );
            $msg = _t('ADDRESSBOOK_RESULT_NEW_ADDRESS_SAVED');
        } else {
            $result = $model->UpdateAddress($post['id'], $post['addressbook_name'], 
                                            $post['addressbook_company'], $post['addressbook_title'], 
                                            $post['addressbook_email'], $post['addressbook_phone'], 
                                            $post['addressbook_mobile'], $post['addressbook_fax'], 
                                            $post['addressbook_address'], $post['addressbook_pstcode'],
                                            $post['addressbook_url'], $post['addressbook_notes'], 0
                                            );
            $msg = _t('ADDRESSBOOK_RESULT_EDIT_ADDRESS_SAVED');
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
            Jaws_Header::Referrer();
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse($msg, 'AddressBook');
            $link = $this->gadget->urlMap('AddressList');
            Jaws_Header::Location($link);
        }
    }
}



















