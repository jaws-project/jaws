<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Model_Logs extends Jaws_Gadget_Model
{
    /**
     * Inserts a Log
     *
     * @access  public
     * @param   array   $dLog   Log information data
     * @return  mixed   Log identity or Jaws_Error on failure
     */
    function InsertLog($dLog)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($dLog),
            array('auth', 'domain', 'username', 'gadget', 'action', 'priority', 'params', 'status')
        );
        foreach ($invalids as $invalid) {
            unset($dLog[$invalid]);
        }

        // ip address
        $dLog['ip'] = 0;
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $_SERVER['REMOTE_ADDR'])) {
            $dLog['ip'] = ip2long($_SERVER['REMOTE_ADDR']);
            $dLog['ip'] = ($dLog['ip'] < 0)? ($dLog['ip'] + 0xffffffff + 1) : $dLog['ip'];
        }

        // agent
        $dLog['agent'] = substr(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']), 0, 252);

        // extra data
        $dLog['apptype'] = JAWS_APPTYPE;
        $dLog['backend'] = (JAWS_SCRIPT == 'admin');
        $dLog['params']  = isset($dLog['params'])? $dLog['params'] : null;
        $dLog['status']  = isset($dLog['status'])? (int)$dLog['status'] : 200;
        $dLog['insert_time'] = time();

        // register logs in syslogs if enabled
        if ($this->gadget->registry->fetch('syslog')) {
            openlog('jaws', LOG_NDELAY, LOG_USER);
            $syslog_message = $this->gadget->registry->fetch('syslog_format');
            $syslog_message = preg_replace_callback(
                '/{([[:digit:][:lower:]_]+)}/si',
                function ($matches) use($dLog) {
                    return $dLog[$matches[1]];
                },
                $syslog_message
            );

            syslog($dLog['priority'], $syslog_message);
            closelog();
        }

        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->insert($dLog);
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
            'id:integer', 'auth', 'domain', 'username', 'gadget', 'action', 'priority',
            'apptype', 'backend:boolean', 'ip', 'agent', 'status', 'insert_time'
        );
        $logsTable->orderBy('logs.id desc');
        $logsTable->limit((int)$limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('insert_time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('insert_time', $filters['to_date'], '<=');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && !empty($filters['action'])) {
                $logsTable->and()->where('action', $filters['action']);
            }
            // username
            if (isset($filters['username']) && !empty($filters['username'])) {
                $logsTable->and()->where('username', (int)$filters['username']);
            }
            // priority
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            // status
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('status', $filters['status']);
            }
        }

        return $logsTable->fetchAll();
    }

}