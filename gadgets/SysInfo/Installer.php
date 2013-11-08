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
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'SysInfo',
        'PHPInfo',
        'JawsInfo',
        'DirInfo',
    );

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
        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('SysInfo', 'SysInfo', 'SysInfo', 'SysInfo');
            $layoutModel->EditGadgetLayoutAction('SysInfo', 'PHPInfo', 'PHPInfo', 'PHPInfo');
            $layoutModel->EditGadgetLayoutAction('SysInfo', 'JawsInfo', 'JawsInfo', 'JawsInfo');
            $layoutModel->EditGadgetLayoutAction('SysInfo', 'DirInfo', 'DirInfo', 'DirInfo');
        }

        return true;
    }

}