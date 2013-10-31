<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 */
class Logs_Model_Admin_Logs extends Jaws_Gadget_Model
{
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
        $logsTable->select('logs.id:integer', 'ip', 'agent', 'gadget', 'action', 'logs.insert_time',
                           'title', 'users.nickname', 'users.username', 'users.id as user_id:integer');
        $logsTable->join('users', 'users.id', 'logs.user');
        $logsTable->orderBy('id desc');
        if (is_numeric($limit)) {
            $logsTable->limit($limit, $offset);
        }

        if (!empty($filters) && count($filters) > 1) {
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = $GLOBALS['app']->loadDate();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['from_date'], '>=');
            }
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = $GLOBALS['app']->loadDate();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['to_date'], '<=');
            }
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('logs.gadget', $filters['gadget']);
            }
            if (isset($filters['user']) && !empty($filters['user'])) {
                $logsTable->and()->where('user', $filters['user']);
            }
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $logsTable->and()->where('title', '%' . $filters['term'] . '%', 'like');
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

        if (!empty($filters) && count($filters) > 1) {
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = $GLOBALS['app']->loadDate();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['from_date'], '>=');
            }
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = $GLOBALS['app']->loadDate();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['to_date'], '<=');
            }
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $logsTable->and()->where('logs.gadget', $filters['gadget']);
            }
            if (isset($filters['user']) && !empty($filters['user'])) {
                $logsTable->and()->where('user', $filters['user']);
            }
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $logsTable->and()->where('priority', $filters['priority']);
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $logsTable->and()->where('title', '%' . $filters['term'] . '%', 'like');
            }
        }

        return $logsTable->fetchOne();
    }

    /**
     * Get info of a Log
     *
     * @access  public
     * @param   int     $id      Log ID
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function GetLogInfo($id)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select('logs.id:integer', 'ip', 'agent', 'gadget', 'action', 'priority:integer',
                           'logs.status:integer','request_type:integer', 'insert_time', 'users.nickname',
                           'logs.script:boolean', 'logs.title', 'users.username', 'users.id as user_id:integer');
        $logsTable->join('users', 'users.id', 'logs.user');
        return $logsTable->where('logs.id', (int) $id)->fetchRow();
    }

    /**
     * Delete Logs
     *
     * @access  public
     * @param   array   $logsID      Logs ID
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function DeleteLogs($logsID)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        return $logsTable->delete()->where('id', $logsID, 'in')->exec();
    }
}
