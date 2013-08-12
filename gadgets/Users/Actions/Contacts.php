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
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
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

        $tpl->SetVariable('lbl_country', _t('USERS_CONTACTS_COUNTRY'));
        $tpl->SetVariable('lbl_city', _t('USERS_CONTACTS_CITY'));
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_phone_number', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_mobile_number', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_fax_number', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariablesArray($contacts);

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
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserContacts');
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

        Jaws_Header::Location($this->gadget->urlMap('Contacts'));
    }

}