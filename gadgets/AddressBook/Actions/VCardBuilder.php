<?php
require_once JAWS_PATH. 'gadgets/AddressBook/Actions/Default.php';
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_VCardBuilder extends AddressBook_Actions_Default
{
    /**
     * Build and export data with VCard format
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function VCardBuild()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        require_once JAWS_PATH . 'gadgets/Addressbook/vCard.php';

        $model = $this->gadget->loadModel('AddressBook');
        $agModel = $this->gadget->loadModel('AddressBookGroup');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $ids =  jaws()->request->fetch('adr:array');

        $link = $this->gadget->urlMap('AddressBook', array(), true);
        if (empty($ids)) {
            Jaws_Header::Location($link);
            return false;
        }

        $addressItems = $model->GetAddresses($ids, $user);
        if (Jaws_Error::IsError($addressItems) || empty($addressItems)) {
            return Jaws_HTTPError::Get(404);
        }

        $result = '';
        $nVCard = array('LastName', 'FirstName', 'AdditionalNames', 'Prefixes', 'Suffixes');
        foreach ($addressItems as $addressItem) {
            $vCard = new vCard;

            $names = explode(';', $addressItem['name']);
            foreach ($names as $key => $name) {
                 $vCard->n($name, $nVCard[$key]);
            }
            $vCard->fn($names[3] . (trim($names[3]) == '' ?  '' : ' ') . $names[1] . (trim($names[1]) == '' ? '' : ' ') . $names[0]);
            $vCard->nickname($addressItem['nickname']);
            $vCard->title($addressItem['title']);

            $adrGroups = $agModel->GetGroupNames($addressItem['address_id'], $user);
            $vCard->categories(implode(',', $adrGroups));

            $this->FillVCardTypes($vCard, 'tel', $addressItem['tel_home'], $this->_TelTypes);
            $this->FillVCardTypes($vCard, 'tel', $addressItem['tel_work'], $this->_TelTypes);
            $this->FillVCardTypes($vCard, 'tel', $addressItem['tel_other'], $this->_TelTypes);

            $this->FillVCardTypes($vCard, 'email', $addressItem['email_home'], $this->_EmailTypes);
            $this->FillVCardTypes($vCard, 'email', $addressItem['email_work'], $this->_EmailTypes);
            $this->FillVCardTypes($vCard, 'email', $addressItem['email_other'], $this->_EmailTypes);

            $this->FillVCardTypes($vCard, 'adr', $addressItem['adr_home'], $this->_AdrTypes, '\n');
            $this->FillVCardTypes($vCard, 'adr', $addressItem['adr_work'], $this->_AdrTypes, '\n');
            $this->FillVCardTypes($vCard, 'adr', $addressItem['adr_other'], $this->_AdrTypes, '\n');

            $this->FillVCardTypes($vCard, 'url', $addressItem['url'], null, '\n');
            $vCard->note($addressItem['notes']);

            $result = $result . $vCard;
        }

        header("Content-Disposition: attachment; filename=\"" . 'address.vcf' . "\"");
        header("Content-type: application/csv");
        header("Content-Length: " . strlen($result));
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Connection: close");

        echo $result;
        exit;
    }

    /**
     * Fill data in vcard format
     *
     * @access  public
     * @param   object  $vCard
     * @param   string  $base_block
     * @param   array   $inputValue
     * @param   array   $options
     * @return  string  XHTML template content
     */
    function FillVCardTypes(&$vCard, $dataType, $inputValue, $options = null, $seperatChar = ',')
    {
        if (trim($inputValue) == '') {
            return;
        }
        $inputValue = explode($seperatChar, trim($inputValue));
        foreach ($inputValue as $val) {
            $result = explode(':', $val);
            if ($dataType == 'tel') {
                $vCard->tel($result[1], $options[$result[0]]['fieldType'], $options[$result[0]]['telType']);
            } else if ($dataType == 'adr') {
                $vCard->adr('', $options[$result[0]]['fieldType']);
                $vCard->adr($result[1], 'ExtendedAddress');
                //$vCard->label($result[1], $options[$result[0]]['fieldType']);
            } else if ($dataType == 'url') {
                $vCard->url($val);
            } else {
                $vCard->$dataType($result[1], $options[$result[0]]['fieldType']);
            }
        }
    }
}