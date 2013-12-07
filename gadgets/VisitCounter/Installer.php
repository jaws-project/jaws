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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('timeout', '600'),
        array('type', 'impressions'),
        array('period', '0'),
        array('start', ''),
        array('mode', 'text'),
        array('custom_text', '<strong>Total Visitors:</strong> <font color="red">{total}</font>'),
        array('unique_visits', '0'),
        array('visit_counters', 'online,today,yesterday,total'),
        array('impression_visits', '0'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ResetCounter',
        'CleanEntries',
        'UpdateProperties'
    );

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
        $this->gadget->registry->update('start', date('Y-m-d H:i:s'));

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
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
            return new Jaws_Error($errMsg);
        }

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
        if (version_compare($old, '0.9.0', '<')) {
            $this->gadget->registry->update('visit_counters', 'online,today,yesterday,total');

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
                FROM (SELECT DISTINCT [ip] FROM [[ipvisitor]] WHERE [visit_date] < {date}) as visitors';
            $unique_visits = $GLOBALS['db']->queryOne($sql, $date);
            if (Jaws_Error::IsError($unique_visits)) {
                return false;
            }

            $sql = 'SELECT SUM([visits]) FROM [[ipvisitor]] WHERE [visit_date] < {date}';
            $impression_visits = $GLOBALS['db']->queryOne($sql, $date);
            if (Jaws_Error::IsError($impression_visits)) {
                return false;
            }
            $this->gadget->registry->insert('unique_visits', $unique_visits);
            $this->gadget->registry->insert('impression_visits', $impression_visits);

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

        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('VisitCounter', 'Display', 'Display', 'VisitCounter');
            $layoutModel->EditGadgetLayoutAction('VisitCounter', 'DisplayOnline', 'DisplayOnline', 'VisitCounter');
            $layoutModel->EditGadgetLayoutAction('VisitCounter', 'DisplayToday', 'DisplayToday', 'VisitCounter');
            $layoutModel->EditGadgetLayoutAction('VisitCounter', 'DisplayTotal', 'DisplayTotal', 'VisitCounter');
        }

        return true;
    }

}