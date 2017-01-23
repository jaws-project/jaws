<?php
/**
 * AbuseReporter Gadget
 *
 * @category    GadgetModel
 * @package     AbuseReporter
 */
class AbuseReporter_Model_Reports extends Jaws_Gadget_Model
{
    /**
     * Inserts a Report
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $priority   Priority of report
     * @param   array   $params     Action parameters
     * @param   int     $status     Status code
     * @return  mixed   Report identity or Jaws_Error on failure
     */
    function InsertReport($user, $gadget, $action, $priority = 0, $params = null, $status = 200)
    {
        // ip address
        $ip = 0;
        if (preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            $ip = ($ip < 0)? ($ip + 0xffffffff + 1) : $ip;
        }
        // agent
        $agent = substr(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']), 0, 252);

        $reportsTable = Jaws_ORM::getInstance()->table('abuse_reports');
        $reportsTable->insert(
            array(
                'user'     => (int)$user,
                'gadget'   => $gadget,
                'action'   => $action,
                'priority' => $priority,
                'params'   => $params,
                'apptype'  => JAWS_APPTYPE,
                'backend'  => JAWS_SCRIPT == 'admin',
                'ip'       => $ip,
                'agent'    => $agent,
                'status'   => (int)$status,
                'insert_time' => time(),
            )
        );

        return $reportsTable->exec();
    }
}