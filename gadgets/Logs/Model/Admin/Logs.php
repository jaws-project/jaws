<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Model_Admin_Logs extends Jaws_Gadget_Model
{
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
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['from_date'], '>=');
            }
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['to_date'], '<=');
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
            if (isset($filters['result']) && !empty($filters['result'])) {
                $logsTable->and()->where('logs.result', $filters['result']);
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
            'logs.id:integer', 'auth', 'logs.domain', 'user', 'username', 'gadget', 'action',
            'priority:integer', 'apptype', 'backend:boolean', 'ip', 'agent', 'result:integer',
            'logs.status:integer', 'logs.time:integer'
        );
        return $logsTable->where('logs.id', (int)$id)->fetchRow();
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

    /**
     * Delete Logs
     *
     * @access  public
     * @param   array   $filters  log filters
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function DeleteLogsUseFilters($filters)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->delete();

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $this->app->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $logsTable->and()->where('logs.time', $filters['to_date'], '<=');
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
            // result
            if (isset($filters['result']) && !empty($filters['result'])) {
                $logsTable->and()->where('logs.result', $filters['result']);
            }
            // status
            if (isset($filters['status']) && !empty($filters['status'])) {
                $logsTable->and()->where('logs.status', $filters['status']);
            }
        }

        return $logsTable->exec();
    }

}