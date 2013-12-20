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
        $logsTable->select(
            'logs.id:integer', 'gadget', 'action',
            'users.nickname', 'users.username', 'users.id as user_id:integer',
            'apptype', 'backend:boolean', 'logs.ip', 'logs.status', 'logs.insert_time'
        );
        $logsTable->join('users', 'users.id', 'logs.user', 'left');
        $logsTable->orderBy('id desc');
        $logsTable->limit($limit, $offset);

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
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.insert_time', $filters['from_date'], '>=');
            }
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
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
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('logs.status', $filters['status']);
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
    function GetLog($id)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select(
            'logs.id:integer', 'gadget', 'action', 'priority:integer',
            'users.nickname', 'users.username', 'users.id as user_id:integer',
            'apptype', 'backend:boolean', 'ip', 'agent', 'logs.status:integer',
            'insert_time'
        );
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