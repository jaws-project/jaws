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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        require_once ROOT_JAWS_PATH . 'gadgets/Users/include/vCard.php';

        $currentUser = $this->app->session->user->id;
        $jUser = new Jaws_User;
        $contacts = $jUser->GetUserContacts($currentUser);

        $result = '';
        foreach ($contacts as $contact) {
            $vCard = new vCard;

            $vCard->n($contact['name'], 'LastName');
//            $vCard->fn($names[3] . (trim($names[3]) == '' ?  '' : ' ') . $names[1] . (trim($names[1]) == '' ? '' : ' ') . $names[0]);
//            $vCard->nickname($contact['nickname']);
            $vCard->title($contact['title']);

            $keys = array('tel' => 'voice', 'mobile' => 'cell', 'fax' => 'fax');
            $props = array('home', 'work', 'other');
            foreach ($keys as $key => $type) {
                $keyValues = array_filter(json_decode($contact[$key], true));
                foreach ($props as $prop) {
                    if (array_key_exists($prop, $keyValues)) {
                        $vCard->tel($keyValues[$prop], $prop, $type);
                    }
                }
            }

            if (!empty($contact['email'])) {
                $email = array_filter(json_decode($contact['email'], true));
                foreach ($props as $prop) {
                    if (array_key_exists($prop, $email)) {
                        $vCard->email($email[$prop], $prop);
                    }
                }
            }

            if (!empty($contact['url'])) {
                $url = array_filter(json_decode($contact['url'], true));
                foreach ($props as $prop) {
                    if (array_key_exists($prop, $url)) {
                        $vCard->url($url[$prop], $prop);
                    }
                }
            }

            if (!empty($contact['address'])) {
                $adr = json_decode($contact['address'], true);
                foreach ($props as $prop) {
                    if (empty($adr[$prop]) || empty(array_filter($adr[$prop]))) {
                        continue;
                    }

                    $vCard->adr('', 'POBox');
                    $vCard->adr('', 'ExtendedAddress');
                    $vCard->adr($adr[$prop]['address'], 'StreetAddress');
                    $vCard->adr($adr[$prop]['city'], 'Locality');
                    $vCard->adr($adr[$prop]['province'], 'Region');
                    $vCard->adr($adr[$prop]['postal_code'], 'PostalCode');
                    $vCard->adr($adr[$prop]['country'], 'Country');
                }
            }

            $vCard->note($contact['note']);
            $result = $result . $vCard;
        }

        header('Content-Disposition: attachment; filename="contacts.vcf"');
        header('Content-type: application/csv');
        header('Content-Length: ' . strlen($result));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Connection: close');

        return $result;
    }

    /**
     * Importing vCard file to user's contacts
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function ImportVCard()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        if (!empty($_FILES)) {
            $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), '', null);
            if (Jaws_Error::IsError($res) || !isset($res['file'][0])) {
                return $this->gadget->session->response(
                    _t('GLOBAL_ERROR_UPLOAD'),
                    RESPONSE_ERROR
                );
            }

            $inputVcards = @file_get_contents(Jaws_Utils::upload_tmp_dir() . '/' . $res['file'][0]['host_filename']);
        } else {
            $inputVcards = Jaws_Request::getInstance()->rawData('input');
        }

        try {
            require_once ROOT_JAWS_PATH . 'gadgets/Users/include/vCard.php';
            $vCard = new vCard(
                false,
                $inputVcards,
                array('Collapse' => false)
            );
            if (count($vCard) == 0) {
                return $this->gadget->session->response(
                    _t('USERS_ERROR_VCARD_DATA_NOT_FOUND'),
                    RESPONSE_ERROR
                );
            }

            $jUser = new Jaws_User;
            $currentUser = $this->app->session->user->id;
            if (count($vCard) == 1) {
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
            return $this->gadget->session->response(
                $e->getMessage(),
                RESPONSE_ERROR
            );
        }

        if(Jaws_Error::IsError($contactId)) {
            return $this->gadget->session->response(
                $contactId->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            _t('USERS_VCARD_IMPORT_COMPLETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Prepare data to insert in database
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function PrepareForImport(&$vCard)
    {
        $data['name']  = Jaws_UTF8::trim($vCard->N[0]['LastName'] . ' ' . $vCard->N[0]['FirstName']);
        $data['name'] = $data['name']?: (@$vCard->FN[0]?: @$vCard->NICKNAME[0]);
        if (empty($data['name'])) {
            return false; // TODO: Show message: Can not import data without name
        }
        $data['title'] = (@$vCard->TITLE[0])?: (@$vCard->FN[0]?: $data['name']);
        $data['note']  = @$vCard->NOTE[0];

        $mappedTelType = array('voice' => 'tel', 'cell' => 'mobile', 'fax' => 'fax');
        $data['tel'] = $data['fax'] = $data['mobile'] = $data['email'] = $data['url'] = $data['address'] = array(
            'home' => '',
            'work' => '',
            'other' => ''
        );

        // tel/mobile/fax
        foreach ($vCard->TEL as $tel) {
            if (!is_array($tel)) {
                $data['tel']['home'] = $tel;
                continue;
            }

            $telProp = array_intersect(array('home', 'work', 'other'), $tel['Type']);
            if (empty($telProp)) {
                $telProp = array('home');
            }

            $telType = array_intersect(array('voice', 'cell', 'fax'), $tel['Type']);
            if (empty($telType)) {
                $telType = array('voice');
            }

            $data[$mappedTelType[reset($telType)]][reset($telProp)] = $tel['Value'];
        }
        $data['tel']    = json_encode($data['tel']);
        $data['fax']    = json_encode($data['fax']);
        $data['mobile'] = json_encode($data['mobile']);

        // email
        foreach ($vCard->EMAIL as $email) {
            if (!is_array($email)) {
                $data['email']['home'] = $email;
                continue;
            }

            $email['Type'] = array_key_exists('Type', $email)? $email['Type'] : array('home');
            $emailProp = array_intersect(array('home', 'work', 'other'), $email['Type']);
            if (empty($emailProp)) {
                continue;
            }

            $data['email'][reset($emailProp)] = $email['Value'];
        }
        $data['email'] = json_encode($data['email']);

        // url
        foreach ($vCard->URL as $url) {
            if (!is_array($url)) {
                $data['url']['home'] = $url;
                continue;
            }

            $urlProp = array_intersect(array('home', 'work', 'other'), $url['Type']);
            if (empty($urlProp)) {
                continue;
            }

            $data['url'][reset($urlProp)] = $url['Value'];
        }
        $data['url'] = json_encode($data['url']);

        // address
        foreach ($vCard->ADR as $address) {
            $adrProp = array_intersect(array('home', 'work', 'other'), $address['Type']);
            if (empty($adrProp)) {
                continue;
            }

            $data['address'][reset($adrProp)] = array(
                'address'     => $address['StreetAddress'],
                'city'        => $address['Locality'],
                'province'    => $address['Region'],
                'postal_code' => $address['PostalCode'],
                'country'     => $address['Country']
            );
        }
        $data['address'] = json_encode($data['address']);

        // photo
        if ($vCard->PHOTO) {
            $data['image'] = json_encode($vCard->PHOTO[0]);
        }

        return $data;
    }
}