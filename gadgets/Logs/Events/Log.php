<?php
/**
 * Logs Log event
 *
 * @category    Gadget
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Events_Log extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     * @access  public
     * @param   string  $shouter    Shouter name
     * @param   array   $params     Log information parameters
     * @return  mixed   Log identity or Jaws_Error on failure
     */
    function Execute($shouter, $params)
    {
        if (!isset($GLOBALS['app']->Session)) {
            return false;
        }

        $params['authtype'] = isset($params['authtype'])? $params['authtype'] : 'Default';
        $params['priority'] = isset($params['priority'])? (int)$params['priority'] : JAWS_INFO;
        $params['username'] =
            isset($params['username'])?
            (string)$params['username'] :
            $GLOBALS['app']->Session->GetAttribute('username');

        // log events if user logged
        if (empty($params['username']) ||
            $params['priority'] > (int)$this->gadget->registry->fetch('log_priority_level')
        ) {
            return false;
        }

        return $this->gadget->model->load('Logs')->InsertLog($params);
    }

}