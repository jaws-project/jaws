<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_VCard extends Users_Actions_Default
{
    /**
     * Export VCard
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function ExportVCard()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        require_once JAWS_PATH . 'gadgets/Users/include/vCard.php';

        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;
        $contacts = $jUser->GetUserContacts($currentUser);

        $result = '';
        foreach ($contacts as $contact) {
            $vCard = new vCard;

            $vCard->n($contact['name'], 'LastName');
//            $vCard->fn($names[3] . (trim($names[3]) == '' ?  '' : ' ') . $names[1] . (trim($names[1]) == '' ? '' : ' ') . $names[0]);
//            $vCard->nickname($contact['nickname']);
            $vCard->title($contact['title']);

            if (!empty($contact['tel'])) {
                $tel = json_decode($contact['tel'], true);
                $vCard->tel($tel['home'], 'Home');
                $vCard->tel($tel['work'], 'Work');
                $vCard->tel($tel['other'], 'Other');
            }

            if (!empty($contact['email'])) {
                $email = json_decode($contact['email'], true);
                $vCard->email($email['home'], 'Home');
                $vCard->email($email['work'], 'Work');
                $vCard->email($email['other'], 'Other');
            }

            if (!empty($contact['address'])) {
                $adr = json_decode($contact['address'], true);
                $vCard->adr($adr['home'], 'Home');
                $vCard->adr($adr['work'], 'Work');
                $vCard->adr($adr['other'], 'Other');
            }

            if (!empty($contact['url'])) {
                $url = json_decode($contact['url'], true);
                $vCard->url($url['home'], 'Home');
                $vCard->url($url['work'], 'Work');
                $vCard->url($url['other'], 'Other');
            }

            $vCard->note($contact['note']);

            $result = $result . $vCard;
        }

        header("Content-Disposition: attachment; filename=\"" . 'contacts.vcf' . "\"");
        header("Content-type: application/csv");
        header("Content-Length: " . strlen($result));
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Connection: close");

        echo $result;
        return;
    }

    /**
     * UI for Importing vCard file to user's contacts
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function ImportVCardUI()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('EditUserContacts');
//        $this->AjaxMe('index.js');

        // Load the template
        $tpl = $this->gadget->template->load('ImportVCard.html');
        $tpl->SetBlock('vcard');

        $response = $GLOBALS['app']->Session->PopResponse('Users.ImportVCard');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->SetVariable('gadget_title', _t('USERS_IMPORT_VCARD'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Account'));
        $tpl->SetVariable(
            'submenubar',
            $this->SubMenuBar('Contact', array('Account', 'Personal', 'Preferences', 'Contact', 'Contacts'))
        );

        $tpl->SetVariable('upload_vcard_file_desc', _t('USERS_IMPORT_VCART_DESC'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->ParseBlock('vcard');
        return $tpl->Get();
    }

    /**
     * Importing vCard file to user's contacts
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function ImportVCard()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        require_once JAWS_PATH . 'gadgets/Users/include/vCard.php';

        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;


        if (empty($_FILES) || !is_array($_FILES)) {
            $GLOBALS['app']->Session->PushResponse(_t('GLOBAL_ERROR_UPLOAD'), 'Users.ImportVCard', RESPONSE_ERROR);
            return Jaws_Header::Location($this->gadget->urlMap('ImportVCardUI'));
        }

        try {
            $vCard = new vCard($_FILES['vcard_file']['tmp_name'], false, array('Collapse' => false));

            if (count($vCard) == 0) {
                $GLOBALS['app']->Session->PushResponse(_t('USERS_ERROR_VCARD_DATA_NOT_FOUND'), 'Users.ImportVCard', RESPONSE_ERROR);
                return Jaws_Header::Location($this->gadget->urlMap('ImportVCardUI'));
            } elseif (count($vCard) == 1) {
                $result = $this->PrepareForImport($vCard);
                if ($result) {
                    $contactId = $jUser->UpdateContacts($currentUser, 0, $result);
                }
            } else {
                foreach ($vCard as $Index => $vCardPart) {
                    $result = $this->PrepareForImport($vCardPart);
                    if ($result) {
                        $contactId = $jUser->UpdateContacts($currentUser, 0, $result);
                    }
                }
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'Users.ImportVCard', RESPONSE_ERROR);
            return Jaws_Header::Location($this->gadget->urlMap('ImportVCardUI'));
        }

        if(Jaws_Error::IsError($contactId)) {
            $GLOBALS['app']->Session->PushResponse($contactId->getMessage(), 'Users.ImportVCard', RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('USERS_VCARD_IMPORT_COMPLETED'), 'Users.ImportVCard', RESPONSE_NOTICE);
        }
        return Jaws_Header::Location($this->gadget->urlMap('ImportVCardUI'));
    }

    /**
     * Prepare data to insert in databse
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function PrepareForImport($vCard)
    {
        if ($vCard->N) {
            $data['name'] = implode(';', $vCard->N[0]);
        } else {
            $data['name'] = $vCard->FN[0] . ';;;;';
        }
        if (empty($data['name'])) {
            return false; // TODO: Show message: Can not import data without name
        }

//        if ($vCard->NICKNAME) {
//            $data['nickname'] = implode(';', $vCard->NICKNAME[0]);
//        }

        if ($vCard->TITLE) {
            $data['title'] = $vCard->TITLE[0];
        } else {
            $data['title'] = $data['name'];
        }

        if ($vCard->NOTE) {
            $data['note'] = $vCard->NOTE[0];
        }

        if ($vCard->TEL) {
            foreach ($vCard->TEL as $tel) {
                $telHome = array();
                $telWork = array();
                $telOther = array();

                if (is_scalar($tel)) {
                    $telOther[] = $tel['Value'];
                } else {
                    foreach ($vCard->TEL[0]['Type'] as $type) {
                        switch (strtolower($type)) {
                            case 'home':
                                $telHome[] = $vCard->TEL[0]['Value'];
                                break;
                            case 'work':
                                $telWork[] = $vCard->TEL[0]['Value'];
                                break;
                            default:
                                $telOther[] = $vCard->TEL[0]['Value'];
                                break;
                        }
                    }
                }

                $telItems = array(
                    'home' => empty($telHome) ? '' : implode(';', $telHome),
                    'work' => empty($telWork) ? '' : implode(';', $telWork),
                    'other' => empty($telOther) ? '' : implode(';', $telOther),
                );
                $data['tel'] = json_encode($telItems);
            }
        }

        if ($vCard->EMAIL) {
            foreach ($vCard->EMAIL as $email) {
                $emailHome = array();
                $emailWork = array();
                $emailOther = array();

                if (is_scalar($email)) {
                    $emailOther[] = $email['Value'];
                } else {
                    foreach ($vCard->EMAIL[0]['Type'] as $type) {
                        switch (strtolower($type)) {
                            case 'home':
                                $emailHome[] = $vCard->EMAIL[0]['Value'];
                                break;
                            case 'work':
                                $emailWork[] = $vCard->EMAIL[0]['Value'];
                                break;
                            default:
                                $emailOther[] = $vCard->EMAIL[0]['Value'];
                                break;
                        }
                    }
                }

                $emailItems = array(
                    'home' => empty($emailHome) ? '' : implode(';', $emailHome),
                    'work' => empty($emailWork) ? '' : implode(';', $emailWork),
                    'other' => empty($emailOther) ? '' : implode(';', $emailOther),
                );
                $data['email'] = json_encode($emailItems);
            }
        }

        if ($vCard->URL) {
            $data['url'] = implode('\n', $vCard->URL);
        }

        if ($vCard->ADR) {
            foreach ($vCard->ADR as $address) {
                $addressHome = array();
                $addressWork = array();
                $addressOther = array();

                $adr = array();
                $adr['country_name'] = isset($address['Country']) ? $address['Country'] . ' ' : '';
                $adr['province_name'] = isset($address['Region']) ? $address['Region'] . ' ' : '';
                $adr['postal_code'] = isset($address['PostalCode']) ? $address['PostalCode'] . ' ' : '';
                $adr['po_box'] = isset($address['POBox']) ? $address['POBox'] . ' ' : '';

                $adrStr = ($address['StreetAddress'] ? $address['StreetAddress'] . ' ' : '');
                $adrStr .= ($address['ExtendedAddress'] ? $address['ExtendedAddress'] . ' ' : '');
                $adrStr .= ($address['Locality'] ? $address['Locality'] . ' ' : '');
                $adr['address'] = $adrStr;

                foreach ($vCard->ADR[0]['Type'] as $type) {
                        switch (strtolower($type)) {
                            case 'home':
                                $addressHome = $adr;
                                break;
                            case 'work':
                                $addressWork = $adr;
                                break;
                            default:
                                $addressOther = $adr;
                                break;
                        }
                    }

                $addressItems = array(
                    'home' => empty($addressHome) ? '' : implode(';', $addressHome),
                    'work' => empty($addressWork) ? '' : implode(';', $addressWork),
                    'other' => empty($addressOther) ? '' : implode(';', $addressOther),
                );
                $data['address'] = json_encode($addressItems);
            }
        }

        /*        if ($vCard->PHOTO) {
                    foreach ($vCard->PHOTO as $Photo) {
                        if ($Photo['Encoding'] == 'b') {
                            $vCard->SaveFile('photo', 0, Jaws_Utils::upload_tmp_dir() . '/test_image.' . $Photo['Type'][0]);
                            $data['image'] = 'test_image.' . $Photo['Type'][0];
                            break;
                        }
                    }
                }*/

        return $data;
    }
}