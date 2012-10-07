<?php
/**
 * Poll Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PollLayoutHTML
{

    /**
     * Loads layout actions
     *
     * @access private
     */
    function LoadLayoutActions()
    {
        $actions = array();
        $actions['LastPoll'] = array(
            'mode' => 'LayoutAction',
            'name' =>  _t('POLL_LAYOUT_DISPLAY_LAST'),
            'desc' => _t('POLL_LAYOUT_DISPLAY_LAST_DESC')
        );
        $actions['ListOfPolls'] = array(
            'mode' => 'LayoutAction',
            'name' => _t('POLL_LAYOUT_LIST_POLLS'),
            'desc' => _t('POLL_LAYOUT_LIST_POLLS_DESC')
        );

        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $pollGroups = $model->GetPollGroups();
        if (!Jaws_Error::isError($pollGroups)) {
            foreach ($pollGroups as $pGroup) {
                $actions['ListOfPolls(' . $pGroup['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $pGroup['title'],
                    'desc' => _t('POLL_LAYOUT_LIST_INGROUP_POLLS_DESC')
                );
            }
        }

        $polls = $model->GetPolls();
        if (!Jaws_Error::isError($polls)) {
            foreach ($polls as $poll) {
                $actions['Display(' . $poll['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $poll['question'],
                    'desc' => ''
                );
            }
        }

        return $actions;
    }

    /**
     * Print the last poll
     *
     * @param   string  $pid Poll ID
     * @return  string  The poll form or the poll results
     * @access  public
     */
    function LastPoll()
    {
        return $this->Display(0);
    }

    /**
     * Prints all the enabled polls as a layout
     *
     * @access  public
     * @return  string  HTML view of a list of polls
     */
    function ListOfPolls($gid = null)
    {
        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('Polls.html');
        $tpl->SetBlock('Polls');

        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        if (!empty($gid)) {
            $group = $model->GetPollGroup($gid);
            if (Jaws_Error::isError($group) || empty($group)) {
                $group['title'] = '';
            }
            $tpl->SetVariable('title', _t('POLL_ACTION_POLLS_INGROUP_TITLE', $group['title']));
        } else {
            $tpl->SetVariable('title', _t('POLL_ACTION_POLLS_TITLE'));
        }

        $xss   = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $polls = $model->GetPolls($gid, true);
        if (!Jaws_Error::isError($polls)) {
            foreach ($polls as $poll) {
                $tpl->SetBlock('Polls/poll');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Poll', 'ViewPoll', array('id' => $poll['id'])));
                $tpl->SetVariable('question', $xss->filter($poll['question']));
                $tpl->ParseBlock('Polls/poll');
            }
        }
        $tpl->ParseBlock('Polls');
        return $tpl->Get();
    }

    /**
     * Builds the default template with polls and answers
     *
     * @param   string  $pollid Poll ID
     * @return  string  The poll form or the poll results
     * @access  public
     */
    function Display($pid = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        if (empty($pid)) {
            $poll = $model->GetLastPoll();
        } else {
            $poll = $model->GetPoll($pid);
        }

        if (Jaws_Error::IsError($poll) || empty($poll) || ($poll['visible'] == 0) || 
            (!empty($poll['start_time']) && ($GLOBALS['db']->Date() < $poll['start_time'])) ||
            (!empty($poll['stop_time']) && ($GLOBALS['db']->Date() > $poll['stop_time'])))
        {
            return '';
        }

        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('Poll.html');
        $tpl->SetBlock('Poll');
        $tpl->SetVariable('title', _t('POLL_ACTION_POLL_TITLE'));

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Poll')) {
            $tpl->SetBlock('Poll/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('Poll/response');
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $tpl->SetVariable('pid', $poll['id']);
        $tpl->SetVariable('question', $xss->filter($poll['question']));
        $votable = ($poll['poll_type'] == 1) || (!$GLOBALS['app']->Session->GetCookie('poll_'.$poll['id']));
        if ($votable || $poll['result_view']) {
            //print the answers or results
            $answers = $model->GetPollAnswers($poll['id']);
            if (!Jaws_Error::IsError($answers)) {
                $block = $votable? 'answer' : 'result';
                $total_votes = array_sum(array_map(create_function('$row','return $row["votes"];'), $answers));
                foreach ($answers as $answer) {
                    $tpl->SetBlock("Poll/{$block}");
                    $tpl->SetVariable('aid', $answer['id']);
                    $tpl->SetVariable('answer', $xss->filter($answer['answer']));
                    if ($poll['select_type'] == 1) {
                        $rb = '<input type="checkbox" name="answers[]" id="poll-answer-input-'.
                              $answer['id'].'" value="' .$answer['id']. '"/>';
                    } else {
                        $rb = '<input type="radio" name="answers[]" id="poll-answer-input-'.
                              $answer['id'].'" value="' .$answer['id']. '"/>';
                    }

                    $tpl->SetVariable('input', $rb);
                    $tpl->SetVariable('votes', $answer['votes']);
                    $percent = ($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100);
                    $tpl->SetVariable('percent', $percent);
                    $tpl->SetVariable('txt-percent', _t('POLL_REPORTS_PERCENT', $percent));
                    $tpl->ParseBlock("Poll/{$block}");
                }
            }
        }

        if ($votable) {
            $btnVote =& Piwi::CreateWidget('Button', 'btn_vote', _t('POLL_VOTE'));
            $btnVote->SetSubmit();
            $tpl->SetVariable('btn-vote', $btnVote->Get());
        } else {
            $tpl->SetVariable('already-message', _t('POLL_ALREADY_VOTED'));
        }

        $link = $GLOBALS['app']->Map->GetURLFor('Poll', 'ViewResult', array('id' => $poll['id']));
        if ($poll['result_view']) {
            if (!$votable) {
                $tpl->SetBlock("Poll/total-votes");
                $tpl->SetVariable('total-votes', $total_votes);
                $tpl->SetVariable('lbl-total-votes', _t('POLL_REPORTS_TOTAL_VOTES'));
                $tpl->ParseBlock("Poll/total-votes");
            }

            $viewRes =& Piwi::CreateWidget('Link', _t('POLL_REPORTS_RESULTS'), $link);
            $tpl->SetVariable('result-link', $viewRes->Get());
        } else {
            $tpl->SetVariable('disabled-message', _t('POLL_RESULT_DISABLED'));
        }

        $tpl->ParseBlock('Poll');
        return $tpl->Get();
    }
}
