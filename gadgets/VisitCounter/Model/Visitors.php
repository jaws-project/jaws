<?php
/**
 * Visit Counter Gadget
 *
 * @category   GadgetModel
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Model_Visitors extends Jaws_Gadget_Model
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
            return new Jaws_Error(_t('VISITCOUNTER_ERROR_CANT_REVERSE_HOSTNAME', $ip));
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
        $date = $GLOBALS['app']->UserTime2UTC($GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00'));
        $table = Jaws_ORM::getInstance()->table('ipvisitor');
        $table->select('ip', 'visit_time:integer');
        $table->where('ip', $ip)->and()->where('visit_time', $date, '>=');
        $visited = $table->fetchRow();
        if (Jaws_Error::IsError($visited)) {
            return $visited;
        }

        $now = time();
        $table = Jaws_ORM::getInstance()->table('ipvisitor');
        if (!empty($visited)) {
            $table->update(array(
                'visit_time' => $now, 
                'visits' => $table->expr('visits + ?', (int)$inc)
            ));
            $table->where('ip', $ip)->and()->where('visit_time', $visited['visit_time']);
        } else {
            $table->insert(array(
                'ip' => $ip, 
                'visit_time' => $now, 
                'visits' => 1
            ));
        }

        $result = $table->exec(JAWS_ERROR_NOTICE);
        return $result;
    }

    /**
     * Gets the period of the counter cookie
     *
     * @access  public
     * @return  int  Number of days
     */
    function GetCookiePeriod()
    {
        $rs = $this->gadget->registry->fetch('period');
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
        $rs = $this->gadget->registry->fetch('timeout');
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
        $rs = $this->gadget->registry->fetch('start');
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
        $date = time() - $this->GetOnlineVisitorsTimeout();
        $table = Jaws_ORM::getInstance()->table('ipvisitor');
        $table->select('COUNT(id):integer');
        $table->where('visit_time', $date, '>=');
        $count = $table->fetchOne();
        if (Jaws_Error::IsError($count)) {
            return '-';
        }

        return $count;
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
        $date = $GLOBALS['app']->UserTime2UTC($GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00'));
        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type === 'unique') {
            $innerTable = Jaws_ORM::getInstance()->table('ipvisitor');
            $innerTable->distinct();
            $innerTable->select('id')->where('visit_time', $date, '>=');
            $table = Jaws_ORM::getInstance()->table($innerTable, 'visitors');
            $table->select('COUNT(id)');
        } else {
            $table = Jaws_ORM::getInstance()->table('ipvisitor');
            $table->select('SUM(visits)')->where('visit_time', $date, '>=');
        }

        $visits = $table->fetchOne();
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
        $begin = $GLOBALS['app']->UserTime2UTC(
            $GLOBALS['app']->UTC2UserTime(time() - 24 * 3600, 'Y-m-d 00:00:00')
        );
        $end = $GLOBALS['app']->UserTime2UTC(
            $GLOBALS['app']->UTC2UserTime(time(), 'Y-m-d 00:00:00')
        );
        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type === 'unique') {
            $innerTable = Jaws_ORM::getInstance()->table('ipvisitor');
            $innerTable->distinct();
            $innerTable->select('id')
                ->where('visit_time', $begin, '>=')->and()
                ->where('visit_time', $end, '<=');
            $table = Jaws_ORM::getInstance()->table($innerTable, 'visitors');
            $table->select('COUNT([id])');
        } else {
            $table = Jaws_ORM::getInstance()->table('ipvisitor');
            $table->select('SUM(visits)')
                ->where('visit_time', $begin, '>=')->and()
                ->where('visit_time', $end, '<=');
        }

        $visits = $table->fetchOne();
        if (Jaws_Error::IsError($visits)) {
            return '-';
        }

        return (int)$visits;
    }

    /**
     * Gets number of total visitors from start date
     *
     * @access  public
     * @param   string  $type   Type of calculation
     * @return  int     Number of total visitors or Jaws_Error on failure
    */
    function GetTotalVisitors($type = null)
    {
        $date = $this->GetStartDate();
        if (is_null($type)) {
            $type = $this->GetVisitType();
        }

        if ($type === 'unique') {
            $innerTable = Jaws_ORM::getInstance()->table('ipvisitor');
            $innerTable->distinct();
            $innerTable->select('id')->where('visit_time', $date, '>=');
            $table = Jaws_ORM::getInstance()->table($innerTable, 'visitors');
            $table->select('COUNT(id)');
            $total = $this->gadget->registry->fetch('unique_visits');
        } else {
            $table = Jaws_ORM::getInstance()->table('ipvisitor');
            $table->select('SUM(visits)')->where('visit_time', $date, '>=');
            $total = $this->gadget->registry->fetch('impression_visits');
        }

        $visits = $table->fetchOne();
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
        $type = $this->gadget->registry->fetch('type');
        if (!$type || Jaws_Error::IsError($type)) {
            return 'unique';
        }

        return $type;
    }

}