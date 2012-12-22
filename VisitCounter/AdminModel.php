<?php
require_once JAWS_PATH . 'gadgets/VisitCounter/Model.php';
/**
 * Visit Counter Gadget Admin
 *
 * @category   GadgetModel
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounterAdminModel extends VisitCounterModel
{
    /**
     * Gets list of IP visitors / date visited
     *
     * @access  public
     * @param   int     $limit  Data limit to fetch
     * @return  array   Array of visitors or Jaws_Error on failure
     */
    function GetVisitors($limit = null)
    {
        $sql = '
            SELECT
                [ip], [visit_date], [visits]
            FROM [[ipvisitor]]';
        if (!is_null($limit)) {
            $sql .= ' ORDER BY [visit_date] DESC';

            $result = $GLOBALS['db']->setLimit(15, $limit);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error($result->getMessage(), 'SQL');
            }
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Clears the visitors table
     *
     * @access  private
     * @return  mixedTrue if change was successful, otherwise returns Jaws_Error
     */
    function ClearVisitors()
    {
        $sql    = 'DELETE FROM [[ipvisitor]]';
        $result = $GLOBALS['db']->query($sql);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_VISITORS_NOT_CLEARED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_VISITORS_NOT_CLEARED'), _t('VISITCOUNTER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_VISITORS_CLEARED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Resets the counter to zero
     *
     * @access  public
     * @return  mixed   True if change was successful, otherwise returns Jaws_Error
     */
    function ResetCounter()
    {
        if (!Jaws_Error::IsError($this->ClearVisitors())) {
            $sql = 'UPDATE [[ipvisitor]] SET [visits] = 0';
            $result = $GLOBALS['db']->query($sql);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'), _t('VISITCOUNTER_NAME'));
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_COUNTER_RESETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'), _t('VISITCOUNTER_NAME'));
    }

    /**
     * Updates VisitCounter settings
     *
     * @access  public
     * @param   bool    $online     Include online visitors
     * @param   bool    $today      Include today visitors
     * @param   bool    $total      Include total visitors
     * @param   bool    $custom     Display custom text
     * @param   int     $numdays    Number of days
     * @param   int     $type       Type of calculation (unique/impressions)
     * @param   int     $mode       Display type (text/image)
     * @param   string  $custom_text    Custome text to be displayed
     * @return  bool    True if change was successful, otherwise returns Jaws_Error
     */
    function UpdateProperties($online, $today, $total, $custom, $numdays, $type, $mode, $custom_text='')
    {
        if ($online) {
            $visit_counters[] = 'online';
        }
        if ($today) {
            $visit_counters[] = 'today';
        }
        if ($total) {
            $visit_counters[] = 'total';
        }
        if ($custom) {
            $visit_counters[] = 'custom';
        }
        $rs1 = $this->gadget->SetRegistry('visit_counters', implode(',', $visit_counters));
        $rs2 = $this->gadget->SetRegistry('period', $numdays);
        $rs3 = $this->gadget->SetRegistry('type',   $type);
        $rs4 = $this->gadget->SetRegistry('mode', $mode);
        $rs5 = $this->gadget->SetRegistry('custom_text', $custom_text);
        if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
            $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_PROPERTIES_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('VISITCOUNTER_ERROR_PROPERTIES_UPDATED'), _t('VISITCOUNTER_NAME'));
    }

    /**
     * Sets the initial date for visit counter
     *
     * @access  public
     * @param   string  $date StartDate
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function SetStartDate($date)
    {
        $rs = $this->gadget->SetRegistry('start', $date);
        if (!$rs || Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_COULD_NOT_CHANGE_STARTDATE'), _t('VISITCOUNTER_NAME'));
        }
        return true;
    }

}