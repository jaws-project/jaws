<?php
/**
 * SysInfo Installer
 *
 * @category    GadgetModel
 * @package     SysInfo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SysInfo_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        // Registry keys
        $this->gadget->registry->del('frontend_avail');

        // ACL keys
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/SysInfo/SysInfo',  'false');
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/SysInfo/PHPInfo',  'false');
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/SysInfo/JawsInfo', 'false');
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/SysInfo/DirInfo',  'false');

        return true;
    }

}