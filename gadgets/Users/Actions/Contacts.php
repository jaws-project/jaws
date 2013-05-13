<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Contacts extends Users_HTML
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
                $this->gadget->GetURLFor(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserContacts');

        $response = $GLOBALS['app']->Session->PopResponse('Users.Contacts');
        if (!isset($response['data'])) {
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $jUser = new Jaws_User;
            $contacts = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), false, false, false, true);
        } else {
            $contacts = $response['data'];
        }

        // Load the template
        $tpl = $this->gadget->loadTemplate('Contacts.html');
        $tpl->SetBlock('contacts');
        
        $tpl->SetVariable('title', _t('USERS_USERS_ACCOUNT_PREF'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        // country
        $country =& Piwi::CreateWidget('Entry', 'country', $contacts['country']);
        $country->SetStyle('width: 142px;');
        $country->SetID('country');
        $tpl->SetVariable('lbl_country', _t('USERS_CONTACTS_COUNTRY'));
        $tpl->SetVariable('country', $country->Get());

        // city
        $city =& Piwi::CreateWidget('Entry', 'city', $contacts['city']);
        $city->SetStyle('width: 142px;');
        $city->SetID('city');
        $tpl->SetVariable('lbl_city', _t('USERS_CONTACTS_CITY'));
        $tpl->SetVariable('city', $city->Get());

        // address
        $address =& Piwi::CreateWidget('TextArea', 'address', $contacts['address']);
        $address->SetID('address');
        $address->SetRows(4);
        $address->SetColumns(34);
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('address', $address->Get());

        // postal_code
        $postalCode =& Piwi::CreateWidget('Entry', 'postal_code', $contacts['postal_code']);
        $postalCode->SetStyle('width: 142px;');
        $postalCode->SetID('postal_code');
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('postal_code', $postalCode->Get());

        // phone_number
        $phoneNumber =& Piwi::CreateWidget('Entry', 'phone_number', $contacts['phone_number']);
        $phoneNumber->SetStyle('width: 142px;');
        $phoneNumber->SetID('phone_number');
        $tpl->SetVariable('lbl_phone_number', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('phone_number', $phoneNumber->Get());

        // mobile_number
        $mobileNumber =& Piwi::CreateWidget('Entry', 'mobile_number', $contacts['mobile_number']);
        $mobileNumber->SetStyle('width: 142px;');
        $mobileNumber->SetID('mobile_number');
        $tpl->SetVariable('lbl_mobile_number', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile_number', $mobileNumber->Get());

        // fax_number
        $faxNumber =& Piwi::CreateWidget('Entry', 'fax_number', $contacts['fax_number']);
        $faxNumber->SetStyle('width: 142px;');
        $faxNumber->SetID('fax_number');
        $tpl->SetVariable('lbl_fax_number', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('fax_number', $faxNumber->Get());

        if (!empty($response)) {
            $tpl->SetBlock('contacts/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('contacts/response');
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
                $this->gadget->GetURLFor(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserContacts');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(
            array(
                'country', 'city', 'address', 'postal_code', 'phone_number',
                'mobile_number', 'fax_number'
            ),
            'post'
        );

        $uModel = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Contacts');
        $result = $uModel->UpdateContacts(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['country'],
            $post['city'],
            $post['address'],
            $post['postal_code'],
            $post['phone_number'],
            $post['mobile_number'],
            $post['fax_number']
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

        Jaws_Header::Location($this->gadget->GetURLFor('Contacts'));
    }

}