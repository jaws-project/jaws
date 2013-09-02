<?php
/**
 * AddressBook Installer
 *
 * @category   GadgetModel
 * @package    AddressBook
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AddressBook_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on Success and Jaws_Error on Failure
     */
    function Uninstall()
    {
        $tables = array('address_book',
                        'address_group',
                        'address_book_group');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('ADDRESSBOOK_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success and Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        return true;
    }

}