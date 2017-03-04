<?php
/**
 * Quotes Gadget Action
 *
 * @category   GadgetAdmin
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Actions_Admin_Quotes extends Quotes_Actions_Admin_Default
{

    /**
     * Show quotes administration
     *
     * @access  public
     * @return  string HTML content of administration
     */
    function Quotes()
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
        $this->gadget->define('incompleteQuoteFields', _t('QUOTES_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmQuoteDelete', _t('QUOTES_CONFIRM_DELETE_QUOTE'));

        $tpl = $this->gadget->template->loadAdmin('Quotes.html');
        $tpl->SetBlock('quotes');
        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Quotes'));
        $tpl->SetBlock('quotes/quotes_section');

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();

        //Group Filter
        $combo =& Piwi::CreateWidget('Combo', 'group_filter');
        $combo->AddEvent(ON_CHANGE, 'javascript:fillQuotesCombo();');
        $combo->AddOption('', -1);
        foreach($groups as $group) {
            $combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('group_filter', $combo->Get());
        $tpl->SetVariable('lbl_group_filter', _t('QUOTES_GROUP').':');

        //Fill the quotes combo..
        $comboQuotes =& Piwi::CreateWidget('Combo', 'quotes_combo');
        $comboQuotes->SetSize(24);
        $comboQuotes->AddEvent(ON_CHANGE, 'javascript:editQuote(this.value);');

        $model = $this->gadget->model->load('Quotes');
        $quotes = $model->GetQuotes(-1);
        foreach($quotes as $quote) {
            $comboQuotes->AddOption($quote['title'], $quote['id']);
        }
        $tpl->SetVariable('lbl_quotes', $this->gadget->title);
        $tpl->SetVariable('combo_quotes', $comboQuotes->Get());

        // title
        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        // quotes groups
        $groupscombo =& Piwi::CreateWidget('Combo', 'gid');
        if (!Jaws_Error::IsError($groups) && !empty($groups)) {
            foreach($groups as $group) {
                $groupscombo->AddOption($group['title'], $group['id']);
            }
        }
        $tpl->SetVariable('lbl_group', _t('QUOTES_GROUP'));
        $tpl->SetVariable('group', $groupscombo->Get());

        // start time
        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $startTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $startTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        // stop time
        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $stopTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        // show_title
        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->AddOption(_t('GLOBAL_NO'),  'false');
        $showTitle->AddOption(_t('GLOBAL_YES'), 'true');
        $showTitle->SetDefault('true');
        $tpl->SetVariable('lbl_show_title', _t('QUOTES_SHOW_TITLE'));
        $tpl->SetVariable('show_title', $showTitle->Get());

        // published
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->AddOption(_t('GLOBAL_NO'),  'false');
        $published->AddOption(_t('GLOBAL_YES'), 'true');
        $published->SetDefault('true');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        // quotation editor
        $quotation =& $GLOBALS['app']->LoadEditor('Blocks', 'quotation', '', '');
        $tpl->SetVariable('lbl_quotation', _t('QUOTES_QUOTE_QUOTATION'));
        $tpl->SetVariable('quotation', $quotation->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript:saveQuote();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnDel =& Piwi::CreateWidget('Button', 'btn_del', _t('GLOBAL_DELETE', _t('QUOTES_QUOTE')), STOCK_DELETE);
        $btnDel->AddEvent(ON_CLICK, "javascript:deleteQuote();");
        $btnDel->SetStyle('display:none;');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript:stopAction();");
        $tpl->SetVariable('btn_cancel', $cancelAction->Get());

        $tpl->ParseBlock('quotes/quotes_section');
        $tpl->ParseBlock('quotes');
        return $tpl->Get();
    }

}