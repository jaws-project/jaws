<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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
        $model = $this->gadget->loadModel('Poll');
        $polls = $model->GetPolls(null, false, 12, $offset);
        if (Jaws_Error::IsError($polls)) {
            return array();
        }

        $newData = array();
        foreach($polls as $poll) {
            $pollData = array();
            $pollData['question'] = $poll['question'];
            if ($poll['visible'] == 1) {
                $pollData['visible'] = _t('GLOBAL_YES');

            } else {
                $pollData['visible'] = _t('GLOBAL_NO');
            }
            $actions = '';
            if ($this->gadget->GetPermission('ManagePolls')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript: editPoll(this, '".$poll['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('POLL_POLLS_ANSWERS'),
                    "javascript: editPollAnswers(this, '" . $poll['id'] . "');",
                    'gadgets/Poll/Resources/images/polls_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript: deletePoll(this, '".$poll['id']."');",
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
        $model = $this->gadget->loadModel();
        $total = $model->TotalOfData('poll');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('polls_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('POLL_POLLS_QUESTION'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
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

        $tpl = $this->gadget->loadAdminTemplate('Polls.html');
        $tpl->SetBlock('Polls');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Polls'));
        $tpl->SetVariable('grid', $this->PollsDatagrid());
        $tpl->SetVariable('poll_ui', $this->PollUI());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: savePoll();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('incompletePollsFields', _t('POLL_POLLS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('requiresTwoAnswers',    _t('POLL_ERROR_REQUIRES_TWO_ANSWERS'));
        $tpl->SetVariable('confirmPollDelete',     _t('POLL_POLLS_CONFIRM_DELETE'));
        $tpl->SetVariable('addPoll_title',         _t('POLL_POLLS_ADD_TITLE'));
        $tpl->SetVariable('editPoll_title',        _t('POLL_POLLS_EDIT_TITLE'));
        $tpl->SetVariable('editAnswers_title',     _t('POLL_POLLS_ANSWERS_TITLE'));
        $tpl->SetVariable('legend_title',          _t('POLL_POLLS_ADD_TITLE'));

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
        $tpl = $this->gadget->loadAdminTemplate('Polls.html');
        $tpl->SetBlock('PollUI');

        $question =& Piwi::CreateWidget('Entry', 'question', '');
        $tpl->SetVariable('lbl_question', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('question', $question->Get());

        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $model = $this->gadget->loadModel('Group');
        $groups = $model->GetPollGroups();
        foreach($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('POLL_GROUPS'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $selectType =& Piwi::CreateWidget('Combo', 'select_type');
        $selectType->SetID('select_type');
        $selectType->AddOption(_t('POLL_POLLS_SELECT_SINGLE'), 0);
        $selectType->AddOption(_t('POLL_POLLS_SELECT_MULTI'),  1);
        $tpl->SetVariable('lbl_select_type', _t('POLL_POLLS_SELECT_TYPE'));
        $tpl->SetVariable('select_type', $selectType->Get());

        $pollMode =& Piwi::CreateWidget('Combo', 'poll_type');
        $pollMode->SetID('poll_type');
        $pollMode->AddOption(_t('POLL_POLLS_TYPE_COOKIE'), 0);
        $pollMode->AddOption(_t('POLL_POLLS_TYPE_FREE'),   1);
        $tpl->SetVariable('lbl_poll_type', _t('POLL_POLLS_TYPE'));
        $tpl->SetVariable('poll_type', $pollMode->Get());

        $resultView =& Piwi::CreateWidget('Combo', 'result_view');
        $resultView->SetID('result_view');
        $resultView->AddOption(_t('GLOBAL_NO'),  0);
        $resultView->AddOption(_t('GLOBAL_YES'), 1);
        $resultView->SetDefault(1);
        $tpl->SetVariable('lbl_result_view', _t('POLL_POLLS_RESULT_VIEW'));
        $tpl->SetVariable('result_view', $resultView->Get());

        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->SetId('start_time');
        $startTime->showTimePicker(true);
        $startTime->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $startTime->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->showTimePicker(true);
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->SetIncludeCSS(false);
        $stopTime->SetIncludeJS(false);
        $stopTime->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $stopTime->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        $visible =& Piwi::CreateWidget('Combo', 'visible');
        $visible->SetID('visible');
        $visible->AddOption(_t('GLOBAL_NO'),  0);
        $visible->AddOption(_t('GLOBAL_YES'), 1);
        $visible->SetDefault(1);
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE'));
        $tpl->SetVariable('visible', $visible->Get());

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
        $tpl = $this->gadget->loadAdminTemplate('Polls.html');
        $tpl->SetBlock('PollAnswersUI');

        $question =& Piwi::CreateWidget('Entry', 'question', '');
        $question->SetEnabled(false);
        $tpl->SetVariable('lbl_question', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('question', $question->Get());

        $answer =& Piwi::CreateWidget('Entry', 'answer', '');
        $answer->AddEvent(ON_KPRESS, 'javascript: keypressOnAnswer(event);');
        $tpl->SetVariable('lbl_answer', _t('POLL_POLLS_ANSWER'));
        $tpl->SetVariable('answer', $answer->Get());

        $answersCombo =& Piwi::CreateWidget('Combo', 'answers_combo');
        $answersCombo->SetSize(12);
        $answersCombo->AddEvent(ON_CHANGE, 'javascript: editAnswer();');
        $tpl->SetVariable('answers_combo', $answersCombo->Get());

        $btnAdd =& Piwi::CreateWidget('Button','btn_add', '', STOCK_ADD);
        $btnAdd->AddEvent(ON_CLICK, 'javascript: addAnswer();');
        $tpl->SetVariable('btn_add', $btnAdd->Get());

        $btnStop =& Piwi::CreateWidget('Button','btn_stop', '', STOCK_CANCEL);
        $btnStop->AddEvent(ON_CLICK, 'javascript: stopAnswer();');
        $tpl->SetVariable('btn_stop', $btnStop->Get());

        $btnDel =& Piwi::CreateWidget('Button','btn_del', '', STOCK_DELETE);
        $btnDel->AddEvent(ON_CLICK, 'javascript: delAnswer();');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $btnUp =& Piwi::CreateWidget('Button','btn_up', '', STOCK_UP);
        $btnUp->AddEvent(ON_CLICK, 'javascript: upAnswer();');
        $tpl->SetVariable('btn_up', $btnUp->Get());

        $btnDown =& Piwi::CreateWidget('Button','btn_down', '', STOCK_DOWN);
        $btnDown->AddEvent(ON_CLICK, 'javascript: downAnswer();');
        $tpl->SetVariable('btn_down', $btnDown->Get());

        $tpl->ParseBlock('PollAnswersUI');
        return $tpl->Get();
    }


}