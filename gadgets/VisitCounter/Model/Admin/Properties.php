<?php
/**
 * Visit Counter Gadget Admin
 *
 * @category   GadgetModel
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Updates VisitCounter settings
     *
     * @access  public
     * @param   string  $counters   Enabled visit counters
     * @param   int     $numdays    Number of days
     * @param   int     $type       Type of calculation (unique/impressions)
     * @param   int     $mode       Display type (text/image)
     * @param   string  $custom_text    Custome text to be displayed
     * @return  bool    True if change was successful, otherwise returns Jaws_Error
     */
    function UpdateProperties($counters, $numdays, $type, $mode, $custom_text='')
    {
        $rs1 = $this->gadget->registry->update('visit_counters', $counters);
        $rs2 = $this->gadget->registry->update('period', $numdays);
        $rs3 = $this->gadget->registry->update('type', $type);
        $rs4 = $this->gadget->registry->update('mode', $mode);
        $rs5 = $this->gadget->registry->update('custom_text', $custom_text);
        if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
            $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_PROPERTIES_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('VISITCOUNTER_ERROR_PROPERTIES_UPDATED'));
    }
}