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
        // gadget
        $params['gadget'] =
            isset($params['gadget'])?
            $params['gadget'] :
            $shouter;
        // auth
        $params['auth'] =
            isset($params['auth'])?
            (string)$params['auth'] :
            $this->app->session->getAttribute('auth');
        // domain
        $params['domain'] =
            isset($params['domain'])?
            (int)$params['domain'] :
            $this->app->session->getAttribute('domain');
        // username
        $params['username'] =
            isset($params['username'])?
            (string)$params['username'] :
            $this->app->session->getAttribute('username');
        // priority
        $params['priority'] = empty($params['priority'])? JAWS_INFO : (int)$params['priority'];

        // log events if user logged
        if (empty($params['username']) ||
            $params['priority'] > (int)$this->gadget->registry->fetch('log_priority_level')
        ) {
            return false;
        }

        return $this->gadget->model->load('Logs')->InsertLog($params);
    }

}