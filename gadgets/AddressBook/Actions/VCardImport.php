<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_VCardImport extends AddressBook_Actions_Default
{
    /**
     * Show import data form
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function VCardImport()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetTitle(_t('ADDRESSBOOK_IMPORT_VCART_TITLE'));
        $tpl = $this->gadget->template->load('VCardImport.html');
        $tpl->SetBlock("vcard");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_IMPORT_VCART_TITLE'));
        $tpl->SetVariable('upload_vcard_file_desc', _t('ADDRESSBOOK_IMPORT_VCART_DESC'));

        $tpl->SetVariable('lbl_save', _t('ADDRESSBOOK_IMPORT_VCART'));
        $response = $GLOBALS['app']->Session->PopResponse('AddressBook.Import');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->SetVariable('menubar', $this->MenuBar(''));

        // Cancel Button
        $tpl->SetBlock("vcard/actions");
        $tpl->SetVariable('action_lbl', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressBook'));
        $tpl->ParseBlock("vcard/actions");

        $tpl->ParseBlock('vcard');

        return $tpl->Get();
    }

    /**
     * Import data with VCard format from file
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function VCardImportFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        require_once JAWS_PATH . 'gadgets/Addressbook/vCard.php';

        if (empty($_FILES) || !is_array($_FILES)) {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_ERROR_IMPORT_PLEASE_SELECT_FILE'), 'AddressBook.Import', RESPONSE_ERROR);
            Jaws_Header::Location($this->gadget->urlMap('VCardImport'));
        }

        try {
            $vCard = new vCard($_FILES['vcard_file']['tmp_name'], false, array('Collapse' => false));

            $model = $this->gadget->model->load('AddressBook');
            if (count($vCard) == 0) {
                $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_ERROR_VCARD_DATA_NOT_FOUND'), 'AddressBook.Import', RESPONSE_ERROR);
                Jaws_Header::Location($this->gadget->urlMap('VCardImport'));
            } elseif (count($vCard) == 1) {
                $result = $this->PrepareForImport($vCard);
                if ($result) {
                    $adrID = $model->InsertAddress($result);
                }
            } else {
                foreach ($vCard as $Index => $vCardPart) {
                    $result = $this->PrepareForImport($vCardPart);
                    if ($result) {
                        $adrID = $model->InsertAddress($result);
                    }
                }
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'AddressBook.Import', RESPONSE_ERROR); // TODO: Translate Messages
            Jaws_Header::Location($this->gadget->urlMap('VCardImport'));
        }

        $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_IMPORT_COMPLETED'), 'AddressBook');
        Jaws_Header::Location($this->gadget->urlMap('AddressBook'));
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

        if ($vCard->NICKNAME) {
            $data['nickname'] = implode(';', $vCard->NICKNAME[0]);
        }

        if ($vCard->TITLE) {
            $data['title'] = $vCard->TITLE[0];
        }

        if ($vCard->NOTE) {
            $data['notes'] = $vCard->NOTE[0];
        }

        if ($vCard->TEL) {
            $telHome = array();
            $telWork = array();
            $telOther = array();
            foreach ($vCard -> TEL as $Tel) {
                if (is_scalar($Tel)) {
                    $telOther[] = $this->_DefaultTelTypes['voice'] . ':' . $Tel['Value'];
                } else {
                    $prefixKey = 0;
                    foreach ($this->_TelTypes as $key => $tellType) {
                        if (in_array($tellType['fieldType'], $Tel['Type']) && in_array($tellType['telType'], $Tel['Type'])) {
                            $prefixKey = $key;
                        }
                    }
                    if (!empty($this->_TelTypes[$prefixKey])) {
                        switch ($this->_TelTypes[$prefixKey]['fieldType']) {
                            case 'home':
                                $telHome[] = $prefixKey . ':' . $Tel['Value'];
                                break;
                            case 'work':
                                $telWork[] = $prefixKey . ':' . $Tel['Value'];
                                break;
                            case 'other':
                                $telOther[] = $prefixKey . ':' . $Tel['Value'];
                                break;
                        }
                    } else {
                        $prefixKey = $this->_DefaultTelTypes['voice'];
                        foreach ($this->_DefaultTelTypes as $tellType => $key) {
                            if (in_array($tellType, $Tel['Type'])) {
                                $prefixKey = $key;
                            }
                        }
                        $telOther[] = $prefixKey . ':' . $Tel['Value'];
                    }
                }
            }
            $data['tel_home'] = implode(',', $telHome);
            $data['tel_work'] = implode(',', $telWork);
            $data['tel_other'] = implode(',', $telOther);
        }

        if ($vCard->EMAIL) {
            $emailHome = array();
            $emailWork = array();
            $emailOther = array();
            foreach ($vCard -> EMAIL as $Email) {
                if (is_scalar($Email)) {
                    $emailOther[] = $this->_DefaultEmailTypes . ':' . $Email['Value'];
                } else {
                    $prefixKey = 0;
                    foreach ($this->_EmailTypes as $key => $emailType) {
                        if (in_array($emailType['fieldType'], $Email['Type'])) {
                            $prefixKey = $key;
                        }
                    }
                    if (!empty($this->_EmailTypes[$prefixKey])) {
                        switch ($this->_EmailTypes[$prefixKey]['fieldType']) {
                            case 'home':
                                $emailHome[] = $prefixKey . ':' . $Email['Value'];
                                break;
                            case 'work':
                                $emailWork[] = $prefixKey . ':' . $Email['Value'];
                                break;
                            case 'other':
                                $emailOther[] = $prefixKey . ':' . $Email['Value'];
                                break;
                        }
                    } else {
                        $emailOther[] = $this->_DefaultEmailTypes . ':' . $Email['Value'];
                    }
                }
            }
            $data['email_home'] = implode(',', $emailHome);
            $data['email_work'] = implode(',', $emailWork);
            $data['email_other'] = implode(',', $emailOther);
        }

        if ($vCard->URL) {
            $data['url'] = implode('\n', $vCard->URL);
        }

        if ($vCard->PHOTO) {
            foreach ($vCard->PHOTO as $Photo) {
                if ($Photo['Encoding'] == 'b') {
                    $vCard->SaveFile('photo', 0, Jaws_Utils::upload_tmp_dir() . '/test_image.' . $Photo['Type'][0]);
                    $data['image'] = 'test_image.' . $Photo['Type'][0];
                    break;
                }
            }
        }

		if ($vCard->ADR) {
            $adrHome = array();
            $adrWork = array();
            $adrOther = array();
			foreach ($vCard->ADR as $Address) {
                $adr = ($Address['StreetAddress'] ? $Address['StreetAddress'] . ' ' : '');
                $adr .= ($Address['POBox'] ? $Address['POBox'] . ' ' : '');
                $adr .= ($Address['ExtendedAddress'] ? $Address['ExtendedAddress'] . ' ' : '');
                $adr .= ($Address['Locality'] ? $Address['Locality'] . ' ' : '');
                $adr .= ($Address['Region'] ? $Address['Region'] . ' ' : '');
                $adr .= ($Address['PostalCode'] ? $Address['PostalCode'] . ' ' : '');
                $adr .= ($Address['Country'] ? $Address['Country'] . ' ' : '');

                if (in_array('home', $Address['Type'])) {
                    $adrHome[] = '1:' . $adr;
                } elseif (in_array('work', $Address['Type'])) {
                    $adrWork[] = '2:' . $adr;
                } else {
                    $adrOther[] = '3:' . $adr;
                }
			}
            $data['adr_home'] = implode('\n ', $adrHome);
            $data['adr_work'] = implode('\n ', $adrWork);
            $data['adr_other'] = implode('\n ', $adrOther);
		}

        $data['public'] = false;
        $data['[user]'] = (int) $GLOBALS['app']->Session->GetAttribute('user');
        return $data;
    }
}