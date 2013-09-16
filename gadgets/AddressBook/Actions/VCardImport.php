<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/resources/site_style.css');
class AddressBook_Actions_VCardImport extends AddressBook_HTML
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

        $this->SetTitle(_t('ADDRESSBOOK_IMPORT_VCART'));
        $tpl = $this->gadget->loadTemplate('VCardImport.html');
        $tpl->SetBlock("vcard");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_IMPORT_VCART'));
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
        try {
            $vCard = new vCard($_FILES['vcard_file']['tmp_name'], false, array('Collapse' => false));

            $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
            if (count($vCard) == 0) {
                // TODO: Show meesage: No entry find
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
            return 'error';
        }

        $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_IMPORT_COMPLETED'), 'AddressBook');
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
            $data['url'] = implode('/n', $vCard->URL);
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

        $data['public'] = false;
        $data['[user]'] = (int) $GLOBALS['app']->Session->GetAttribute('user');
        return $data;
    }
}