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
class Quotes_Actions_Admin_Groups extends Quotes_Actions_Admin_Default
{
    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function QuoteGroups()
    {
        $this->AjaxMe('script.js');
        $this->gadget->layout->setVariable('incompleteGroupFields', _t('QUOTES_INCOMPLETE_FIELDS'));
        $this->gadget->layout->setVariable('confirmGroupDelete', _t('QUOTES_CONFIRM_DELETE_GROUP'));

        $tpl = $this->gadget->template->loadAdmin('Quotes.html');
        $tpl->SetBlock('quotes');
        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('QuoteGroups'));
        $tpl->SetBlock('quotes/groups_section');

        //Fill the groups combo..
        $comboGroups =& Piwi::CreateWidget('Combo', 'groups_combo');
        $comboGroups->SetSize(14);
        $comboGroups->AddEvent(ON_CHANGE, 'javascript:editGroup(this.value);');
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $comboGroups->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('combo_groups', $comboGroups->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('title', $titleEntry->Get());

        $viewMode =& Piwi::CreateWidget('Combo', 'view_mode');
        $viewMode->AddOption(_t('QUOTES_GROUPS_VIEW_MODE_COMPACT'), 0);
        $viewMode->AddOption(_t('QUOTES_GROUPS_VIEW_MODE_FULL'),    1);
        $tpl->SetVariable('lbl_view_mode', _t('QUOTES_GROUPS_VIEW_MODE'));
        $tpl->SetVariable('view_mode', $viewMode->Get());

        $viewType =& Piwi::CreateWidget('Combo', 'view_type');
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_SIMPLE'),        0);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_UP'),    1);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_DOWN'),  2);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_LEFT'),  3);
        $viewType->AddOption(_t('QUOTES_GROUPS_VIEW_TYPE_MARQUEE_RIGHT'), 4);
        $tpl->SetVariable('lbl_view_type', _t('QUOTES_GROUPS_VIEW_TYPE'));
        $tpl->SetVariable('view_type', $viewType->Get());

        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->AddOption(_t('GLOBAL_NO'),  'false');
        $showTitle->AddOption(_t('GLOBAL_YES'), 'true');
        $showTitle->SetDefault('true');
        $tpl->SetVariable('lbl_show_title', _t('QUOTES_SHOW_TITLE'));
        $tpl->SetVariable('show_title', $showTitle->Get());

        $limitcount =& Piwi::CreateWidget('Entry', 'limit_count', '0');
        $tpl->SetVariable('lbl_limit_count', _t('QUOTES_GROUPS_COUNT_ENTRY'));
        $tpl->SetVariable('limit_count', $limitcount->Get());

        $randomly =& Piwi::CreateWidget('Combo', 'random');
        $randomly->AddOption(_t('GLOBAL_NO'),  'false');
        $randomly->AddOption(_t('GLOBAL_YES'), 'true');
        $randomly->SetDefault('true');
        $tpl->SetVariable('lbl_random', _t('QUOTES_GROUPS_RANDOM'));
        $tpl->SetVariable('random', $randomly->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->AddOption(_t('GLOBAL_NO'),  'false');
        $published->AddOption(_t('GLOBAL_YES'), 'true');
        $published->SetDefault('true');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $saveGroup =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveGroup->AddEvent(ON_CLICK, "javascript:saveGroup();");
        $tpl->SetVariable('btn_save', $saveGroup->Get());

        $GroupQuotes =& Piwi::CreateWidget('Button', 'add_quotes', _t('QUOTES_ADD_QUOTES'), STOCK_EDIT);
        $GroupQuotes->AddEvent(ON_CLICK, "javascript:editGroupQuotes();");
        $GroupQuotes->SetStyle('display:none;');
        $tpl->SetVariable('add_quotes', $GroupQuotes->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript:stopAction();");
        $tpl->SetVariable('btn_cancel', $cancelAction->Get());

        $deleteGroup =& Piwi::CreateWidget('Button', 'btn_del', _t('GLOBAL_DELETE', _t('QUOTES_GROUP')), STOCK_DELETE);
        $deleteGroup->AddEvent(ON_CLICK, "javascript:deleteGroup();");
        $deleteGroup->SetStyle('display:none;');
        $tpl->SetVariable('btn_del', $deleteGroup->Get());

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
        $tpl = $this->gadget->template->loadAdmin('Quotes.html');
        $tpl->SetBlock('quotes');
        $tpl->SetBlock('quotes/quotes_groups_ui_section');

        $model = $this->gadget->model->load('Quotes');

        $quotesCombo =& Piwi::CreateWidget('CheckButtons', 'group_quotes');
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