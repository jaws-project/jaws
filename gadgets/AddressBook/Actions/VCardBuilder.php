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
class AddressBook_Actions_VCardBuilder extends Jaws_Gadget_HTML
{
    /**
     * Build and export data with VCard format
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function VCardBuild()
    {
        require_once PEAR_PATH . 'File/IMC.php';

        // instantiate a builder object
        // (defaults to version 3.0)
        $vcard = File_IMC::build('vCard');
var_dump($vcard);
        // set a formatted name
        $vcard->setFormattedName('Bolivar Shagnasty');

        // set the structured name parts
        $vcard->setName('Shagnasty', 'Bolivar', 'Odysseus', 'Mr.', 'III');

        // add a work email.  note that we add the value
        // first and the param after -- Contact_Vcard_Build
        // is smart enough to add the param in the correct
        // place.
        $vcard->addEmail('boshag@example.com');
        $vcard->addParam('TYPE', 'WORK');

        // add a home/preferred email
        $vcard->addEmail('bolivar@example.net');
        $vcard->addParam('TYPE', 'HOME');
        $vcard->addParam('TYPE', 'PREF');

        // add a work address
        $vcard->addAddress('POB 101', 'Suite 202', '123 Main',
                           'Beverly Hills', 'CA', '90210', 'US');
        $vcard->addParam('TYPE', 'WORK');

        // get back the vCard and print it
        $text = $vcard->fetch();
        echo '<pre>';
        print_r($text);
        echo '</pre>';
    }
}