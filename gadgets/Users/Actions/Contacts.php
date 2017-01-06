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
            $this->SubMenuBar('Contacts', array('Account', 'Personal', 'Preferences', 'Contacts'))
        );

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_home', _t('USERS_CONTACTS_HOME'));
        $tpl->SetVariable('lbl_work', _t('USERS_CONTACTS_WORK'));
        $tpl->SetVariable('lbl_other', _t('USERS_CONTACTS_OTHER'));
        $tpl->SetVariable('lbl_tel', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_fax', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_province', _t('GLOBAL_PROVINCE'));
        $tpl->SetVariable('lbl_city', _t('GLOBAL_CITY'));
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_note', _t('USERS_CONTACTS_NOTE'));
        $tpl->SetVariable('img_add', STOCK_ADD);
        $tpl->SetVariable('img_del', STOCK_REMOVE);
        $tpl->SetVariablesArray($contacts);

        // province
        $model = $this->gadget->model->load('Contacts');
        $provinces = $model->GetProvinces();
        if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
            array_unshift($provinces, array('id' => 0, 'title' => ''));
            foreach ($provinces as $province) {
                $tpl->SetBlock('contacts/province');
                $tpl->SetVariable('value', $province['id']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province']) && $contacts['province'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province');
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

        if (empty($contacts['avatar'])) {
            $user_current_avatar = $GLOBALS['app']->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $GLOBALS['app']->getDataURL() . "avatar/" . $contacts['avatar'];
            $user_current_avatar .= !empty($contacts['last_update']) ? "?" . $contacts['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('contacts');
        return $tpl->Get();
    }

    /**
     * Updates user contacts information
     *
     * @access  public
     * @return  void
     */
    function UpdateContacts()
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
                'title', 'tel_home', 'tel_work', 'tel_other', 'fax_home', 'fax_work', 'fax_other',
                'mobile_home', 'mobile_work', 'mobile_other', 'url_home', 'url_work', 'url_other',
                'province', 'city', 'address', 'postal_code', 'note'
            ),
            'post'
        );

        $uModel = $this->gadget->model->load('Contacts');
        $result = $uModel->UpdateContacts(
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

        Jaws_Header::Location($this->gadget->urlMap('Contacts'), 'Users.Contacts');
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