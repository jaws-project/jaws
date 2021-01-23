<?php
/**
 * VisitCounter Installer
 *
 * @category    GadgetModel
 * @package     VisitCounter
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    // @FIXME: Use Double Braces for 'custom_text' Default Value.
    // @FIXME: According to MDN (https://developer.mozilla.org/en-US/docs/Web/HTML/Element/font)
    //  Font Tag Was Deprecated in HTML4.01 and Obsoleted in HTML5.
    var $_RegKeys = array(
        array('timeout', '600'),
        array('type', 'impressions'),
        array('period', '0'),
        array('start', ''),
        array('mode', 'text'),
        array('custom_text', '<strong>Total Visitors:</strong> <font color="red">{total}</font>'),
        array('unique_visits', '0'),
        array('visit_counters', 'online,today,yesterday,total'),
        array('impression_visits', '0'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ResetCounter',
        'CleanEntries',
        'UpdateProperties'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->registry->update('start', date('Y-m-d H:i:s'));

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Uninstall()
    {
        $result = Jaws_DB::getInstance()->dropTable('ipvisitor');
        if (Jaws_Error::IsError($result)) {
            $errMsg = Jaws::t('ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
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
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        return true;
    }

}