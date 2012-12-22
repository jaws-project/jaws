<?php
/**
 * VisitCounter Installer
 *
 * @category    GadgetModel
 * @package     VisitCounter
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Installer extends Jaws_Gadget_Installer
{
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
        $this->gadget->AddRegistry('visit_counters',  'online,today,total');
        $this->gadget->AddRegistry('timeout', '600');
        $this->gadget->AddRegistry('type', 'impressions');
        $this->gadget->AddRegistry('period', '0');
        $this->gadget->AddRegistry('start', date('Y-m-d H:i:s'));
        $this->gadget->AddRegistry('mode', 'text');
        $this->gadget->AddRegistry('custom_text', 
                                          '<strong>Total Visitors:</strong> <font color="red">{total}</font>');

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
        $result = $GLOBALS['db']->dropTable('ipvisitor');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('VISITCOUNTER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $this->gadget->DelRegistry('visit_counters');
        $this->gadget->DelRegistry('timeout');
        $this->gadget->DelRegistry('type');
        $this->gadget->DelRegistry('period');
        $this->gadget->DelRegistry('start');
        $this->gadget->DelRegistry('mode');
        $this->gadget->DelRegistry('custom_text');

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
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (version_compare($old, '0.8.0', '<')) {
            // Registry keys.
            $this->gadget->AddRegistry('visit_counters',  'online,today,total');
            $this->gadget->AddRegistry('custom_text', 
                                              $this->gadget->GetRegistry('custom'));
            $this->gadget->DelRegistry('online');
            $this->gadget->DelRegistry('today');
            $this->gadget->DelRegistry('total');
            $this->gadget->DelRegistry('custom');
        }

        if (version_compare($old, '0.8.1', '<')) {
            // fix using Y-m-d G:i:s instead of Y-m-d H:i:s in version 0.6.x
            $startDate = $this->gadget->GetRegistry('start');
            if (strlen($startDate) == 18) {
                $startDate = substr_replace($startDate, '0', 11, 0);
                $this->gadget->SetRegistry('start', $startDate);
            }
        }

        return true;
    }

}