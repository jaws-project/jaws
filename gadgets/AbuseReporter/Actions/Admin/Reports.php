<?php
/**
 * AbuseReporter Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    AbuseReporter
 */
class AbuseReporter_Actions_Admin_Reports extends AbuseReporter_Actions_Admin_Default
{
    /**
     * Builds Reports UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Reports()
    {
        $this->gadget->CheckPermission('ManageReports');
        $GLOBALS['app']->Layout->addLink('libraries/bootstrap.fuelux/css/bootstrap.fuelux.min.css');
        $this->AjaxMe('script.js');
        $this->gadget->layout->setVariable('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));
        $this->gadget->layout->setVariable('lbl_gadget', _t('ABUSEREPORTER_GADGET'));
        $this->gadget->layout->setVariable('lbl_action', _t('ABUSEREPORTER_ACTION'));
        $this->gadget->layout->setVariable('lbl_type', _t('ABUSEREPORTER_TYPE'));
        $this->gadget->layout->setVariable('lbl_priority', _t('ABUSEREPORTER_PRIORITY'));
        $this->gadget->layout->setVariable('lbl_status', _t('GLOBAL_STATUS'));
        $this->gadget->layout->setVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->layout->setVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->layout->setVariable('lbl_editReport', _t('ABUSEREPORTER_REPORT_EDIT'));

        $tpl = $this->gadget->template->loadAdmin('Reports.html');
        $tpl->SetBlock('Reports');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_gadget', _t('ABUSEREPORTER_GADGET'));
        $tpl->SetVariable('lbl_action', _t('ABUSEREPORTER_ACTION'));
        $tpl->SetVariable('lbl_reference', _t('ABUSEREPORTER_REFERENCE'));
        $tpl->SetVariable('lbl_comment', _t('ABUSEREPORTER_COMMENT'));
        $tpl->SetVariable('lbl_type', _t('ABUSEREPORTER_TYPE'));
        $tpl->SetVariable('lbl_priority', _t('ABUSEREPORTER_PRIORITY'));
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('lbl_response', _t('ABUSEREPORTER_RESPONSE'));
        $tpl->SetVariable('lbl_insert_time', _t('ABUSEREPORTER_INSERT_TIME'));

        // gadgets filter
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();
        if (!Jaws_Error::IsError($gadgetList) && count($gadgetList) > 0) {
            array_unshift($gadgetList, array('name' => 0, 'title' => _t('GLOBAL_ALL')));
            foreach ($gadgetList as $gadget) {
                $tpl->SetBlock('Reports/filter_gadget');
                $tpl->SetVariable('value', $gadget['name']);
                $tpl->SetVariable('title', $gadget['title']);
                $tpl->ParseBlock('Reports/filter_gadget');
            }
            array_shift($gadgetList);
            foreach ($gadgetList as $gadget) {
                $tpl->SetBlock('Reports/gadget');
                $tpl->SetVariable('value', $gadget['name']);
                $tpl->SetVariable('title', $gadget['title']);
                $tpl->ParseBlock('Reports/gadget');
            }
        }

        // priority filter
        $priorities = array(
            0 => _t('GLOBAL_ALL'),
            AbuseReporter_Info::PRIORITY_VERY_HIGH => _t('ABUSEREPORTER_PRIORITY_VERY_HIGH'),
            AbuseReporter_Info::PRIORITY_HIGH => _t('ABUSEREPORTER_PRIORITY_HIGH'),
            AbuseReporter_Info::PRIORITY_NORMAL => _t('ABUSEREPORTER_PRIORITY_NORMAL'),
            AbuseReporter_Info::PRIORITY_LOW => _t('ABUSEREPORTER_PRIORITY_LOW'),
            AbuseReporter_Info::PRIORITY_VERY_LOW => _t('ABUSEREPORTER_PRIORITY_VERY_LOW'),
        );
        foreach ($priorities as $priority => $title) {
            $tpl->SetBlock('Reports/filter_priority');
            $tpl->SetVariable('value', $priority);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Reports/filter_priority');
        }
        array_shift($priorities);
        foreach ($priorities as $priority => $title) {
            $tpl->SetBlock('Reports/priority');
            $tpl->SetVariable('value', $priority);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Reports/priority');
        }

        // status filter
        $statuses = array(
            0 => _t('GLOBAL_ALL'),
            AbuseReporter_Info::STATUS_NOT_CHECKED => _t('ABUSEREPORTER_STATUS_NOT_CHECKED'),
            AbuseReporter_Info::PRIORITY_HIGH => _t('ABUSEREPORTER_STATUS_CHECKED'),
        );
        foreach ($statuses as $status => $title) {
            $tpl->SetBlock('Reports/filter_status');
            $tpl->SetVariable('value', $status);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Reports/filter_status');
        }
        array_shift($statuses);
        foreach ($statuses as $status => $title) {
            $tpl->SetBlock('Reports/status');
            $tpl->SetVariable('value', $status);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Reports/status');
        }

        // types
        $types = array(
            AbuseReporter_Info::TYPE_ABUSE => _t('ABUSEREPORTER_TYPE_ABUSE'),
            AbuseReporter_Info::TYPE_FRAUD => _t('ABUSEREPORTER_TYPE_FRAUD'),
            AbuseReporter_Info::TYPE_VIRUS => _t('ABUSEREPORTER_TYPE_VIRUS'),
            AbuseReporter_Info::TYPE_SPAM => _t('ABUSEREPORTER_TYPE_SPAM'),
            AbuseReporter_Info::TYPE_OTHER => _t('ABUSEREPORTER_TYPE_OTHER'),
        );
        foreach ($types as $type => $title) {
            $tpl->SetBlock('Reports/type');
            $tpl->SetVariable('value', $type);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Reports/type');
        }

        $tpl->ParseBlock('Reports');
        return $tpl->Get();
    }

    /**
     * Get reports list
     *
     * @access  public
     * @return  JSON
     */
    function GetReports()
    {
        $this->gadget->CheckPermission('ManageReports');
        $post = jaws()->request->fetch(
            array('filters:array', 'limit', 'offset', 'searchLogic', 'search:array', 'sort:array'),
            'post'
        );

        $model = $this->gadget->model->loadAdmin('Reports');
        $reports = $model->GetReports($post['filters'], $post['limit'], $post['offset']);

        foreach ($reports as $key => $report) {
            $report['recid'] = $report['id'];
            $reports[$key] = $report;
        }
        $reportsCount = $model->GetReportsCount($post['filters']);

        return array(
            'status' => 'success',
            'total' => $reportsCount,
            'records' => $reports
        );
    }

    /**
     * Get a report info
     *
     * @access  public
     * @return  JSON
     */
    function GetReport()
    {
        $this->gadget->CheckPermission('ManageReports');
        $id = (int)jaws()->request->fetch('id', 'post');
        $reportInfo = $this->gadget->model->loadAdmin('Reports')->GetReport($id);
        if (Jaws_Error::IsError($reportInfo)) {
            return $reportInfo;;
        }
        if (!empty($reportInfo)) {
            $objDate = Jaws_Date::getInstance();
            $reportInfo['insert_time'] = $objDate->Format($reportInfo['insert_time']);
        }
        return $reportInfo;
    }

    /**
     * Update a report
     *
     * @access  public
     * @return  void
     */
    function UpdateReport()
    {
        $this->gadget->CheckPermission('ManageReports');

        $post = jaws()->request->fetch(array('id', 'data:array'), 'post');
        $result = $this->gadget->model->loadAdmin('Reports')->UpdateReport($post['id'], $post['data']);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('ABUSEREPORTER_REPORT_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete a report
     *
     * @access  public
     * @return  void
     */
    function DeleteReport()
    {
        $this->gadget->CheckPermission('ManageReports');

        $id = (int)jaws()->request->fetch('id', 'post');
        $result =  $this->gadget->model->loadAdmin('Reports')->DeleteReport($id);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('ABUSEREPORTER_REPORT_DELETED'), RESPONSE_NOTICE);
        }
    }

}