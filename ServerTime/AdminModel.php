<?php
/**
 * ServerTime Gadget
 *
 * @category   GadgetModel
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTimeAdminModel extends Jaws_Model
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  boolean  True on success and Jaws_Error on failure
     */
    function InstallGadget()
    {
        // Registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/ServerTime/date_format',  'DN d MN Y');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/ServerTime/date_format');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/ServerTime/display_format');
        $GLOBALS['app']->Registry->NewKey('/gadgets/ServerTime/date_format',  'DN d MN Y');
        return true;
    }

    /**
     * Sets the properties of ServerTime
     *
     * @access  public
     * @param   string  $format    The format of date and time being displayed
     * @return  boolean True if change was successful, otherwise returns Jaws_Error
     */
    function UpdateProperties($format)
    {
        $res = $GLOBALS['app']->Registry->Set('/gadgets/ServerTime/date_format', $format);
        if ($res) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SERVERTIME_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            $GLOBALS['app']->Registry->Commit('ServerTime');
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('SERVERTIME_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('SERVERTIME_ERROR_PROPERTIES_NOT_UPDATED'), _t('SERVERTIME_NAME'));
    }

}