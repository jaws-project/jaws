<?php
/**
 * Visit Counter Gadget
 *
 * @category   GadgetModel
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Model extends Jaws_Gadget_Model
{
    /**
     * Returns the hostname of an IP address using a reverse lookup
     *
     * WARNING: This may cause delays if DNS isn't setup properly, don't use it anywhere
     *          performance is a major issue.
     *
     * @access  public
     * @param   string  IP of the visitor
     * @return  string  The hostname of the remote machine, or Jaws_Error if no reverse lookup could be done.
     */
    function GetHostname($ip)
    {
        $hostname = gethostbyaddr($ip);
        if ($hostname == $ip) {
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_CANT_REVERSE_HOSTNAME', $ip), _t('VISITCOUNTER_NAME'));
        }

        return $hostname;
    }

    /**
     * Adds a new visitor
     *
     * @access  public
     * @param   string  $ip     IP of the visitor
     * @param   bool    $inc    Whether increments number of visits or not
     * @return  mixed   True if query was successful, otherwise returns Jaws_Error
     */
    function AddVisitor($ip, $inc = true)
    {
        $params = array();
        $params['date'] = $GLOBALS['app']->UserTime2UTC($GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00'));
        $params['now'] = time();
        $params['ip'] = $ip;

        $sql = '
            SELECT [ip], [visit_time]
            FROM [[ipvisitor]]
            WHERE [ip] = {ip} AND [visit_time] >= {date}';

        $visited = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($visited)) {
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_CANT_ADD_VISIT', $ip), _t('VISITCOUNTER_NAME'));
        }

        if (!empty($visited)) {
            $params['visits']   = $inc ? 1 : 0;
            $params['old_date'] = $visited['visit_time'];
            $sql = '
                UPDATE [[ipvisitor]] SET
                    [visit_time] = {now},
                    [visits]     = [visits] + {visits}
                WHERE
                    [ip] = {ip} AND [visit_time] = {old_date}';
        } else {
            $params['visits'] = 1;
            $sql = '
                INSERT INTO [[ipvisitor]]
                    ([ip], [visit_time], [visits])
                VALUES
                    ({ip}, {now}, {visits})';
        }

        $result = $GLOBALS['db']->query($sql, $params, JAWS_ERROR_NOTICE);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Gets the period of the counter cookie
     *
     * @access  public
     * @return  int  Number of days
     */
    function GetCookiePeriod()
    {
        $rs = $this->gadget->registry->get('period');
        if (Jaws_Error::IsError($rs)) {
            $rs = 5;
        }

        return $rs;
    }

    /**
     * Gets the timeout of online visitors in seconds
     *
     * @access  public
     * @return  int  Timeout in seconds
    */
    function GetOnlineVisitorsTimeout()
    {
        $rs = $this->gadget->registry->get('timeout');
        if (!$rs || Jaws_Error::IsError($rs)) {
            $rs = 600;
        }

        return $rs;
    }

    /**
     * Gets the initial date for visit counter
     *
     * @access  public
     * @return  string  Date of the start date
     */
    function GetStartDate()
    {
        $rs = $this->gadget->registry->get('start');
        if (!$rs || Jaws_Error::IsError($rs)) {
            $rs = date('Y-m-d H:i:s');
        }

        return $GLOBALS['app']->UserTime2UTC($rs);
    }

    /**
     * Gets number of online visitors
     *
     * @access  public
     * @return  int Number of online visitors by IP or Jaws_Error on failure
    */
    function GetOnlineVisitors()
    {
        $params = array();
        $params['date'] = time() - $this->GetOnlineVisitorsTimeout();
        $sql = '
            SELECT COUNT(*)
            FROM [[ipvisitor]]
            WHERE [visit_time] >= {date}';

        $onlinevisitors = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($onlinevisitors)) {
            return '-';
        }

        return $onlinevisitors;
    }

    /**
     * Gets number of today visitors
     *
     * @access  public
     * @param   string  $type   Type of calculation
     * @return  mixed   Number of today visitors or Jaws_Error on failure
    */
    function GetTodayVisitors($type = null)
    {
        $params = array();
        $params['date'] = $GLOBALS['app']->UserTime2UTC($GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00'));
        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type == 'unique') {
            $sql = '
                SELECT COUNT([ip])
                FROM (SELECT DISTINCT [ip] FROM [[ipvisitor]] WHERE [visit_time] >= {date}) AS visitors';
        } else {
            $sql = '
                SELECT SUM([visits])
                FROM [[ipvisitor]]
                WHERE [visit_time] >= {date}';
        }

        $visits = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($visits)) {
            return '-';
        }

        return (int)$visits;
    }

    /**
     * Gets number of yesterday visitors
     *
     * @access  public
     * @param   string  $type   Type of calculation
     * @return  mixed   Number of yesterday visitors or Jaws_Error on failure
    */
    function GetYesterdayVisitors($type = null)
    {
        $params = array();
        $params['begin'] = $GLOBALS['app']->UserTime2UTC(
            $GLOBALS['app']->UTC2UserTime(time() - 24 * 3600, 'Y-m-d 00:00:00')
        );
        $params['end'] = $GLOBALS['app']->UserTime2UTC(
            $GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00')
        );
        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type == 'unique') {
            $sql = '
                SELECT COUNT([ip])
                FROM (
                    SELECT DISTINCT [ip] 
                    FROM [[ipvisitor]] 
                    WHERE [visit_time] >= {begin} AND [visit_time] < {end}
                ) AS visitors';
        } else {
            $sql = '
                SELECT SUM([visits])
                FROM [[ipvisitor]]
                WHERE [visit_time] >= {begin} AND [visit_time] < {end}';
        }

        $visits = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($visits)) {
            return '-';
        }

        return (int)$visits;
    }

    /**
     * Gets number of total visitors since start date
     *
     * @access  public
     * @param   string  $type   Type of calculation
     * @return  int  Number of total visitors or Jaws_Error on failure
    */
    function GetTotalVisitors($type = null)
    {
        $params = array();
        $params['date'] = $this->GetStartDate();

        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type === 'unique') {
            $total = $this->gadget->registry->get('unique_visits');
            $sql = '
                SELECT COUNT([ip])
                FROM (SELECT DISTINCT [ip] FROM [[ipvisitor]] WHERE [visit_time] >= {date}) AS visitors';
        } else {
            $total = $this->gadget->registry->get('impression_visits');
            $sql = '
                SELECT SUM([visits])
                FROM [[ipvisitor]]
                WHERE [visit_time] >= {date}';
        }

        $visits = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($visits)) {
            return '-';
        }

        return $visits + $total;
    }

    /**
     * Gets type of visits to be displayed
     *
     * @access  public
     * @return  string  Type of visits being displayed
     */
    function GetVisitType()
    {
        $type = $this->gadget->registry->get('type');
        if (!$type || Jaws_Error::IsError($type)) {
            return 'unique';
        }

        return $type;
    }

}