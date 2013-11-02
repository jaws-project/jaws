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
class Poll_Actions_Admin_Report extends Poll_Actions_Admin_Default
{

    /**
     * View report
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Reports()
    {
        $this->gadget->CheckPermission('ViewReports');
        $this->AjaxMe('script.js');

        $model = $this->gadget->loadModel('Group');
        $tpl = $this->gadget->loadAdminTemplate('Reports.html');
        $tpl->SetBlock('Reports');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));

        $tpl->SetVariable('lbl_pollgroups', _t('POLL_GROUPS'));
        $groupsCombo =& Piwi::CreateWidget('Combo', 'pollgroups');
        $groupsCombo->SetID('pollgroups');
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
        $tpl = $this->gadget->loadAdminTemplate('Reports.html');
        $tpl->SetBlock('PollResults');
        $model = $this->gadget->loadModel('Poll');
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