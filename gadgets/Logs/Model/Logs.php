<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Model_Logs extends Jaws_Gadget_Model
{
    /**
     * Inserts a Log
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $priority   Priority of log
     * @param   array   $params     Action parameters
     * @return  mixed   Log identity or Jaws_Error on failure
     */
    function InsertLog($gadget, $action, $priority = 0, $params = null)
    {
        // ip address
        $ip = 0;
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            $ip = ($ip < 0)? ($ip + 0xffffffff + 1) : $ip;
        }
        // agent
        $agent = substr(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']), 0, 252);

        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->insert(
            array(
                'user'     => (int)$GLOBALS['app']->Session->GetAttribute('user'),
                'gadget'   => $gadget,
                'action'   => $action,
                'priority' => $priority,
                'params'   => $params,
                'apptype'  => JAWS_APPTYPE,
                'backend'  => JAWS_SCRIPT == 'admin',
                'ip'       => $ip,
                'agent'    => $agent,
                'status'   => 0,
                'insert_time' => time(),
            )
        );

        return $logsTable->exec();
    }

}