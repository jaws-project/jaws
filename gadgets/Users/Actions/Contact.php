<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Contact extends Users_Actions_Default
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
            Jaws_Header::Location(
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

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Account'));
        $tpl->SetVariable(
            'submenubar',
            $this->SubMenuBar('Contact', array('Account', 'Personal', 'Preferences', 'Contact', 'Contacts'))
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
        $tpl->SetVariable('img_add', STOCK_ADD);
        $tpl->SetVariable('img_del', STOCK_REMOVE);
        if (!empty($contact)) {
            $tpl->SetVariablesArray($contact);
        }

        // country
        $zModel = Jaws_Gadget::getInstance('Settings')->model->load('Zones');
        $countries = $zModel->GetCountries();
        if (!Jaws_Error::IsError($countries)) {
            array_unshift($countries, array('country' => 0, 'title' => ''));
            foreach ($countries as $country) {
                $tpl->SetBlock('contact/country_home');
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contact['country_home']) && $contact['country_home'] == $country['country']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contact/country_home');

                $tpl->SetBlock('contact/country_work');
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contact['country_work']) && $contact['country_work'] == $country['country']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contact/country_work');

                $tpl->SetBlock('contact/country_other');
                $tpl->SetVariable('value', $country['country']);
                $tpl->SetVariable('title', $country['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contact['country_other']) && $contact['country_other'] == $country['country']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contact/country_other');
            }
        }
        
        // province
        if (!empty($contact['country_home'])) {
            $provinces = $zModel->GetProvinces($contact['country_home']);
            if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
                array_unshift($provinces, array('province' => 0, 'title' => ''));
                foreach ($provinces as $province) {
                    $tpl->SetBlock('contact/province_home');
                    $tpl->SetVariable('value', $province['province']);
                    $tpl->SetVariable('title', $province['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['province_home']) && $contact['province_home'] == $province['province']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/province_home');
                }
            }
        }

        if (!empty($contact['country_work'])) {
            $provinces = $zModel->GetProvinces($contact['country_work']);
            if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
                array_unshift($provinces, array('province' => 0, 'title' => ''));
                foreach ($provinces as $province) {
                    $tpl->SetBlock('contact/province_work');
                    $tpl->SetVariable('value', $province['province']);
                    $tpl->SetVariable('title', $province['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['province_work']) && $contact['province_work'] == $province['province']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/province_work');
                }
            }
        }

        if (!empty($contact['country_other'])) {
            $provinces = $zModel->GetProvinces($contact['country_other']);
            if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
                array_unshift($provinces, array('province' => 0, 'title' => ''));
                foreach ($provinces as $province) {
                    $tpl->SetBlock('contact/province_other');
                    $tpl->SetVariable('value', $province['province']);
                    $tpl->SetVariable('title', $province['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['province_other']) && $contact['province_other'] == $province['province']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/province_other');
                }
            }
        }

        // city_home
        if (!empty($contact['province_home'])) {
            $cities = $zModel->GetCities($contact['province_home']);
            if (!Jaws_Error::IsError($cities) && count($cities) > 0) {
                foreach ($cities as $city) {
                    $tpl->SetBlock('contact/city_home');
                    $tpl->SetVariable('value', $city['city']);
                    $tpl->SetVariable('title', $city['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['city_home']) && $contact['city_home'] == $city['city']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/city_home');
                }
            }
        }

        // city_work
        if (!empty($contact['province_work'])) {
            $cities = $zModel->GetCities($contact['province_work']);
            if (!Jaws_Error::IsError($cities) && count($cities) > 0) {
                foreach ($cities as $city) {
                    $tpl->SetBlock('contact/city_work');
                    $tpl->SetVariable('value', $city['city']);
                    $tpl->SetVariable('title', $city['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['city_work']) && $contact['city_work'] == $city['city']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/city_work');
                }
            }
        }

        // city_other
        if (!empty($contact['province_other'])) {
            $cities = $zModel->GetCities($contact['province_other']);
            if (!Jaws_Error::IsError($cities) && count($cities) > 0) {
                foreach ($cities as $city) {
                    $tpl->SetBlock('contact/city_other');
                    $tpl->SetVariable('value', $city['city']);
                    $tpl->SetVariable('title', $city['title']);
                    $tpl->SetVariable('selected', '');
                    if (isset($contact['city_other']) && $contact['city_other'] == $city['city']) {
                        $tpl->SetVariable('selected', 'selected');
                    }
                    $tpl->ParseBlock('contact/city_other');
                }
            }
        }

        if (empty($contact['avatar'])) {
            $user_current_avatar = $GLOBALS['app']->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $GLOBALS['app']->getDataURL() . "avatar/" . $contact['avatar'];
            $user_current_avatar .= !empty($contact['last_update']) ? "?" . $contact['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('contact');
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
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
        $post = jaws()->request->fetch(
            array(
                'title', 'name', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'email_home', 'email_work', 'email_other',
                'province_home', 'city_home', 'address_home', 'postal_code_home',
                'province_work', 'city_work', 'address_work', 'postal_code_work',
                'province_other', 'city_other', 'address_other', 'postal_code_other',
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
                'Users.Contacts',
                RESPONSE_ERROR,
                $post
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_USERS_CONTACTINFO_UPDATED'),
                'Users.Contacts'
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('Contact'), 'Users.Contact');
    }

}