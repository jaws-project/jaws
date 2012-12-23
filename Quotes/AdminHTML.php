<?php
/**
 * Quotes Gadget Action
 *
 * @category   GadgetAdmin
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class QuotesAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access       public
     * @return       string  Template content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageQuotes')) {
            return $this->Quotes();
        } elseif ($this->gadget->GetPermission('ManageQuoteGroups')) {
            return $this->QuoteGroups();
        }

        $this->gadget->CheckPermission('Properties');
    }

    /**
     * Prepares the quotes menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Quotes', 'QuoteGroups');
        if (!in_array($action, $actions)) {
            $action = 'Quotes';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageQuotes')) {
            $menubar->AddOption('Quotes', _t('QUOTES_NAME'),
                                BASE_SCRIPT . '?gadget=Quotes&amp;action=Admin', 'gadgets/Quotes/images/quotes_mini.png');
        }
        if ($this->gadget->GetPermission('ManageQuoteGroups')) {
            $menubar->AddOption('QuoteGroups', _t('QUOTES_GROUPS'),
                                BASE_SCRIPT . '?gadget=Quotes&amp;action=QuoteGroups', 'gadgets/Quotes/images/groups_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Show quotes administration
     *
     * @access  public
     * @return  string HTML content of administration
     */
    function Quotes()
    {
        $GLOBALS['app']->Layout->AddScriptLink('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/piwi/piwidata/js/jscalendar/lang/calendar-en.js');
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddHeadLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css', 'stylesheet', 'text/css');

        $tpl = new Jaws_Template('gadgets/Quotes/templates/');
        $tpl->Load('AdminQuotes.html');
        $tpl->SetBlock('quotes');
        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Quotes'));
        $tpl->SetBlock('quotes/quotes_section');

        $model = $GLOBALS['app']->LoadGadget('Quotes', 'Model');
        $groups = $model->GetGroups();

        //Group Filter
        $combo =& Piwi::CreateWidget('Combo', 'group_filter');
        $combo->setStyle('width:250px;');
        $combo->AddEvent(ON_CHANGE, 'javascript: fillQuotesCombo();');
        $combo->AddOption('', -1);
        foreach($groups as $group) {
            $combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('group_filter', $combo->Get());
        $tpl->SetVariable('lbl_group_filter', _t('QUOTES_GROUP').':');

        //Fill the quotes combo..
        $comboQuotes =& Piwi::CreateWidget('Combo', 'quotes_combo');
        $comboQuotes->SetSize(20);
        $comboQuotes->SetStyle('width: 250px; height: 358px;');
        $comboQuotes->AddEvent(ON_CHANGE, 'javascript: editQuote(this.value);');

        $model = $GLOBALS['app']->LoadGadget('Quotes', 'AdminModel');
        $quotes = $model->GetQuotes(-1);
        foreach($quotes as $quote) {
            $comboQuotes->AddOption($quote['title'], $quote['id']);
        }
        $tpl->SetVariable('lbl_quotes', _t('QUOTES_NAME'));
        $tpl->SetVariable('combo_quotes', $comboQuotes->Get());

        // title
        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetID('title');
        $title->setStyle('width: 256px;');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        // quotes groups
        $groupscombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupscombo->SetID('gid');
        $groupscombo->setStyle('width: 262px;');
        if (!Jaws_Error::IsError($groups) && !empty($groups)) {
            foreach($groups as $group) {
                $groupscombo->AddOption($group['title'], $group['id']);
            }
        }
        $tpl->SetVariable('lbl_group', _t('QUOTES_GROUP'));
        $tpl->SetVariable('group', $groupscombo->Get());

        // start time
        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->SetId('start_time');
        $startTime->showTimePicker(true);
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $startTime->setLanguageCode($this->gadget->GetRegistry('calendar_language', 'Settings'));
        $startTime->setCalType($this->gadget->GetRegistry('calendar_type', 'Settings'));
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        // stop time
        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->showTimePicker(true);
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->SetIncludeCSS(false);
        $stopTime->SetIncludeJS(false);
        $stopTime->setLanguageCode($this->gadget->GetRegistry('calendar_language', 'Settings'));
        $stopTime->setCalType($this->gadget->GetRegistry('calendar_type', 'Settings'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        // show_title
        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->SetID('show_title');
        $showTitle->setStyle('width: 182px;');
        $showTitle->AddOption(_t('GLOBAL_NO'),  'false');
        $showTitle->AddOption(_t('GLOBAL_YES'), 'true');
        $showTitle->SetDefault('true');
        $tpl->SetVariable('lbl_show_title', _t('QUOTES_SHOW_TITLE'));
        $tpl->SetVariable('show_title', $showTitle->Get());

        // published
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->setStyle('width: 182px;');
        $published->AddOption(_t('GLOBAL_NO'),  'false');
        $published->AddOption(_t('GLOBAL_YES'), 'true');
        $published->SetDefault('true');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        // quotation editor
        $quotation =& $GLOBALS['app']->LoadEditor('Blocks', 'quotation', '', '');
        $quotation->SetID('quotation');
        $quotation->TextArea->SetStyle('width: 100%;');
        $quotation->SetWidth('522px;');
        $tpl->SetVariable('lbl_quotation', _t('QUOTES_QUOTE_QUOTATION'));
        $tpl->SetVariable('quotation', $quotation->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript: saveQuote();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnDel =& Piwi::CreateWidget('Button', 'btn_del', _t('GLOBAL_DELETE', _t('QUOTES_QUOTE')), STOCK_DELETE);
        $btnDel->AddEvent(ON_CLICK, "javascript: deleteQuote();");
        $btnDel->SetStyle('display: none;');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript: stopAction();");
        $tpl->SetVariable('btn_cancel', $cancelAction->Get());

        $tpl->SetVariable('incompleteQuoteFields', _t('QUOTES_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmQuoteDelete', _t('QUOTES_CONFIRM_DELETE_QUOTE'));
        $tpl->ParseBlock('quotes/quotes_section');
        $tpl->ParseBlock('quotes');
        return $tpl->Get();
    }

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function QuoteGroups()
    {
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Quotes/templates/');
        $tpl->Load('AdminQuotes.html');
        $tpl->SetBlock('quotes');
        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('QuoteGroups'));
        $tpl->SetBlock('quotes/groups_section');

        //Fill the groups combo..
        $comboGroups =& Piwi::CreateWidget('Combo', 'groups_combo');
        $comboGroups->SetSize(20);
        $comboGroups->SetStyle('width: 250px; height: 358px;');
        $comboGroups->AddEvent(ON_CHANGE, 'javascript: editGroup(this.value);');
        $model = $GLOBALS['app']->LoadGadget('Quotes', 'AdminModel');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $comboGroups->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('combo_groups', $comboGroups->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $viewMode =& Piwi::CreateWidget('Combo', 'view_mode');
        $viewMode->SetID('view_mode');
        $viewMode->setStyle('width: 128px;');
        $viewMode->AddOption(_t('QUOTES_GROUPS_VIEW_MODE_COMPACT'), 0);
        $viewMode->AddOption(_t('QUOTES_GROUPS_VIEW_MODE_FULL'),    1);
        $tpl->SetVariable('lbl_view_mode', _t('QUOTES_GROUPS_VIEW_MODE'));
        $tpl->SetVariable('view_mode', $viewMode->Get());

        $viewType =& Piwi::CreateWidget('Combo', 'view_type');
        $viewType->SetID('view_type');
        $viewType->setStyle('width: 128px;');
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_SIMPLE'),        0);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_UP'),    1);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_DOWN'),  2);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_LEFT'),  3);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_RIGHT'), 4);
        $tpl->SetVariable('lbl_view_type', _t('QUOTES_GROUPS_VIEW_TYPE'));
        $tpl->SetVariable('view_type', $viewType->Get());

        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->SetID('show_title');
        $showTitle->setStyle('width: 128px;');
        $showTitle->AddOption(_t('GLOBAL_NO'),  'false');
        $showTitle->AddOption(_t('GLOBAL_YES'), 'true');
        $showTitle->SetDefault('true');
        $tpl->SetVariable('lbl_show_title', _t('QUOTES_SHOW_TITLE'));
        $tpl->SetVariable('show_title', $showTitle->Get());

        $limitcount =& Piwi::CreateWidget('Entry', 'limit_count', '0');
        $limitcount->setStyle('width: 120px;');
        $tpl->SetVariable('lbl_limit_count', _t('QUOTES_GROUPS_COUNT_ENTRY'));
        $tpl->SetVariable('limit_count', $limitcount->Get());

        $randomly =& Piwi::CreateWidget('Combo', 'random');
        $randomly->SetID('random');
        $randomly->setStyle('width: 128px;');
        $randomly->AddOption(_t('GLOBAL_NO'),  'false');
        $randomly->AddOption(_t('GLOBAL_YES'), 'true');
        $randomly->SetDefault('true');
        $tpl->SetVariable('lbl_random', _t('QUOTES_GROUPS_RANDOM'));
        $tpl->SetVariable('random', $randomly->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->setStyle('width: 128px;');
        $published->AddOption(_t('GLOBAL_NO'),  'false');
        $published->AddOption(_t('GLOBAL_YES'), 'true');
        $published->SetDefault('true');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $saveGroup =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveGroup->AddEvent(ON_CLICK, "javascript: saveGroup();");
        $tpl->SetVariable('btn_save', $saveGroup->Get());

        $GroupQuotes =& Piwi::CreateWidget('Button', 'add_quotes', _t('QUOTES_ADD_QUOTES'), STOCK_EDIT);
        $GroupQuotes->AddEvent(ON_CLICK, "javascript: editGroupQuotes();");
        $GroupQuotes->SetStyle('display: none;');
        $tpl->SetVariable('add_quotes', $GroupQuotes->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript: stopAction();");
        $tpl->SetVariable('btn_cancel', $cancelAction->Get());

        $deleteGroup =& Piwi::CreateWidget('Button', 'btn_del', _t('GLOBAL_DELETE', _t('QUOTES_GROUP')), STOCK_DELETE);
        $deleteGroup->AddEvent(ON_CLICK, "javascript: deleteGroup();");
        $deleteGroup->SetStyle('display: none;');
        $tpl->SetVariable('btn_del', $deleteGroup->Get());

        $tpl->SetVariable('incompleteGroupFields', _t('QUOTES_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmGroupDelete', _t('QUOTES_CONFIRM_DELETE_GROUP'));

        $tpl->ParseBlock('quotes/groups_section');
        $tpl->ParseBlock('quotes');
        return $tpl->Get();
    }

    /**
     * Returns the quotes-group management
     *
     * @access  public
     * @return  string
     */
    function GroupQuotesUI()
    {
        $tpl = new Jaws_Template('gadgets/Quotes/templates/');
        $tpl->Load('AdminQuotes.html');
        $tpl->SetBlock('quotes');
        $tpl->SetBlock('quotes/quotes_groups_ui_section');

        $model = $GLOBALS['app']->LoadGadget('Quotes', 'AdminModel');

        $quotesCombo =& Piwi::CreateWidget('CheckButtons', 'group_quotes');
        $quotesCombo->SetID('group_quotes');
        $quotesCombo->SetColumns(1);
        $quotes = $model->GetQuotes(-1, -1);
        foreach ($quotes as $quote) {
            $max_size = 48;
            $quotesCombo->AddOption($quote['title'], $quote['id']);
        }

        $tpl->SetVariable('group_quotes_combo', $quotesCombo->Get());
        $tpl->SetVariable('title', _t('QUOTES_GROUPS_MARK_QUOTES'));

        $tpl->ParseBlock('quotes/quotes_groups_ui_section');
        $tpl->ParseBlock('quotes');
        return $tpl->Get();
    }
}