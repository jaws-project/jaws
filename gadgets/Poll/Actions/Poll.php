<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Poll extends Jaws_Gadget_Action
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function PollLayoutParams()
    {
        $result = array();
        $pModel = $this->gadget->model->load('Poll');
        $polls = $pModel->GetPolls();
        if (!Jaws_Error::isError($polls)) {
            $ppollss = array();
            foreach ($polls as $poll) {
                $ppollss[$poll['id']] = $poll['question'];
            }

            $ppollss = array('0' => _t('POLL_LAYOUT_LAST')) + $ppollss;
            $result[] = array(
                'title' => _t('POLL_ACTION_POLL_TITLE'),
                'value' => $ppollss
            );
        }

        return $result;
    }

    /**
     * Builds the default template with polls and answers
     *
     * @access  public
     * @param   int     $pid    Poll ID
     * @return  string  XHTML Template content
     */
    function Poll($pid = 0)
    {
        $model = $this->gadget->model->load('Poll');
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

        $tpl = $this->gadget->template->load('Poll.html');
        $tpl->SetBlock('poll');
        $tpl->SetVariable('title', _t('POLL_ACTION_POLL_TITLE'));

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Poll')) {
            $tpl->SetVariable('msg', $response);
        }

        $tpl->SetVariable('question', $poll['question']);
        $votable = ($poll['poll_type'] == 1) || (!$GLOBALS['app']->Session->GetCookie('poll_'.$poll['id']));
        if ($votable || $poll['result_view']) {
            //print the answers or results
            $answers = $model->GetPollAnswers($poll['id']);
            if (!Jaws_Error::IsError($answers)) {
                $block = $votable? 'voting' : 'result';
                $tpl->SetBlock("poll/{$block}");
                $tpl->SetVariable('pid', $poll['id']);
                $total_votes = array_sum(array_map(create_function('$row','return $row["votes"];'), $answers));
                foreach ($answers as $answer) {
                    $tpl->SetBlock("poll/{$block}/answer");
                    $tpl->SetVariable('aid', $answer['id']);
                    $tpl->SetVariable('rank', $answer['rank']+1);
                    $tpl->SetVariable('answer', $answer['answer']);
                    if ($poll['select_type'] == 1) {
                        $rb = '<input type="checkbox" name="answers[]" id="poll_answer_input_'.
                              $answer['id'].'" value="' .$answer['id']. '"/>';
                    } else {
                        $rb = '<input type="radio" name="answers[]" id="poll_answer_input_'.
                              $answer['id'].'" value="' .$answer['id']. '"/>';
                    }

                    $tpl->SetVariable('input', $rb);
                    $tpl->SetVariable('votes', $answer['votes']);
                    $percent = ($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100);
                    $tpl->SetVariable('percent', $percent);
                    $tpl->SetVariable('txt_percent', _t('POLL_REPORTS_PERCENT', $percent));
                    $tpl->ParseBlock("poll/{$block}/answer");
                }

                $tpl->SetVariable('total_votes', $total_votes);
                $tpl->SetVariable('lbl_total_votes', _t('POLL_REPORTS_TOTAL_VOTES'));

                if ($votable) {
                    $btnVote =& Piwi::CreateWidget('Button', 'btn_vote', _t('POLL_VOTE'));
                    $btnVote->SetSubmit();
                    $tpl->SetVariable('btn_vote', $btnVote->Get());
                }

                if ($poll['result_view']) {
                    $link = $this->gadget->urlMap('ViewResult', array('id' => $poll['id']));
                    $viewRes =& Piwi::CreateWidget('Link', _t('POLL_REPORTS_RESULTS'), $link);
                    $tpl->SetVariable('result_link', $viewRes->Get());
                }

                $tpl->ParseBlock("poll/{$block}");
            }
        }

        if (!$votable) {
            $tpl->SetVariable('already_message', _t('POLL_ALREADY_VOTED'));
        }

        if (!$poll['result_view']) {
            $tpl->SetVariable('disabled_message', _t('POLL_RESULT_DISABLED'));
        }

        $tpl->ParseBlock('poll');
        return $tpl->Get();
    }

    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function PollsLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Group');
        $pollGroups = $model->GetPollGroups();
        if (!Jaws_Error::isError($pollGroups)) {
            $pgroups = array();
            foreach ($pollGroups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $pgroups = array('0' => _t('POLL_LAYOUT_POLLS_ALL')) + $pgroups;
            $result[] = array(
                'title' => _t('GLOBAL_CATEGORY'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Builds the default template with polls and answers
     *
     * @access  public
     * @param   int     $gid    Poll group ID
     * @return  string  XHTML Template content
     */
    function Polls($gid = 0)
    {
        $tpl = $this->gadget->template->load('Polls.html');
        $tpl->SetBlock('Polls');

        $pModel = $this->gadget->model->load('Poll');
        $gModel = $this->gadget->model->load('Group');
        if (!empty($gid)) {
            $group = $gModel->GetPollGroup($gid);
            if (Jaws_Error::isError($group) || empty($group)) {
                $group['title'] = '';
            }
            $tpl->SetVariable('title', _t('POLL_ACTION_POLLS_INGROUP_TITLE', $group['title']));
        } else {
            $tpl->SetVariable('title', _t('POLL_ACTION_POLLS_TITLE'));
        }

        $polls = $pModel->GetPolls($gid, true);
        if (!Jaws_Error::isError($polls)) {
            foreach ($polls as $poll) {
                $tpl->SetBlock('Polls/poll');
                $tpl->SetVariable('url', $this->gadget->urlMap('Poll', array('id' => $poll['id'])));
                $tpl->SetVariable('question', $poll['question']);
                $tpl->ParseBlock('Polls/poll');
            }
        }
        $tpl->ParseBlock('Polls');
        return $tpl->Get();
    }

    /**
     * Adds a new vote to an answer of a certain poll
     *
     * @access  public
     */
    function Vote()
    {
        $post = jaws()->request->fetch(array('pid', 'answers:array'), 'post');
        $model = $this->gadget->model->load('Poll');
        $poll = $model->GetPoll((int)$post['pid']);
        if (!Jaws_Error::IsError($poll) && !empty($poll)) {
            if ((($poll['poll_type'] == 1) || (!$GLOBALS['app']->Session->GetCookie('poll_' . $poll['id']))) &&
                is_array($post['answers']) && count($post['answers']) > 0
            ) {
                $GLOBALS['app']->Session->SetCookie('poll_'.$poll['id'], 'voted',
                    (int) $this->gadget->registry->fetch('cookie_period')*24*60);
                foreach ($post['answers'] as $aid) {
                    $model->AddAnswerVote($poll['id'], (int)$aid);
                }
            }
        }

        $GLOBALS['app']->Session->PushSimpleResponse(_t('POLL_THANKS'), 'Poll');
        Jaws_Header::Referrer();
    }

    /**
     * Look for a term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewResult()
    {
        $pid = jaws()->request->fetch('id', 'get');

        $model = $this->gadget->model->load('Poll');
        $poll = $model->GetPoll($pid);
        if (Jaws_Error::IsError($poll) || empty($poll) || ($poll['result_view'] == 0)) {
            return false;
        }

        $tpl = $this->gadget->template->load('Results.html');
        $tpl->SetBlock('results');
        $tpl->SetVariable('title', _t('POLL_ACTION_RESULT_TITLE'));
        $tpl->SetVariable('question', $poll['question']);

        $answers = $model->GetPollAnswers($poll['id']);
        if (!Jaws_Error::IsError($answers)) {
            $total_votes = array_sum(array_map(create_function('$row','return $row["votes"];'), $answers));
            $tpl->SetVariable('total_votes', $total_votes);
            $tpl->SetVariable('lbl_total_votes', _t('POLL_REPORTS_TOTAL_VOTES'));

            foreach($answers as $answer) {
                $tpl->SetBlock('results/answer');
                $tpl->SetVariable('answer', $answer['answer']);
                $percent = (($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100));
                $tpl->SetVariable('txt_percent', _t('POLL_REPORTS_PERCENT', $percent));
                $tpl->SetVariable('percent', $percent);
                $tpl->SetVariable('votes', $answer['votes']);
                $tpl->ParseBlock('results/answer');
            }
        }

        $tpl->ParseBlock ('results');
        return $tpl->Get();
    }
}