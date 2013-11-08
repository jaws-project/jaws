<?php
/**
 * Visit Counter Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Actions_Admin_VisitCounter extends Jaws_Gadget_Action
{
    /**
     * Builds the administration UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function VisitCounter()
    {
        $this->AjaxMe('script.js');

        $model = $this->gadget->model->load('Visitors');
        $num_online       = $model->GetOnlineVisitors();
        $uniqueToday      = $model->GetTodayVisitors('unique');
        $impressionsToday = $model->GetTodayVisitors('impressions');
        $uniqueYesterday  = $model->GetYesterdayVisitors('unique');
        $imprsnsYesterday = $model->GetYesterdayVisitors('impressions');
        $uniqueTotal      = $model->GetTotalVisitors('unique');
        $impressionsTotal = $model->GetTotalVisitors('impressions');
        $startDate        = $model->GetStartDate();

        $tpl = $this->gadget->loadAdminTemplate('VisitCounter.html');
        $tpl->SetBlock('visitcounter');

        $tpl->SetVariable('grid', $this->DataGrid());
        //Ok, the config..
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $config_form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'VisitCounter'));
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $fieldset_config = new Jaws_Widgets_FieldSet(_t('VISITCOUNTER_PROPERTIES'));
            $fieldset_config->SetDirection('vertical');

            $visit_counters = explode(',', $this->gadget->registry->fetch('visit_counters'));
            $check_counters =& Piwi::CreateWidget('CheckButtons', 'c_kind', 'vertical');
            $check_counters->SetTitle(_t('VISITCOUNTER_DISPLAY_COUNTER'));
            $check_counters->AddOption(
                _t('VISITCOUNTER_ONLINE_VISITORS'), 'online', null, in_array('online', $visit_counters)
            );
            $check_counters->AddOption(
                _t('VISITCOUNTER_TODAY_VISITORS'), 'today', null, in_array('today', $visit_counters)
            );
            $check_counters->AddOption(
                _t('VISITCOUNTER_YESTERDAY_VISITORS'), 'yesterday', null, in_array('yesterday', $visit_counters)
            );
            $check_counters->AddOption(
                _t('VISITCOUNTER_TOTAL_VISITORS'), 'total', null, in_array('total', $visit_counters)
            );
            $check_counters->AddOption(
                _t('VISITCOUNTER_CUSTOM_VISITORS'), 'custom', null, in_array('custom', $visit_counters)
            );
            $fieldset_config->Add($check_counters);

            $type =& Piwi::CreateWidget('Combo', 'type');
            $type->SetTitle(_t('VISITCOUNTER_TYPE'));
            $type->AddOption(_t('VISITCOUNTER_UNIQUE'), 'unique');
            $type->AddOption(_t('VISITCOUNTER_BY_IMPRESSIONS'), 'impressions');
            $type->SetDefault($model->GetVisitType());
            $fieldset_config->Add($type);

            $period =& Piwi::CreateWidget('Combo', 'period');
            $period->SetTitle(_t('VISITCOUNTER_COOKIE_PERIOD'));
            for ($i = 0; $i <= 15; $i +=1 ) {
                $period->AddOption($i, $i);
            }
            $period->SetDefault($model->GetCookiePeriod());
            $fieldset_config->Add($period);

            $mode =& Piwi::CreateWidget('Combo', 'mode');
            $mode->SetTitle(_t('VISITCOUNTER_MODE'));
            $mode_reg = $this->gadget->registry->fetch('mode');
            $mode->AddOption(_t('VISITCOUNTER_MODE_TEXT'), 'text');
            $mode->AddOption(_t('VISITCOUNTER_MODE_IMAGE'), 'image');
            $mode->SetDefault($mode_reg);
            $mode->SetId('custom');
            $fieldset_config->Add($mode);

            $custom_reg = stripslashes($this->gadget->registry->fetch('custom_text'));
            $customText =& Piwi::CreateWidget('Entry', 'custom_text');
            $customText->SetTitle(_t('VISITCOUNTER_CUSTOM_TEXT'));
            $customText->SetValue($custom_reg);
            $fieldset_config->Add($customText);

            $config_form->Add($fieldset_config);
            $submit_config =& Piwi::CreateWidget('Button', 'saveproperties',
                                                 _t('VISITCOUNTER_UPDATE_PROPS'), STOCK_SAVE);
            $submit_config->AddEvent(ON_CLICK, 'javascript: updateProperties(this.form);');
            $config_form->Add($submit_config);

            //$tpl->SetVariable('menubar', $this->menubar(''));
            $tpl->SetVariable('config_form', $config_form->Get());
        }

        //Stats..
        $tpl->SetVariable('visitor_stats', _t('VISITCOUNTER_VISITOR_STATS'));

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_STATS_FROM'));
        $date = $GLOBALS['app']->loadDate();
        $tpl->SetVariable('value', $date->Format($startDate, 'Y-m-d'));
        $tpl->SetVariable('item_id', 'stats_from');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_ONLINE_VISITORS'));
        $tpl->SetVariable('value', $num_online);
        $tpl->SetVariable('item_id', 'visitors');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_TODAY_UNIQUE_VISITORS'));
        $tpl->SetVariable('value', $uniqueToday);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_TODAY_PAGE_IMPRESSIONS'));
        $tpl->SetVariable('value', $impressionsToday);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_YESTERDAY_UNIQUE_VISITORS'));
        $tpl->SetVariable('value', $uniqueYesterday);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_YESTERDAY_PAGE_IMPRESSIONS'));
        $tpl->SetVariable('value', $imprsnsYesterday);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_TOTAL_UNIQUE_VISITORS'));
        $tpl->SetVariable('value', $uniqueTotal);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->SetBlock('visitcounter/item');
        $tpl->SetVariable('label', _t('VISITCOUNTER_TOTAL_PAGE_IMPRESSIONS'));
        $tpl->SetVariable('value', $impressionsTotal);
        $tpl->SetVariable('item_id', 'impressions');
        $tpl->ParseBlock('visitcounter/item');

        $tpl->ParseBlock('visitcounter');

        return $tpl->Get();
    }

    /**
     * Builds the menubar
     *
     * @access  private
     * @param   string  $selected   Selected menu item
     * @return  string  XHTML menubar
     */
    function MenuBar($selected)
    {
        $actions = array('Admin', 'ResetCounter', 'CleanEntries');

        if (!in_array($selected, $actions)) {
            $selected = 'Admin';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Admin', _t('VISITCOUNTER_ADMIN_ACTION'), '');

        if ($this->gadget->GetPermission('ResetCounter')) {
            $menubar->AddOption('ResetCounter', _t('VISITCOUNTER_RESET_COUNTER_ACTION'),
                                "javascript: if (confirm('"._t("VISITCOUNTER_RESET_COUNTER_CONFIRM")."')) ".
                                "resetCounter(); return false;");
        }

        if ($this->gadget->GetPermission('CleanEntries')) {
            $menubar->AddOption('CleanEntries', _t('VISITCOUNTER_CLEAN_COUNTER'),
                                "javascript: if (confirm('"._t("VISITCOUNTER_CLEAN_COUNTER_CONFIRM")."')) ".
                                "cleanEntries(); return false;");
        }
        $menubar->Activate($selected);

        return $menubar->Get();
    }

    /**
     * Builds the datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function DataGrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('ipvisitor', 'ip');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(15);
        $datagrid->SetID('visitcounter_datagrid');
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('VISITCOUNTER_IP')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('VISITCOUNTER_DATE')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('VISITCOUNTER_VISITS')));

        return $datagrid->Get();
    }

    /**
     * Gets list of visits
     *
     * @access  public
     * @param   int     $offset  Data offset
     * @return  array   List of visits
     */
    function GetVisits($offset = 0)
    {
        $model = $this->gadget->model->loadAdmin('Visitors');
        $visits = $model->GetVisitors($offset);
        if (Jaws_Error::IsError($visits)) {
            return array();
        }

        $newData = array();
        $date = $GLOBALS['app']->loadDate();
        foreach($visits as $visit) {
            $visitData = array();
            $visitData['ip']     = $visit['ip'];
            $visitData['date']   = $date->Format($visit['visit_time'], 'Y-m-d H:i:s');
            $visitData['visits'] = $visit['visits'];

            $newData[] = $visitData;
        }
        return $newData;

    }
}