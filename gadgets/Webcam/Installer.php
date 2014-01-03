<?php
/**
 * Webcam Installer
 *
 * @category    GadgetModel
 * @package     Webcam
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('limit_random', '3'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'AddWebcam',
        'EditWebcam',
        'DeleteWebcam',
        'UpdateProperties'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('webcam');
        if (Jaws_Error::IsError($result)) {
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
            return new Jaws_Error($errMsg);
        }

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
            $layoutModel->EditGadgetLayoutAction('Webcam', 'Display', 'Display', 'Webcam');
            $layoutModel->EditGadgetLayoutAction('Webcam', 'Random', 'Random', 'Webcam');
        }

        return true;
    }

}