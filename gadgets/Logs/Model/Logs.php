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
     * @param   int     $status     Status code
     * @return  mixed   Log identity or Jaws_Error on failure
     */
    function InsertLog($user, $gadget, $action, $priority = 0, $params = null, $status = 200)
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
                'user'     => (int)$user,
                'gadget'   => $gadget,
                'action'   => $action,
                'priority' => $priority,
                'params'   => $params,
                'apptype'  => JAWS_APPTYPE,
                'backend'  => JAWS_SCRIPT == 'admin',
                'ip'       => $ip,
                'agent'    => $agent,
                'status'   => (int)$status,
                'insert_time' => time(),
            )
        );

        return $logsTable->exec();
    }

    /**
     * Get a list of the Logs
     *
     * @access  public
     * @param   array    $filters   log filters
     * @param   bool|int $limit     Count of logs to be returned
     * @param   int      $offset    Offset of data array
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function GetLogs($filters = null, $limit = false, $offset = null)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select(
            'logs.id:integer', 'gadget', 'action', 'nickname', 'username', 'users.id as user:integer',
            'apptype', 'backend:boolean', 'ip', 'agent', 'logs.status', 'logs.insert_time'
        );
        $logsTable->join('users', 'users.id', 'logs.user', 'left');
        $logsTable->orderBy('id desc');
        $logsTable->limit((int)$limit, $offset);

        if (!empty($filters) && count($filters) > 1) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['to_date'], '<=');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('logs.gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && !empty($filters['action'])) {
                $logsTable->and()->where('logs.action', $filters['action']);
            }
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $logsTable->and()->where('user', (int)$filters['user']);
            }
            // priority
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            // status
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('logs.status', $filters['status']);
            }
        }

        return $logsTable->fetchAll();
    }

}