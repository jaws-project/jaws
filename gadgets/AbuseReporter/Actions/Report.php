<?php
/**
 * AbuseReporter Gadget
 *
 * @category    Gadget
 * @package     AbuseReporter
 */
class AbuseReporter_Actions_Report extends Jaws_Gadget_Action
{
    /**
     * Report UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReportUI()
    {
        $tpl = $this->gadget->template->load('Report.html');
        $tpl->SetBlock('Report');

        $post = $this->gadget->request->fetch(array('report_gadget', 'report_action', 'report_reference'), 'post');
        $tpl->SetVariable('gadget', $post['report_gadget']);
        $tpl->SetVariable('action', $post['report_action']);
        $tpl->SetVariable('reference', $post['report_reference']);

        $tpl->SetVariable('lbl_comment', $this::t('COMMENT'));
        $tpl->SetVariable('lbl_type', $this::t('TYPE'));
        $tpl->SetVariable('lbl_priority', $this::t('PRIORITY'));

        // types
        $types = array(
            0 => $this::t('TYPE_ABUSE_0'),
            1 => $this::t('TYPE_ABUSE_1'),
            2 => $this::t('TYPE_ABUSE_2'),
            3 => $this::t('TYPE_ABUSE_3'),
            4 => $this::t('TYPE_ABUSE_4'),
            5 => $this::t('TYPE_ABUSE_5'),
        );
        foreach ($types as $type => $title) {
            $tpl->SetBlock('Report/type');
            $tpl->SetVariable('value', $type);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Report/type');
        }

        // priority
        $priorities = array(
            0 => $this::t('PRIORITY_0'),
            1 => $this::t('PRIORITY_1'),
            2 => $this::t('PRIORITY_2'),
            3 => $this::t('PRIORITY_3'),
            4 => $this::t('PRIORITY_4'),
        );
        foreach ($priorities as $priority => $title) {
            $tpl->SetBlock('Report/priority');
            $tpl->SetVariable('value', $priority);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Report/priority');
        }

        $tpl->ParseBlock('Report');
        return $tpl->Get();
    }

    /**
     * Save new report
     *
     * @access  public
     * @return  void
     */
    function SaveReport()
    {
        $post = $this->gadget->request->fetch(
            array('report_gadget', 'report_action', 'report_reference', 'url', 'comment', 'type', 'priority'),
            'post'
        );
        $currentUser = $this->app->session->user->id;
        $result = $this->gadget->model->load('Reports')->SaveReport(
            $currentUser,
            $post['report_gadget'],
            $post['report_action'],
            $post['report_reference'],
            $post['url'],
            $post['comment'],
            $post['type'],
            $post['priority']
        );

        $response = array(
            'gadget' => $post['report_gadget'],
            'action' => $post['report_action'],
            'reference' => $post['report_reference'],
        );

        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response(
                $result->GetMessage(),
                RESPONSE_ERROR,
                $response
            );
        } else {
            return $this->gadget->session->response(
                $this::t('REPORT_SAVED'),
                RESPONSE_NOTICE,
                $response
            );
        }
    }

}