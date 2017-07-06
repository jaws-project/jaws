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
     * Prepares a simple form to update user's contact information (country, city, ...)
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Contact()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
        $this->AjaxMe('index.js');
        $response = $GLOBALS['app']->Session->PopResponse('Users.Contact');
        if (!isset($response['data'])) {
            $jUser = new Jaws_User;
            $contact = $jUser->GetUserContact($GLOBALS['app']->Session->GetAttribute('user'));
            if (Jaws_Error::IsError($contact)) {
                return Jaws_HTTPError::Get(500);
            }
        } else {
            $contact = $response['data'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('Contact.html');
        $tpl->SetBlock('contact');

        $tpl->SetVariable('gadget_title', _t('USERS_CONTACTS_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        if (empty($contact['avatar'])) {
            $user_current_avatar = $GLOBALS['app']->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $GLOBALS['app']->getDataURL() . "avatar/" . $contact['avatar'];
            $user_current_avatar .= !empty($contact['last_update']) ? "?" . $contact['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        // load contact template
        $this->ContactTemplate($tpl);
        $this->gadget->define('contact', $contact);

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('contact');
        return $tpl->Get();
    }

    /**
     * Providing contact template
     *
     * @access  public
     * @param   object  $tpl    (Optional) Jaws Template object
     * @return  string  XHTML template of a form
     */
    function ContactTemplate(&$tpl)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/template");

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
        $tpl->SetVariable('lbl_country', _t('GLOBAL_COUNTRY'));
        $tpl->SetVariable('lbl_province', _t('GLOBAL_PROVINCE'));
        $tpl->SetVariable('lbl_city', _t('GLOBAL_CITY'));
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_note', _t('USERS_CONTACTS_NOTE'));
        $tpl->SetVariable('img_add', STOCK_ADD);
        $tpl->SetVariable('img_del', STOCK_REMOVE);

        // country
        $countries = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCountries();
        if (!Jaws_Error::IsError($countries)) {
            array_unshift($countries, array('country' => '', 'title' => ''));
            foreach ($countries as $country) {
                $tpl->SetBlock("$block/template/country_home");
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                $tpl->ParseBlock("$block/template/country_home");

                $tpl->SetBlock("$block/template/country_work");
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                $tpl->ParseBlock("$block/template/country_work");

                $tpl->SetBlock("$block/template/country_other");
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                $tpl->ParseBlock("$block/template/country_other");
            }
        }

        $tpl->ParseBlock("$block/template");
        return $tpl->Get();
    }

    /**
     * Updates user contact information
     *
     * @access  public
     * @return  void
     */
    function UpdateContact()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
        $post = $this->gadget->request->fetch(
            array(
                'title', 'name', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'email_home', 'email_work', 'email_other',
                'country_home', 'province_home', 'city_home', 'address_home', 'postal_code_home',
                'country_work', 'province_work', 'city_work', 'address_work', 'postal_code_work',
                'country_other', 'province_other', 'city_other', 'address_other', 'postal_code_other',
                'note'
            ),
            'post'
        );

        $uModel = $this->gadget->model->load('Contacts');
        $result = $uModel->UpdateContact(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post
        );
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Users.Contact',
                RESPONSE_ERROR,
                $post
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_USERS_CONTACTINFO_UPDATED'),
                'Users.Contact'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Contact'), 'Users.Contact');
    }

    /**
     * Prepares a simple form to update user's contacts information (country, city, ...)
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Contacts()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }
        $this->gadget->CheckPermission('EditUserContacts');
        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));
        $this->gadget->define('lbl_addContact', _t('USERS_CONTACTS_ADD'));
        $this->gadget->define('lbl_editContact', _t('USERS_CONTACTS_EDIT'));
        $this->gadget->define('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));

        // Load the template
        $tpl = $this->gadget->template->load('Contacts.html');
        $tpl->SetBlock('contacts');

        $tpl->SetVariable('gadget_title', _t('USERS_CONTACTS_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));
        $tpl->SetVariable('lbl_export_vcard', _t('USERS_EXPORT_VCARD'));
        $tpl->SetVariable('lbl_import_vcard', _t('USERS_IMPORT_VCARD'));

        $tpl->SetVariable('export_url', $this->gadget->urlMap('ExportVCard'));
        $tpl->SetVariable('import_url', $this->gadget->urlMap('ImportVCardUI'));

        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));


        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // load contact template
        $this->ContactTemplate($tpl);

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
        $id = (int)$this->gadget->request->fetch('id', 'post');

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
        $post = $this->gadget->request->fetch(
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

        return array(
            'status' => 'success',
            'total' => $contactsCount,
            'records' => $contacts
        );
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

        $post = $this->gadget->request->fetch(array('cid', 'data:array'), 'post');
        // unset invalid keys
        $invalids = array_diff(
            array_keys($post['data']),
            array(
                'title', 'name', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'email_home', 'email_work', 'email_other',
                'country_home', 'province_home', 'city_home', 'address_home', 'postal_code_home',
                'country_work', 'province_work', 'city_work', 'address_work', 'postal_code_work',
                'country_other', 'province_other', 'city_other', 'address_other', 'postal_code_other',
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

        $ids = $this->gadget->request->fetch('ids:array', 'post');
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
}