<?php
/**
 * UrlMapper Core Gadget Admin
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Actions_Admin_ErrorMaps extends UrlMapper_Actions_Admin_Default
{
    /**
     * Builds error maps datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function ErrorMapsDatagrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->setID('errormaps_datagrid');
        $grid->SetStyle('margin-top: 0px; width: 100%;');
        $grid->useMultipleSelection();
        $grid->pageBy(15);

        $column1 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_URL'), null, false);
        $column1->SetStyle('width:200px;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_CODE'), null, false);
        $column2->SetStyle('width:100px;');
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_NEW_URL'), null, false);
        $column3->SetStyle('width:200px;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_NEW_CODE'), null, false);
        $column4->SetStyle('width:100px;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_HITS'), null, false);
        $column5->SetStyle('width:100px;');
        $grid->AddColumn($column5);
        return $grid->Get();
    }


    /**
     * Prepares list of error maps for datagrid
     *
     * @access  public
     * @param   array   $filters
     * @param   int     $limit
     * @param   int     $offset
     * @param   string  $order
     * @return  array   Grid data
     */
    function GetErrorMaps($filters, $limit, $offset, $order)
    {
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $errorMaps = $model->GetErrorMaps($filters, $limit, $offset, $order);
        if (Jaws_Error::IsError($errorMaps)) {
            return array();
        }

        $retData = array();
        foreach ($errorMaps as $errorMap) {
            $usrData = array();
            $usrData['__KEY__'] = $errorMap['id'];
            if ($this->gadget->GetPermission('ManageErrorMaps')) {
                $errorMap['url'] = Piwi::CreateWidget('Link',
                    $errorMap['url'],
                    "javascript:editErrorMap(this, '" . $errorMap['id'] . "');")->get();
            }
            $usrData['url']         = $errorMap['url'];
            $usrData['code']        = $errorMap['code'];
            $usrData['new_url']     = $errorMap['new_url'];
            $usrData['new_code']    = $errorMap['new_code'];
            $usrData['hits']        = $errorMap['hits'];

            $retData[] = $usrData;
        }

        return $retData;
    }


    /**
     * Builds error maps UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ErrorMaps()
    {
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('ErrorMaps.html');
        $tpl->SetBlock('ErrorMaps');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('ErrorMaps'));

        $tpl->SetVariable('datagrid',   $this->ErrorMapsDatagrid());

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'filter_from_date', '');
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $fromDate->AddEvent(ON_CHANGE, "javascript:searchErrorMaps();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', _t('LOGS_FROM_DATE'));

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'filter_to_date', '');
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $toDate->AddEvent(ON_CHANGE, "javascript:searchErrorMaps();");
        $tpl->SetVariable('filter_to_date', $toDate->Get());
        $tpl->SetVariable('lbl_filter_to_date', _t('LOGS_TO_DATE'));

        // Combo for code
        $codeCombo =& Piwi::CreateWidget('Combo', 'filter_code');
        $codeCombo->SetID('filter_code');
        $codeCombo->SetStyle('width: 150px;');
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_404'), 404);
        $codeCombo->AddEvent(ON_CHANGE, "javascript:searchErrorMaps();");
        $tpl->SetVariable('lbl_filter_code', _t('URLMAPPER_ERRORMAPS_CODE'));
        $tpl->SetVariable('filter_code', $codeCombo->Get());

        // New code filter
        $codeCombo =& Piwi::CreateWidget('Combo', 'filter_new_code');
        $codeCombo->SetID('filter_new_code');
        $codeCombo->SetStyle('width: 150px;');
        $codeCombo->AddOption(_t('GLOBAL_ALL'), 0);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_301'), 301);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_302'), 302);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_404'), 404);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_410'), 410);
        $codeCombo->SetDefault(0);
        $codeCombo->AddEvent(ON_CHANGE, "javascript:searchErrorMaps();");
        $tpl->SetVariable('lbl_filter_new_code', _t('URLMAPPER_ERRORMAPS_NEW_CODE'));
        $tpl->SetVariable('filter_new_code', $codeCombo->Get());

        // Order
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('GLOBAL_CREATETIME'). ' &darr;', 'id');
        $orderType->AddOption(_t('GLOBAL_CREATETIME'). ' &uarr;', 'id desc');
        $orderType->AddOption(_t('URLMAPPER_ERRORMAPS_HITS'). ' &darr;', 'hits');
        $orderType->AddOption(_t('URLMAPPER_ERRORMAPS_HITS'). ' &uarr;', 'hits desc');
        $orderType->AddEvent(ON_CHANGE, "javascript:searchErrorMaps();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', _t('URLMAPPER_ORDER_TYPE'));

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'errormaps_actions');
        $actions->SetID('errormaps_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('URLMAPPER_ERRORMAPS_DELETE_ALL'), 'deleteAll');
        $actions->AddOption(_t('URLMAPPER_ERRORMAPS_DELETE_FILTERED'), 'deleteFiltered');
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeErrorMapsAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:errorMapsDGAction();");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());

        // url
        $code =& Piwi::CreateWidget('Entry', 'url', '');
        $code->SetID('url');
        $code->SetStyle('direction: ltr; width: 250px;');
        $tpl->SetVariable('lbl_url', _t('URLMAPPER_ERRORMAPS_URL'));
        $tpl->SetVariable('url', $code->Get());

        // Combo for code
        $codeCombo =& Piwi::CreateWidget('Combo', 'code');
        $codeCombo->SetID('code');
        $codeCombo->SetStyle('width: 200px;');
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_404'), 404);
        $tpl->SetVariable('lbl_code', _t('URLMAPPER_ERRORMAPS_CODE'));
        $tpl->SetVariable('code', $codeCombo->Get());

        // new url
        $newUrl =& Piwi::CreateWidget('Entry', 'new_url', '');
        $newUrl->SetID('new_url');
        $newUrl->SetStyle('direction: ltr; width: 250px;');
        $tpl->SetVariable('lbl_new_url', _t('URLMAPPER_ERRORMAPS_NEW_URL'));
        $tpl->SetVariable('new_url', $newUrl->Get());

        // Combo for new code
        $codeCombo =& Piwi::CreateWidget('Combo', 'new_code');
        $codeCombo->SetID('new_code');
        $codeCombo->SetStyle('width: 200px;');
        $codeCombo->AddOption('', 0);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_301'), 301);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_302'), 302);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_404'), 404);
        $codeCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_410'), 410);
        $codeCombo->SetDefault(404);
        $codeCombo->AddEvent(ON_CHANGE, "javascript:changeCode();");
        $tpl->SetVariable('lbl_new_code', _t('URLMAPPER_ERRORMAPS_NEW_CODE'));
        $tpl->SetVariable('new_code', $codeCombo->Get());

        // Insert time
        $insertTime =& Piwi::CreateWidget('Entry', 'insert_time', '');
        $insertTime->SetID('insert_time');
        $insertTime->SetEnabled(false);
        $insertTime->SetStyle('width: 250px;');
        $tpl->SetVariable('lbl_insert_time', _t('GLOBAL_CREATETIME'));
        $tpl->SetVariable('insert_time', $insertTime->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->SetID('btn_cancel');
        $btnCancel->SetStyle('visibility: hidden;');
        $btnCancel->AddEvent(ON_CLICK, "javascript:stopErrorMapAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript:saveErrorMap();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $this->gadget->layout->setVariable('addErrorMap_title', _t('URLMAPPER_ERRORMAPS_ADD_TITLE'));
        $this->gadget->layout->setVariable('editErrorMap_title', _t('URLMAPPER_ERRORMAPS_EDIT_TITLE'));
        $this->gadget->layout->setVariable('confirmErrorMapDelete', _t('URLMAPPER_ERRORMAPS_CONFIRM_DELETE'));
        $this->gadget->layout->setVariable('incompleteFieldsMsg', _t('URLMAPPER_ERRORMAPS_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('ErrorMaps');
        return $tpl->Get();
    }

}