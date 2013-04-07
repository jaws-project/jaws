<?php
/**
 * Menu Installer
 *
 * @category    GadgetModel
 * @package     Menu
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Menu_Installer extends Jaws_Gadget_Installer
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

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Add listener for remove/publish menu items related to given gadget
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');

        // Registry keys
        $this->gadget->AddRegistry('default_group_id', '1');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed     True on success or Jaws_Error on failure
     */
    function Uninstall()
    {
        $tables = array('menus',
                        'menus_groups');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('MENU_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->DelRegistry('default_group_id');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.7.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Remove old event listener
            $GLOBALS['app']->Listener->DeleteListener($this->gadget->name);
            // Add listener for remove/publish menu items related to given gadget
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');
        }

        return true;
    }

}