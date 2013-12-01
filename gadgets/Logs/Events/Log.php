<?php
/**
 * Logs Log event
 *
 * @category    Gadget
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Events_Log extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $priority   Priority of log
     * @param   array   $params     Action parameters
     * @return  mixed   Log identity or Jaws_Error on failure
     */
    function Execute($gadget, $action, $priority = 0, $params = null)
    {
        $priority = empty($priority)? JAWS_INFO : (int)$priority;
        if ($priority <= (int)$this->gadget->registry->fetch('log_priority_level')) {
            $logsModel = $this->gadget->model->load('Logs');
            return $logsModel->InsertLog($gadget, $action, $priority, $params);
        }

        return false;
    }

}