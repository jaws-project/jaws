<?php
/**
 * AddressBook Actions file
 *
 * @category   Gadget
 * @package    AddressBook
 */
class AddressBook_Actions_Default extends Jaws_Gadget_Action
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
     * Displays menu bar according to selected action
     *
     * @access  public
     * @param   string  $action_selected    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action_selected)
    {
        $actions = array('AddressBook', 'Groups');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'AddressBook';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('AddressBook',_t('ADDRESSBOOK_ADDRESSBOOK_MANAGE'),
            $this->gadget->urlMap('AddressBook'), 'gadgets/AddressBook/Resources/images/contact.png');

        $menubar->AddOption('Groups',_t('ADDRESSBOOK_GROUPS_MANAGE'),
            $this->gadget->urlMap('ManageGroups'), 'gadgets/AddressBook/Resources/images/groups_mini.png');

        $menubar->Activate($action_selected);

        return $menubar->Get();
    }
}