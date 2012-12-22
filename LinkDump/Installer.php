<?php
/**
 * LinkDump Installer
 *
 * @category    GadgetModel
 * @package     LinkDump
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on a successfull Install and Jaws_Error on errors
     */
    function Install()
    {
        $new_dir = JAWS_DATA . 'xml' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('LINKDUMP_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry key
        $this->gadget->AddRegistry('max_limit_count', '100');
        $this->gadget->AddRegistry('links_target', 'blank');

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
        $tables = array('linkdump_links',
                        'linkdump_groups',
                        'linkdump_tags',
                        'linkdump_links_tags');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('LINKDUMP_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // registry keys
        $this->gadget->DelRegistry('max_limit_count');
        $this->gadget->DelRegistry('links_target');

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
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (version_compare($old, '0.4.0', '<')) {
            $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageLinks', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageGroups', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageTags',   'true');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/AddLink');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/UpdateLink');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/DeleteLink');

            // Registry keys.
            $this->gadget->AddRegistry('max_limit_count', '100');
            $this->gadget->AddRegistry('links_target', 'blank');
            $this->gadget->DelRegistry('limitation');
            $this->gadget->DelRegistry('target');
        }

        return true;
    }

}