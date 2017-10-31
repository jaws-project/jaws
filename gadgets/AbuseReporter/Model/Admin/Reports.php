<?php
/**
 * AbuseReporter Gadget
 *
 * @category    GadgetModel
 * @package     AbuseReporter
 */
class AbuseReporter_Model_Admin_Reports extends Jaws_Gadget_Model
{
    /**
     * Gets reports count
     *
     * @access  public
     * @param   array   $filters   report filters
     * @return  mixed   Count of available reports and Jaws_Error on failure
     */
    function GetReportsCount($filters = null)
    {
        $reportsTable = Jaws_ORM::getInstance()->table('abuse_reports');
        $reportsTable->select('count(id):integer');

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $reportsTable->and()->where('abuse_reports.insert_time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $reportsTable->and()->where('abuse_reports.insert_time', $filters['to_date'], '<=');
            }
            // gadget
            if ($filters['gadget'] != -1) {
                $reportsTable->and()->where('abuse_reports.gadget', $filters['gadget']);
            }
            // action
            if ($filters['action'] != -1) {
                $reportsTable->and()->where('abuse_reports.action', $filters['action'], 'like');
            }
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $reportsTable->and()->where('user', (int)$filters['user']);
            }
            // priority
            if ($filters['priority'] != -1) {
                $reportsTable->and()->where('abuse_reports.priority', $filters['priority']);
            }
            // status
            if ($filters['status'] != -1) {
                $reportsTable->and()->where('abuse_reports.status', $filters['status']);
            }
        }

        return $reportsTable->fetchOne();
    }


    /**
     * Get a list of the Reports
     *
     * @access  public
     * @param   array    $filters   report filters
     * @param   bool|int $limit     Count of reports to be returned
     * @param   int      $offset    Offset of data array
     * @param   string   $orderBy   Order by?
     * @return  mixed   Array of Reports or Jaws_Error on failure
     */
    function GetReports($filters = null, $limit = false, $offset = null, $orderBy = 'id desc')
    {
        $reportsTable = Jaws_ORM::getInstance()->table('abuse_reports');
        $reportsTable->select(
            'abuse_reports.id:integer', 'abuse_reports.url', 'gadget', 'action', 'reference:integer', 'comment',
            'abuse_reports.type:integer', 'abuse_reports.priority:integer', 'abuse_reports.status:integer',
            'response', 'abuse_reports.insert_time:integer', 'abuse_reports.update_time:integer'
        );
        $reportsTable->join('users', 'users.id', 'abuse_reports.user', 'left');
        $reportsTable->limit((int)$limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $reportsTable->and()->where('abuse_reports.insert_time', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $reportsTable->and()->where('abuse_reports.insert_time', $filters['to_date'], '<=');
            }
            // gadget
            if ($filters['gadget'] != -1) {
                $reportsTable->and()->where('abuse_reports.gadget', $filters['gadget']);
            }
            // action
            if ($filters['action'] != -1) {
                $reportsTable->and()->where('abuse_reports.action', $filters['action'], 'like');
            }
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $reportsTable->and()->where('user', (int)$filters['user']);
            }
            // priority
            if ($filters['priority'] != -1) {
                $reportsTable->and()->where('abuse_reports.priority', $filters['priority']);
            }
            // status
            if ($filters['status'] != -1) {
                $reportsTable->and()->where('abuse_reports.status', $filters['status']);
            }
        }

        return $reportsTable->orderBy('abuse_reports.' . $orderBy)->fetchAll();
    }

    /**
     * Get info of a Report
     *
     * @access  public
     * @param   int     $id      Report ID
     * @return  mixed   Array of Reports or Jaws_Error on failure
     */
    function GetReport($id)
    {
        $reportsTable = Jaws_ORM::getInstance()->table('abuse_reports');
        $reportsTable->select(
            'abuse_reports.id:integer', 'abuse_reports.url', 'gadget', 'action', 'reference:integer', 'comment',
            'abuse_reports.type:integer', 'abuse_reports.priority:integer', 'abuse_reports.status:integer',
            'response', 'abuse_reports.insert_time:integer', 'abuse_reports.update_time:integer'
        );
        $reportsTable->join('users', 'users.id', 'abuse_reports.user');
        return $reportsTable->where('abuse_reports.id', (int) $id)->fetchRow();
    }

    /**
     * Updates a report
     *
     * @access  public
     * @param   int     $id        Report ID
     * @param   array   $data      The report data
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateReport($id, $data)
    {
        $data['update_time'] = time();
        return Jaws_ORM::getInstance()->table('abuse_reports')
            ->update($data)->where('id', $id)->exec();
    }

    /**
     * Delete a report
     *
     * @access  public
     * @param   int     $id      Report ID
     * @return  mixed   Array of Reports or Jaws_Error on failure
     */
    function DeleteReport($id)
    {
        return Jaws_ORM::getInstance()->table('abuse_reports')->delete()->where('id', $id)->exec();
    }
}