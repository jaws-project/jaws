<?php
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
class VisitCounter_Model_Admin_Visitors extends Jaws_Gadget_Model
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
        $table->orderBy('visit_time desc');
        $table->limit(15, $offset);

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
            return $result;
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
                return $result;
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_COUNTER_RESETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('VISITCOUNTER_ERROR_COUNTER_NOT_RESETED'));
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
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_COULD_NOT_CHANGE_STARTDATE'));
        }
        return true;
    }

}