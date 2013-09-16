<?php
/**
 * AddressBook Actions file
 *
 * @category   Gadget
 * @package    AddressBook
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AddressBook_HTML extends Jaws_Gadget_HTML
{
    /**
     * Telephone Types
     * @var     array
     * @access  private
     */
    var $_TelTypes = array(
        1 => array('fieldType' => 'home', 'telType' => 'voice', 'lang' => 'HOME_TELL'),
        2 => array('fieldType' => 'home', 'telType' => 'cell', 'lang' => 'HOME_MOBILE'),
        3 => array('fieldType' => 'home', 'telType' => 'fax', 'lang' => 'HOME_FAX'),
        4 => array('fieldType' => 'work', 'telType' => 'voice', 'lang' => 'WORK_TELL'),
        5 => array('fieldType' => 'work', 'telType' => 'cell', 'lang' => 'WORK_MOBILE'),
        6 => array('fieldType' => 'work', 'telType' => 'fax', 'lang' => 'WORK_FAX'),
        7 => array('fieldType' => 'other', 'telType' => 'voice', 'lang' => 'OTHER_TELL'),
        8 => array('fieldType' => 'other', 'telType' => 'cell', 'lang' => 'OTHER_MOBILE'),
        9 => array('fieldType' => 'other', 'telType' => 'fax', 'lang' => 'OTHER_FAX'),
    );
    var $_DefaultTelTypes = array('voice' => 7, 'cell' => 8, 'fax' => 9);

    /**
     * Email Types
     * @var     array
     * @access  private
     */
    var $_EmailTypes = array(
        1 => array('fieldType' => 'home', 'lang' => 'HOME_EMAIL'),
        2 => array('fieldType' => 'work', 'lang' => 'WORK_EMAIL'),
        3 => array('fieldType' => 'other', 'lang' => 'OTHER_EMAIL'),
    );
    var $_DefaultEmailTypes = 3;

    /**
     * Address Types
     * @var     array
     * @access  private
     */
    var $_AdrTypes = array(
        1 => array('fieldType' => 'home', 'lang' => 'HOME_ADR'),
        2 => array('fieldType' => 'work', 'lang' => 'WORK_ADR'),
        3 => array('fieldType' => 'other', 'lang' => 'OTHER_ADR'),
    );

    /**
     * Get lists of phone number, email address, address
     *
     * @access  public
     * @param   object  $tpl
     * @param   string  $base_block
     * @param   array   $inputValue
     * @param   array   $options
     * @return  string  XHTML template content
     */
    function GetItemsLable(&$tpl, $base_block, $inputValue, $options = null)
    {
        foreach ($inputValue as $val) {
            $tpl->SetBlock("address/$base_block");
            if (isset($options)) {
                $result = explode(':', $val);
                $tpl->SetVariable('item', $result[1]);
                $tpl->SetVariable('lbl_item', _t('ADDRESSBOOK_' . $options[$result[0]]['lang']));
            } else {
                $tpl->SetVariable('item', $val);
                $tpl->SetVariable('lbl_item', _t('ADDRESSBOOK_ITEMS_URL'));
            }
            $tpl->ParseBlock("address/$base_block");
        }
    }
}