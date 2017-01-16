<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Admin_Poll extends Poll_Actions_Admin_Default
{
    /**
     * Prepares the data (an array) of polls
     *
     * @access  public
     * @param   int     $offset  Offset of data
     * @return  array   Polls Data array
     */
    function GetPolls($offset = null)
    {
        $model = $this->gadget->model->load('Poll');
        $polls = $model->GetPolls(null, false, 12, $offset);
        if (Jaws_Error::IsError($polls)) {
            return array();
        }

        $newData = array();
        foreach($polls as $poll) {
            $pollData = array();
            $pollData['title'] = $poll['title'];
            if ($poll['published'] == true) {
                $pollData['published'] = _t('GLOBAL_YES');
            } else {
                $pollData['published'] = _t('GLOBAL_NO');
            }
            $actions = '';
            if ($this->gadget->GetPermission('ManagePolls')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript:editPoll(this, '".$poll['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('POLL_POLLS_ANSWERS'),
                    "javascript:editPollAnswers(this, '" . $poll['id'] . "');",
                    'gadgets/Poll/Resources/images/polls_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript:deletePoll(this, '".$poll['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $pollData['actions'] = $actions;
            $newData[] = $pollData;
        }
        return $newData;
    }

    /**
     * Build the datagrid of polls
     *
     * @access  public
     * @return  string  XHTML of Datagrid
     */
    function PollsDatagrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('poll');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('polls_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('POLL_POLLS_QUESTION'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_PUBLISHED'), null, false);
        $column2->SetStyle('width:56px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width:60px; white-space:nowrap;');
        $grid->AddColumn($column3);

        return $grid->Get();
    }

    /**
     * Prepares the polls management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function Polls()
    {
        $this->gadget->CheckPermission('ManagePolls');
        $this->AjaxMe('script.js');
        $this->gadget->layout->setVariable('incompletePollsFields', _t('POLL_POLLS_INCOMPLETE_FIELDS'));
        $this->gadget->layout->setVariable('requiresTwoAnswers',    _t('POLL_ERROR_REQUIRES_TWO_ANSWERS'));
        $this->gadget->layout->setVariable('confirmPollDelete',     _t('POLL_POLLS_CONFIRM_DELETE'));
        $this->gadget->layout->setVariable('addPoll_title',         _t('POLL_POLLS_ADD_TITLE'));
        $this->gadget->layout->setVariable('editPoll_title',        _t('POLL_POLLS_EDIT_TITLE'));
        $this->gadget->layout->setVariable('editAnswers_title',     _t('POLL_POLLS_ANSWERS_TITLE'));
        $this->gadget->layout->setVariable('legend_title',          _t('POLL_POLLS_ADD_TITLE'));

        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $tpl = $this->gadget->template->loadAdmin('Polls.html');
        $tpl->SetBlock('Polls');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Polls'));
        $tpl->SetVariable('grid', $this->PollsDatagrid());
        $tpl->SetVariable('poll_ui', $this->PollUI());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:savePoll();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->ParseBlock('Polls');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given poll
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Polls.html');
        $tpl->SetBlock('PollUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('title', $title->Get());

        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $model = $this->gadget->model->load('Group');
        $groups = $model->GetPollGroups();
        foreach($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('POLL_GROUPS'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $type =& Piwi::CreateWidget('Combo', 'type');
        $type->SetID('type');
        $type->AddOption(_t('POLL_POLLS_SELECT_SINGLE'), 0);
        $type->AddOption(_t('POLL_POLLS_SELECT_MULTI'),  1);
        $tpl->SetVariable('lbl_type', _t('POLL_POLLS_TYPE'));
        $tpl->SetVariable('type', $type->Get());

        $pollMode =& Piwi::CreateWidget('Combo', 'restriction');
        $pollMode->SetID('restriction');
        $pollMode->AddOption(_t('POLL_RESTRICTION_TYPE_IP'), Poll_Info::POLL_RESTRICTION_TYPE_IP);
        $pollMode->AddOption(_t('POLL_RESTRICTION_TYPE_USER'), Poll_Info::POLL_RESTRICTION_TYPE_USER);
        $pollMode->AddOption(_t('POLL_RESTRICTION_TYPE_SESSION'), Poll_Info::POLL_RESTRICTION_TYPE_SESSION);
        $pollMode->AddOption(_t('POLL_RESTRICTION_TYPE_FREE'), Poll_Info::POLL_RESTRICTION_TYPE_FREE);
        $tpl->SetVariable('lbl_restriction', _t('POLL_POLLS_RESTRICTION'));
        $tpl->SetVariable('restriction', $pollMode->Get());

        $resultView =& Piwi::CreateWidget('Combo', 'result_view');
        $resultView->SetID('result_view');
        $resultView->AddOption(_t('GLOBAL_NO'),  0);
        $resultView->AddOption(_t('GLOBAL_YES'), 1);
        $resultView->SetDefault(1);
        $tpl->SetVariable('lbl_result_view', _t('POLL_POLLS_RESULT_VIEW'));
        $tpl->SetVariable('result_view', $resultView->Get());

        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->SetId('start_time');
        $startTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $startTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $stopTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->AddOption(_t('GLOBAL_NO'),  0);
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->SetDefault(1);
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('PollUI');

        return $tpl->Get();
    }

    /**
     * Show a form to edit a given poll answers
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollAnswersUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Polls.html');
        $tpl->SetBlock('PollAnswersUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetEnabled(false);
        $tpl->SetVariable('lbl_title', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('title', $title->Get());

        $answer =& Piwi::CreateWidget('Entry', 'answer', '');
        $answer->AddEvent(ON_KPRESS, 'javascript:keypressOnAnswer(event);');
        $tpl->SetVariable('lbl_answer', _t('POLL_POLLS_ANSWER'));
        $tpl->SetVariable('answer', $answer->Get());

        $answersCombo =& Piwi::CreateWidget('Combo', 'answers_combo');
        $answersCombo->SetSize(12);
        $answersCombo->AddEvent(ON_CHANGE, 'javascript:editAnswer();');
        $tpl->SetVariable('answers_combo', $answersCombo->Get());

        $btnAdd =& Piwi::CreateWidget('Button','btn_add', '', STOCK_ADD);
        $btnAdd->AddEvent(ON_CLICK, 'javascript:addAnswer();');
        $tpl->SetVariable('btn_add', $btnAdd->Get());

        $btnStop =& Piwi::CreateWidget('Button','btn_stop', '', STOCK_CANCEL);
        $btnStop->AddEvent(ON_CLICK, 'javascript:stopAnswer();');
        $tpl->SetVariable('btn_stop', $btnStop->Get());

        $btnDel =& Piwi::CreateWidget('Button','btn_del', '', STOCK_DELETE);
        $btnDel->AddEvent(ON_CLICK, 'javascript:delAnswer();');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $btnUp =& Piwi::CreateWidget('Button','btn_up', '', STOCK_UP);
        $btnUp->AddEvent(ON_CLICK, 'javascript:upAnswer();');
        $tpl->SetVariable('btn_up', $btnUp->Get());

        $btnDown =& Piwi::CreateWidget('Button','btn_down', '', STOCK_DOWN);
        $btnDown->AddEvent(ON_CLICK, 'javascript:downAnswer();');
        $tpl->SetVariable('btn_down', $btnDown->Get());

        $tpl->ParseBlock('PollAnswersUI');
        return $tpl->Get();
    }


}