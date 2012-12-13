<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PollAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function Admin()
    {
        if ($this->GetPermission('ManagePolls')) {
            return $this->Polls();
        } elseif ($this->GetPermission('ManageGroups')) {
            return $this->PollGroups();
        }

        $this->CheckPermission('ViewReports');
    }

    /**
     * Prepares the poll menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Polls', 'PollGroups', 'Reports');
        if (!in_array($action, $actions)) {
            $action = 'Polls';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->GetPermission('ManagePolls')) {
            $menubar->AddOption('Polls', _t('POLL_POLLS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=Polls', 'gadgets/Poll/images/polls_mini.png');
        }
        if ($this->GetPermission('ManageGroups')) {
            $menubar->AddOption('PollGroups', _t('POLL_GROUPS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=PollGroups', 'gadgets/Poll/images/groups_mini.png');
        }
        if ($this->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', _t('POLL_REPORTS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=Reports', 'gadgets/Poll/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Prepares the data (an array) of polls
     *
     * @access  public
     * @param   int     $offset  Offset of data
     * @return  array   Polls Data array
     */
    function GetPolls($offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
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
            if ($this->GetPermission('ManagePolls')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editPoll(this, '".$poll['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('POLL_POLLS_ANSWERS'),
                                            "javascript: editPollAnswers(this, '" . $poll['id'] . "');",
                                            'gadgets/Poll/images/polls_mini.png');
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
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $total = $model->TotalOfData('poll');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('polls_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('POLL_POLLS_QUESTION'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
        $column2->SetStyle('width: 56px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width: 60px; white-space:nowrap;');
        $grid->AddColumn($column3);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

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
        $this->CheckPermission('ManagePolls');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPolls.html');
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
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPolls.html');
        $tpl->SetBlock('PollUI');

        $question =& Piwi::CreateWidget('Entry', 'question', '');
        $question->setStyle('width: 256px;');
        $tpl->SetVariable('lbl_question', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('question', $question->Get());

        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $groupCombo->setStyle('width: 262px;');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $groups = $model->GetPollGroups();
        foreach($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('POLL_GROUPS'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $selectType =& Piwi::CreateWidget('Combo', 'select_type');
        $selectType->SetID('select_type');
        $selectType->setStyle('width: 100px;');
        $selectType->AddOption(_t('POLL_POLLS_SELECT_SINGLE'), 0);
        $selectType->AddOption(_t('POLL_POLLS_SELECT_MULTI'),  1);
        $tpl->SetVariable('lbl_select_type', _t('POLL_POLLS_SELECT_TYPE'));
        $tpl->SetVariable('select_type', $selectType->Get());

        $pollMode =& Piwi::CreateWidget('Combo', 'poll_type');
        $pollMode->SetID('poll_type');
        $pollMode->setStyle('width: 100px;');
        $pollMode->AddOption(_t('POLL_POLLS_TYPE_COOKIE'), 0);
        $pollMode->AddOption(_t('POLL_POLLS_TYPE_FREE'),   1);
        $tpl->SetVariable('lbl_poll_type', _t('POLL_POLLS_TYPE'));
        $tpl->SetVariable('poll_type', $pollMode->Get());

        $resultView =& Piwi::CreateWidget('Combo', 'result_view');
        $resultView->SetID('result_view');
        $resultView->setStyle('width: 100px;');
        $resultView->AddOption(_t('GLOBAL_NO'),  0);
        $resultView->AddOption(_t('GLOBAL_YES'), 1);
        $resultView->SetDefault(1);
        $tpl->SetVariable('lbl_result_view', _t('POLL_POLLS_RESULT_VIEW'));
        $tpl->SetVariable('result_view', $resultView->Get());

        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->SetId('start_time');
        $startTime->showTimePicker(true);
        $startTime->setLanguageCode($GLOBALS['app']->Registry->Get('/gadgets/Settings/calendar_language'));
        $startTime->setCalType($GLOBALS['app']->Registry->Get('/gadgets/Settings/calendar_type'));
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->showTimePicker(true);
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->SetIncludeCSS(false);
        $stopTime->SetIncludeJS(false);
        $stopTime->setLanguageCode($GLOBALS['app']->Registry->Get('/gadgets/Settings/calendar_language'));
        $stopTime->setCalType($GLOBALS['app']->Registry->Get('/gadgets/Settings/calendar_type'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        $visible =& Piwi::CreateWidget('Combo', 'visible');
        $visible->SetID('visible');
        $visible->setStyle('width: 100px;');
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
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPolls.html');
        $tpl->SetBlock('PollAnswersUI');

        $question =& Piwi::CreateWidget('Entry', 'question', '');
        $question->setStyle('width: 256px;');
        $question->SetEnabled(false);
        $tpl->SetVariable('lbl_question', _t('POLL_POLLS_QUESTION'));
        $tpl->SetVariable('question', $question->Get());

        $answer =& Piwi::CreateWidget('Entry', 'answer', '');
        $answer->setStyle('width: 224px;');
        $answer->AddEvent(ON_KPRESS, 'javascript: keypressOnAnswer(event);');
        $tpl->SetVariable('lbl_answer', _t('POLL_POLLS_ANSWER'));
        $tpl->SetVariable('answer', $answer->Get());

        $answersCombo =& Piwi::CreateWidget('Combo', 'answers_combo');
        $answersCombo->SetSize(12);
        $answersCombo->SetStyle('width: 230px;');
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

    /**
     * Prepares the data (an array) of polls
     *
     * @access  public
     * @param   int     $offset  Offset of data
     * @return  array   Data array
     */
    function GetPollGroups($offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $groups = $model->GetPollGroups(10, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        $newData = array();
        foreach($groups as $group) {
            $groupData = array();
            $groupData['question'] = $group['title'];
            if ($group['visible'] == 1) {
                $groupData['visible'] = _t('GLOBAL_YES');
            } else {
                $groupData['visible'] = _t('GLOBAL_NO');
            }
            $actions = '';
            if ($this->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editPollGroup(this, '" . $group['id'] . "');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('POLL_GROUPS_POLLS_TITLE'),
                                            "javascript: editPollGroupPolls(this, '" . $group['id'] . "');",
                                            'gadgets/Poll/images/polls_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deletePollGroup(this, '". $group['id'] ."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $groupData['actions'] = $actions;
            $newData[] = $groupData;
        }
        return $newData;
    }

    /**
     * Build the datagrid of polls
     *
     * @access  public
     * @return  string  XHTML of Datagrid
     */
    function PollGroupsDatagrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $total = $model->TotalOfData('poll_groups');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('pollgroups_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
        $column2->SetStyle('width: 56px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width: 60px; white-space:nowrap;');
        $grid->AddColumn($column3);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML of view
     */
    function PollGroups()
    {
        $this->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPollGroups.html');
        $tpl->SetBlock('PollGroups');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('PollGroups'));
        $tpl->SetVariable('grid', $this->PollGroupsDatagrid());
        $tpl->SetVariable('pollgroup_ui', $this->PollGroupUI());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: savePollGroup();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('incompleteGroupsFields',   _t('POLL_POLLS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmPollGroupDelete',   _t('POLL_GROUPS_CONFIRM_DELETE'));
        $tpl->SetVariable('addPollGroup_title',       _t('POLL_GROUPS_ADD_TITLE'));
        $tpl->SetVariable('editPollGroup_title',      _t('POLL_GROUPS_EDIT_TITLE'));
        $tpl->SetVariable('editPollGroupPolls_title', _t('POLL_GROUPS_POLLS_TITLE'));
        $tpl->SetVariable('legend_title',             _t('POLL_GROUPS_ADD_TITLE'));

        $tpl->ParseBlock('PollGroups');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given poll group
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupUI()
    {
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPollGroups.html');
        $tpl->SetBlock('PollGroupUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $visible =& Piwi::CreateWidget('Combo', 'visible');
        $visible->SetID('visible');
        $visible->SetStyle('width: 100px;');
        $visible->AddOption(_t('GLOBAL_NO'),  0);
        $visible->AddOption(_t('GLOBAL_YES'), 1);
        $visible->SetDefault(1);
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE'));
        $tpl->SetVariable('visible', $visible->Get());

        $tpl->ParseBlock('PollGroupUI');

        return $tpl->Get();
    }

    /**
     * Returns the poll-group management
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PollGroupPollsUI()
    {
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminPollGroups.html');
        $tpl->SetBlock('PollGroupPollsUI');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetStyle('width: 200px;');
        $title->SetEnabled(false);
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $polls = $model->GetPolls();
        $pollsCombo =& Piwi::CreateWidget('CheckButtons', 'pg_polls_combo');
        foreach ($polls as $poll) {
            $pollsCombo->AddOption($poll['question'], $poll['id']);
        }
        $pollsCombo->SetColumns(1);
        $tpl->SetVariable('pg_polls_combo', $pollsCombo->Get());

        $tpl->ParseBlock('PollGroupPollsUI');
        return $tpl->Get();
    }

    /**
     * View report
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Reports()
    {
        $this->CheckPermission('ViewReports');
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('Poll', 'AdminModel');
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminReports.html');
        $tpl->SetBlock('Reports');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));

        $tpl->SetVariable('lbl_pollgroups', _t('POLL_GROUPS'));
        $groupsCombo =& Piwi::CreateWidget('Combo', 'pollgroups');
        $groupsCombo->SetID('pollgroups');
        $groupsCombo->SetStyle('width: 300px;');
        $groupsCombo->AddEvent(ON_CHANGE, "javascript: getGroupPolls(this.value);");
        $groups = $model->GetPollGroups();
        $groupsCombo->AddOption('', 0);
        foreach($groups as $group) {
            $groupsCombo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('pollgroups_combo', $groupsCombo->Get());

        $tpl->SetVariable('lbl_grouppolls', _t('POLL_POLLS'));
        $pollsCombo =& Piwi::CreateWidget('Combo', 'grouppolls');
        $pollsCombo->SetID('grouppolls');
        $pollsCombo->SetStyle('height: 254px; width: 300px;');
        $pollsCombo->SetSize(15);
        $pollsCombo->AddEvent(ON_CHANGE, 'javascript: showResult(this.value);');
        $tpl->SetVariable('grouppolls_combo', $pollsCombo->Get());

        $tpl->ParseBlock('Reports');
        return $tpl->Get();
    }

    /**
     * Get the poll results
     *
     * @access  public
     * @param   int     $pid    Poll ID
     * @return  string  XHTML template content
     */
    function PollResultsUI($pid)
    {
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('AdminReports.html');
        $tpl->SetBlock('PollResults');
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $poll = $model->GetPoll($pid);
        if (Jaws_Error::IsError($poll)) {
            //we need to handle errors
            return '';
        }

        $answers = $model->GetPollAnswers($poll['id']);
        if (!Jaws_Error::IsError($answers)) {
            $total_votes = array_sum(array_map(create_function('$row','return $row["votes"];'), $answers));
            $tpl->SetVariable('lbl_total_votes', _t('POLL_REPORTS_TOTAL_VOTES'));
            $tpl->SetVariable('total_votes', $total_votes);

            foreach($answers as $answer) {
                $tpl->SetBlock('PollResults/answer');
                $tpl->SetVariable('answer', $answer['answer']);
                $percent = (($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100));
                $tpl->SetVariable('percent', _t('POLL_REPORTS_PERCENT', $percent));
                $tpl->SetVariable('image_width', floor($percent*1.5));
                $tpl->SetVariable('votes', $answer['votes']);
                $tpl->ParseBlock('PollResults/answer');
            }
        }

        $tpl->ParseBlock('PollResults');
        return $tpl->Get();
    }

}