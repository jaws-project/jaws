<?php
/**
 * Logs Gadget
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 */
class Logs_Model_Admin_Logs extends Jaws_Gadget_Model
{
    /**
     * Get a list of the Logs
     *
     * @access  public
     * @param   int     $limit      Count of logs to be returned
     * @param   int     $offset     offset of data array
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function GetLogs($limit = false, $offset = null)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select('*');
        $logsTable->orderBy('id desc');
        if (is_numeric($limit)) {
            $logsTable->limit($limit, $offset);
        }
        return $logsTable->fetchAll();
    }

    /**
     * Gets logs count
     *
     * @access  public
     * @return  mixed   Count of available logs and Jaws_Error on failure
     */
    function GetLogsCount()
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select('count([id]):integer');
        return $logsTable->fetchOne();
    }

    /**
     * Get info of a Log
     *
     * @access  public
     * @param   int     $logID      Log ID
     * @return  mixed   Array of Logs or Jaws_Error on failure
     */
    function GetLogInfo($logID)
    {
        $logsTable = Jaws_ORM::getInstance()->table('logs');
        $logsTable->select('*')->where('id', (int) $logID);
        return $logsTable->fetchRow();
    }
}
