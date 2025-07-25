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
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
        $this->AjaxMe('index.js');
        $response = $this->gadget->session->pop('Contact');
        if (!isset($response['data'])) {
            $contact = $this->gadget->model->load('Contact')->get($this->app->session->user->id);
            if (Jaws_Error::IsError($contact)) {
                return Jaws_HTTPError::Get(500);
            }
        } else {
            $contact = $response['data'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('Contact.html');
        $tpl->SetBlock('contact');

        $tpl->SetVariable('title', $this::t('CONTACTS_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', $this::t('USERS_ACCOUNT_UPDATE'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        if (empty($contact['avatar'])) {
            $user_current_avatar = $this->app->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $this->app->getDataURL() . "avatar/" . $contact['avatar'];
            $user_current_avatar .= !empty($contact['last_update']) ? "?" . $contact['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        // load contact template
        $this->ContactTemplate($tpl);
        $this->gadget->export('contact', $contact);

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

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('lbl_home', $this::t('CONTACTS_HOME'));
        $tpl->SetVariable('lbl_work', $this::t('CONTACTS_WORK'));
        $tpl->SetVariable('lbl_other', $this::t('CONTACTS_OTHER'));
        $tpl->SetVariable('lbl_tel', $this::t('CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_fax', $this::t('CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('lbl_mobile', $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $tpl->SetVariable('lbl_email', Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_country', Jaws::t('COUNTRY'));
        $tpl->SetVariable('lbl_province', Jaws::t('PROVINCE'));
        $tpl->SetVariable('lbl_city', Jaws::t('CITY'));
        $tpl->SetVariable('lbl_address', $this::t('CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', $this::t('CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_note', $this::t('CONTACTS_NOTE'));
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
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
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

        $result = $this->gadget->model->load('Contact')->update(
            $this->app->session->user->id,
            $post,
            true // main user contact
        );
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $result->GetMessage(),
                RESPONSE_ERROR,
                'Contact',
                $post
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_CONTACTINFO_UPDATED'),
                RESPONSE_NOTICE,
                'Contact'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Contact'), 'Contact');
    }

    /**
     * Prepares a simple form to update user's contacts information (country, city, ...)
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Contacts()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }
        $this->gadget->CheckPermission('EditUserContacts');
        $this->AjaxMe('index.js');
        $this->gadget->export('lbl_name', Jaws::t('NAME'));
        $this->gadget->export('lbl_title', Jaws::t('TITLE'));
        $this->gadget->export('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->export('lbl_addContact', $this::t('CONTACTS_ADD'));
        $this->gadget->export('lbl_editContact', $this::t('CONTACTS_EDIT'));
        $this->gadget->export('lbl_edit', Jaws::t('EDIT'));
        $this->gadget->export('lbl_delete', Jaws::t('DELETE'));

        // Load the template
        $tpl = $this->gadget->template->load('Contacts.html');
        $tpl->SetBlock('contacts');

        $tpl->SetVariable('title', $this::t('CONTACTS_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', $this::t('USERS_ACCOUNT_UPDATE'));
        $tpl->SetVariable('lbl_add', Jaws::t('ADD'));
        $tpl->SetVariable('lbl_export_vcard', $this::t('EXPORT_VCARD'));
        $tpl->SetVariable('lbl_import_vcard', $this::t('IMPORT_VCARD'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));
        $tpl->SetVariable('export_url', $this->gadget->urlMap('ExportVCard'));

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

        return $this->gadget->model->load('Contact')->get($this->app->session->user->id, $id);
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
            array('search', 'limit', 'offset'),
            'post'
        );

        $currentUser = $this->app->session->user->id;
        $contacts = $this->gadget->model->load('Contact')->list(
            $currentUser,
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::isError($contacts)) {
            return $this->gadget->session->response(
                $contacts->getMessage(),
                RESPONSE_ERROR
            );
        }

        $total = $this->gadget->model->load('Contact')->listCount($currentUser, $post['search']);
        if (Jaws_Error::IsError($total)) {
            return $this->gadget->session->response(
                $total->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $contacts
            )
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

        $result = $this->gadget->model->load('Contact')->update(
            $this->app->session->user->id,
            $post['data'],
            false, // not main user contact
            $post['cid']
        );
        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('USERS_CONTACTINFO_UPDATED'), RESPONSE_NOTICE);
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
        $result = $this->gadget->model->load('Contact')->delete(
            $this->app->session->user->id,
            $ids
        );
        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('USERS_CONTACTINFO_DELETED'), RESPONSE_NOTICE);
        }
    }

}