<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Contacts extends Users_Actions_Default
{
    /**
     * Prepares a simple form to update user's contacts information (country, city, ...)
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Contacts()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }
        $this->gadget->CheckPermission('EditUserContacts');
        $this->AjaxMe('index.js');
        $response = $GLOBALS['app']->Session->PopResponse('Users.Contacts');
        if (!isset($response['data'])) {
            $jUser = new Jaws_User;
            $contacts = $jUser->GetUserContact($GLOBALS['app']->Session->GetAttribute('user'));
        } else {
            $contacts = $response['data'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('Contacts.html');
        $tpl->SetBlock('contacts');

        $tpl->SetVariable('gadget_title', _t('USERS_CONTACTS_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Account'));
        $tpl->SetVariable(
            'submenubar',
            $this->SubMenuBar('Contacts', array('Account', 'Personal', 'Preferences', 'Contact', 'Contacts'))
        );

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_home', _t('USERS_CONTACTS_HOME'));
        $tpl->SetVariable('lbl_work', _t('USERS_CONTACTS_WORK'));
        $tpl->SetVariable('lbl_other', _t('USERS_CONTACTS_OTHER'));
        $tpl->SetVariable('lbl_tel', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_fax', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_province', _t('GLOBAL_PROVINCE'));
        $tpl->SetVariable('lbl_city', _t('GLOBAL_CITY'));
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_note', _t('USERS_CONTACTS_NOTE'));
        $tpl->SetVariable('lbl_addContact', _t('USERS_CONTACTS_ADD'));
        $tpl->SetVariable('lbl_editContact', _t('USERS_CONTACTS_EDIT'));
        $tpl->SetVariable('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        // province
        $model = $this->gadget->model->load('Contacts');
        $provinces = $model->GetProvinces();
        if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
            array_unshift($provinces, array('id' => 0, 'title' => ''));
            foreach ($provinces as $province) {
                $tpl->SetBlock('contacts/province_home');
                $tpl->SetVariable('value', $province['id']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_home']) && $contacts['province_home'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_home');

                $tpl->SetBlock('contacts/province_work');
                $tpl->SetVariable('value', $province['id']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_work']) && $contacts['province_work'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_work');

                $tpl->SetBlock('contacts/province_other');
                $tpl->SetVariable('value', $province['id']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_other']) && $contacts['province_other'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_other');
            }
        }

        // city
        if (!empty($contacts['province'])) {
            $cities = $model->GetCities($contacts['province']);
            if (!Jaws_Error::IsError($cities) && count($cities) > 0) {
                foreach ($cities as $city) {
                    $tpl->SetBlock('contacts/city');
                    $tpl->SetVariable('value', $city['id']);
                    $tpl->SetVariable('title', $city['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contacts['city']) && $contacts['city'] == $city['id']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contacts/city');
                }
            }
        }

        $tpl->ParseBlock('contacts');
        return $tpl->Get();
    }


    /**
     * Get contact
     *
     * @access  public
     * @return  JSON
     */
    function GetContact()
    {
        $this->gadget->CheckPermission('EditUserContacts');
        $id = (int)jaws()->request->fetch('id', 'post');

        $jUser = new Jaws_User;
        return $jUser->GetUserContact($GLOBALS['app']->Session->GetAttribute('user'), $id);
    }

    /**
     * Get contacts list
     *
     * @access  public
     * @return  JSON
     */
    function GetContacts()
    {
        $this->gadget->CheckPermission('EditUserContacts');
        $post = jaws()->request->fetch(
            array('filters:array', 'limit', 'offset', 'searchLogic', 'search:array', 'sort:array'),
            'post'
        );

        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;
        $contacts = $jUser->GetUserContacts($currentUser, $post['limit'], $post['offset']);

        foreach($contacts as $key=>$contact) {
            $contact['recid'] = $contact['id'];
            $contacts[$key] = $contact;
        }
        $contactsCount = $jUser->GetUserContactsCount($currentUser);

        $response = array(
            'status' => 'success',
            'total' => $contactsCount,
            'records' => $contacts
        );

        return $response;
    }

    /**
     * Save a contact information
     *
     * @access  public
     * @return  void
     */
    function SaveContact()
    {
        $this->gadget->CheckPermission('EditUserContacts');

        $post = jaws()->request->fetch(array('cid', 'data:array'), 'post');
        // unset invalid keys
        $invalids = array_diff(
            array_keys($post['data']),
            array(
                'title', 'name', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'email_home', 'email_work', 'email_other',
                'province_home', 'city_home', 'address_home', 'postal_code_home',
                'province_work', 'city_work', 'address_work', 'postal_code_work',
                'province_other', 'city_other', 'address_other', 'postal_code_other',
                'note'
            )
        );
        foreach ($invalids as $invalid) {
            unset($post['data'][$invalid]);
        }

        $cModel = $this->gadget->model->load('Contacts');
        $result = $cModel->UpdateContacts(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['cid'],
            $post['data']
        );
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_USERS_CONTACTINFO_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete contact(s)
     *
     * @access  public
     * @return  void
     */
    function DeleteContacts()
    {
        $this->gadget->CheckPermission('EditUserContacts');

        $ids = jaws()->request->fetch('ids:array', 'post');
        $jUser = new Jaws_User;
        $result = $jUser->DeleteUserContacts(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $ids
        );
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_USERS_CONTACTINFO_DELETED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Get cities
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetCities()
    {
        $province = jaws()->request->fetch('province', 'post');
        if (empty($province)) {
            $provinces = jaws()->request->fetch('provinces:array', 'post');
        } else {
            $provinces = array($province);
        }
        $model = $this->gadget->model->load('Contacts');
        $res = $model->GetCities($provinces);
        if (Jaws_Error::IsError($res) || $res === false) {
            return array();
        } else {
            return $res;
        }
    }

}