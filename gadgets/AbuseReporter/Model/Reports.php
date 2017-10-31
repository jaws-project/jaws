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
     * Save a Report
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference id
     * @param   string  $url        Reported page URL
     * @param   string  $comment    User's comment
     * @param   int     $type       Type of report
     * @param   int     $priority   Priority of report
     * @return  mixed   Report identity or Jaws_Error on failure
     */
    function SaveReport($user, $gadget, $action, $reference, $url, $comment, $type, $priority)
    {
        $reportsTable = Jaws_ORM::getInstance()->table('abuse_reports');
        $reportsTable->insert(
            array(
                'user'          => (int)$user,
                'gadget'        => $gadget,
                'action'        => $action,
                'reference'     => $reference,
                'url'           => $url,
                'comment'       => $comment,
                'type'          => (int)$type,
                'priority'      => (int)$priority,
                'status'        => 0,
                'insert_time'   => time(),
            )
        );

        $result = $reportsTable->exec(JAWS_ERROR_INFO);
        if (Jaws_Error::isError($result)) {
            if ($result->GetCode() == -3) {
                $result->setMessage(_t('ABUSEREPORTER_ERROR_REPORT_ALREADY_EXIST'));
            } else {
                $result->setLevel(JAWS_ERROR_ERROR);
            }
        }

        return $result;
    }
}