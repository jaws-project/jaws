<?php
/**
 * VisitCounter Installer
 *
 * @category    GadgetModel
 * @package     VisitCounter
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry('visit_counters',  'online,today,yesterday,total');
        $this->gadget->AddRegistry('timeout', '600');
        $this->gadget->AddRegistry('type', 'impressions');
        $this->gadget->AddRegistry('period', '0');
        $this->gadget->AddRegistry('start', date('Y-m-d H:i:s'));
        $this->gadget->AddRegistry('mode', 'text');
        $this->gadget->AddRegistry('custom_text', '<strong>Total Visitors:</strong> <font color="red">{total}</font>');
        $this->gadget->AddRegistry('unique_visits', '0');
        $this->gadget->AddRegistry('impression_visits', '0');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('ipvisitor');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('VISITCOUNTER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $this->gadget->DelRegistry('visit_counters');
        $this->gadget->DelRegistry('timeout');
        $this->gadget->DelRegistry('type');
        $this->gadget->DelRegistry('period');
        $this->gadget->DelRegistry('start');
        $this->gadget->DelRegistry('mode');
        $this->gadget->DelRegistry('custom_text');
        $this->gadget->DelRegistry('unique_visits');
        $this->gadget->DelRegistry('impression_visits');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        // $result = $this->installSchema('schema.xml', '', "$old.xml");
        // if (Jaws_Error::IsError($result)) {
            // return $result;
        // }

        if (version_compare($old, '0.8.0', '<')) {
            // Registry keys.
            $this->gadget->AddRegistry('visit_counters', 'online,today,total');
            $this->gadget->AddRegistry('custom_text', 
                                              $this->gadget->GetRegistry('custom'));
            $this->gadget->DelRegistry('online');
            $this->gadget->DelRegistry('today');
            $this->gadget->DelRegistry('total');
            $this->gadget->DelRegistry('custom');
        }

        if (version_compare($old, '0.8.1', '<')) {
            // fix using Y-m-d G:i:s instead of Y-m-d H:i:s in version 0.6.x
            $startDate = $this->gadget->GetRegistry('start');
            if (strlen($startDate) == 18) {
                $startDate = substr_replace($startDate, '0', 11, 0);
                $this->gadget->SetRegistry('start', $startDate);
            }
        }

        if (version_compare($old, '0.9.0', '<')) {
            $this->gadget->SetRegistry('visit_counters', 'online,today,yesterday,total');

            $result = $this->installSchema('0.8.3.xml', '', '0.8.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $date = $GLOBALS['app']->UserTime2UTC(
                $GLOBALS['app']->UTC2UserTime(time() - 24 * 3600, 'Y-m-d 00:00:00'),
                'Y-m-d H:i:s'
            );
            $date = array('date' => $date);
            $sql = '
                SELECT COUNT([ip])
                FROM (SELECT DISTINCT [ip] FROM [[ipvisitor]] WHERE [visit_date] < {date}) AS visitors';
            $unique_visits = $GLOBALS['db']->queryOne($sql, $date);
            if (Jaws_Error::IsError($unique_visits)) {
                return false;
            }

            $sql = 'SELECT SUM([visits]) FROM [[ipvisitor]] WHERE [visit_date] < {date}';
            $impression_visits = $GLOBALS['db']->queryOne($sql, $date);
            if (Jaws_Error::IsError($impression_visits)) {
                return false;
            }
            $this->gadget->AddRegistry('unique_visits', $unique_visits);
            $this->gadget->AddRegistry('impression_visits', $impression_visits);

            $sql = 'DELETE FROM [[ipvisitor]] WHERE [visit_date] < {date}';
            $res = $GLOBALS['db']->query($sql, $date);
            if (Jaws_Error::IsError($res)) {
                return false;
            }

            $sql = 'SELECT [ip], [visit_date] FROM [[ipvisitor]]';
            $visits = $GLOBALS['db']->queryAll($sql, $date);
            if (Jaws_Error::IsError($visits)) {
                return false;
            }
            $sql = '
                UPDATE [[ipvisitor]]
                SET [visit_time] = {visit_time}
                WHERE [ip] = {ip} AND [visit_date] = {visit_date}';
            $params = array();
            foreach ($visits as $visit) {
                $params['ip'] = $visit['ip'];
                $params['visit_date'] = $visit['visit_date'];
                $params['visit_time'] = $GLOBALS['app']->UserTime2UTC($visit['visit_date']);
                $res = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($res)) {
                    return false;
                }
            }

            $result = $this->installSchema('schema.xml', '', '0.8.3.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}