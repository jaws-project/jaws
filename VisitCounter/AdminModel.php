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
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_AdminModel extends VisitCounter_Model
{
    /**
     * Gets list of IP visitors / date visited
     *
     * @access  public
     * @param   int     $offset  Data offset to fetch
     * @return  array   Array of visitors or Jaws_Error on failure
     */
    function GetVisitors($offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('ipvisitor');
        $table->select(array('ip:integer', 'visit_time:integer', 'visits:integer'));
        $table->orderBy('visit_time DESC');
        if (!is_null($offset)) {
            $table->limit(15, $offset);
        }

        return $table->fetchAll();
    }

    /**
     * Clears the visitors table
     *
     * @access  private
     * @return  mixedTrue if change was successful, otherwise returns Jaws_Error
     */
    function ClearVisitors()
    {
        $table = Jaws_ORM::getInstance()->table('ipvisitor');
        $result = $table->delete()->exec();
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
            $table = Jaws_ORM::getInstance()->table('ipvisitor');
            $result = $table->update(array('visits', 0))->exec();
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
        $rs = $this->gadget->registry->update('start', $date);
        if (!$rs || Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_COULD_NOT_CHANGE_STARTDATE'), _t('VISITCOUNTER_NAME'));
        }
        return true;
    }

}