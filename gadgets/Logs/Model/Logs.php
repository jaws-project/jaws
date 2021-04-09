<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
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
            array(
                'auth', 'domain', 'user', 'username', 'gadget', 'action',
                'priority', 'input', 'output', 'result', 'status'
            )
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
        $dLog['input']   = json_encode(isset($dLog['input'])? $dLog['input'] : null);
        $dLog['output']  = json_encode(isset($dLog['output'])? $dLog['output'] : null);
        $dLog['result']  = isset($dLog['result'])? (int)$dLog['result'] : 0;
        // temporary status 1: true, 2: false
        $dLog['status']  = isset($dLog['status'])? ((bool)$dLog['status']? 1 : 2) : 1;
        $dLog['time']    = time();

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
            'logs.id:integer', 'auth', 'logs.domain', 'user', 'username',
            'gadget', 'action', 'priority', 'apptype', 'backend:boolean', 'ip', 'agent',
            'result', 'logs.status', 'logs.time'
        );
        $logsTable->orderBy('logs.id desc');
        $logsTable->limit((int)$limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            $objDate = Jaws_Date::getInstance();
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $filters['from_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $filters['from_date'] . ' 0:0:0'), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $filters['to_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $filters['to_date']. ' 23:59:59'), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['to_date'], '<=');
            }

            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && !empty($filters['action'])) {
                $logsTable->and()->where('action', $filters['action']);
            }
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $logsTable->and()->where('user', (int)$filters['user']);
            }
            // priority
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            // result
            if (isset($filters['result']) && !empty($filters['result'])) {
                $logsTable->and()->where('result', $filters['result']);
            }
            // status
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('logs.status', $filters['status']);
            }
        }

        return $logsTable->fetchAll();
    }

    /**
     * Gets logs count
     *
     * @access  public
     * @param   array   $filters   log filters
     * @return  mixed   Count of available logs and Jaws_Error on failure
     */
    function GetLogsCount($filters = null)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select('count(id):integer');

        if (!empty($filters) && count($filters) > 0) {
            $objDate = Jaws_Date::getInstance();
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $filters['from_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $filters['from_date'] . ' 0:0:0'), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $filters['to_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[\/\- \:]/', $filters['to_date']. ' 23:59:59'), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['to_date'], '<=');
            }
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('logs.gadget', $filters['gadget']);
            }
            if (isset($filters['action']) && !empty($filters['action'])) {
                $logsTable->and()->where('action', $filters['action']);
            }
            if (isset($filters['user']) && !empty($filters['user'])) {
                $logsTable->and()->where('user', $filters['user']);
            }
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            if (isset($filters['result']) && !empty($filters['result'])) {
                $logsTable->and()->where('logs.result', $filters['result']);
            }
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('logs.status', $filters['status']);
            }
        }

        return $logsTable->fetchOne();
    }

}