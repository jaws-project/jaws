<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
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
        $polls = $pModel->GetPolls(null, true);
        if (!Jaws_Error::isError($polls)) {
            $p = array();
            foreach ($polls as $poll) {
                $p[$poll['id']] = $poll['title'];
            }

            $p = array('0' => $this::t('LAYOUT_LAST')) + $p;
            $result[] = array(
                'title' => $this::t('ACTION_POLL_TITLE'),
                'value' => $p
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
        if(empty($pid)) {
            $pid = (int)$this->gadget->request->fetch('id', 'get');
        }

        if (empty($pid)) {
            $poll = $model->GetLastPoll();
        } else {
            $poll = $model->GetPoll($pid);
        }

        if (Jaws_Error::IsError($poll) || empty($poll) || ($poll['published'] == false) ||
            (!empty($poll['start_time']) && (time() < $poll['start_time'])) ||
            (!empty($poll['stop_time']) && (time() > $poll['stop_time']))
        ) {
            return '';
        }

        $tpl = $this->gadget->template->load('Poll.html');
        $tpl->SetBlock('poll');
        $tpl->SetVariable('title', $this::t('ACTION_POLL_TITLE'));
        $tpl->SetVariable('poll_title', $poll['title']);

        $response = $this->gadget->session->pop('Vote');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $allowVote = false;
        switch ($poll['restriction']) {
            case Poll_Info::POLL_RESTRICTION_TYPE_IP:
                $ip = $_SERVER['REMOTE_ADDR'];
                $allowVote = $model->CheckAllowVoteForIP($poll['id'], $ip);
                break;
            case Poll_Info::POLL_RESTRICTION_TYPE_USER:
                $currentUser = $this->app->session->user->id;
                $allowVote = $model->CheckAllowVoteForUser($poll['id'], $currentUser);
                break;
            case Poll_Info::POLL_RESTRICTION_TYPE_SESSION:
                $session = $this->app->session->id;
                $allowVote = $model->CheckAllowVoteForSession($poll['id'], $session);
                break;
            case Poll_Info::POLL_RESTRICTION_TYPE_FREE:
                $allowVote = true;
                break;
        }

//        $votable = ($poll['restriction'] == 1) || (!$this->app->session->getCookie('poll_'.$poll['id']));
        if ($allowVote || $poll['result_view']) {
            //print the answers or results
            $answers = $model->GetPollAnswers($poll['id']);
            if (!Jaws_Error::IsError($answers)) {
                $block = $allowVote? 'voting' : 'result';
                $tpl->SetBlock("poll/{$block}");
                $tpl->SetVariable('pid', $poll['id']);
                $total_votes = $poll['total_votes'];
                foreach ($answers as $answer) {
                    $tpl->SetBlock("poll/{$block}/answer");
                    $tpl->SetVariable('aid', $answer['id']);
                    $tpl->SetVariable('order', $answer['order']+1);
                    $tpl->SetVariable('title', $answer['title']);
                    $tpl->SetVariable('type', $poll['type'] == 1? 'checkbox' : 'radio');
                    $tpl->SetVariable('votes', $answer['votes']);
                    $percent = ($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100);
                    $tpl->SetVariable('percent', $percent);
                    $tpl->SetVariable('txt_percent', $this::t('REPORTS_PERCENT', $percent));
                    $tpl->ParseBlock("poll/{$block}/answer");
                }

                $tpl->SetVariable('total_votes', $total_votes);
                $tpl->SetVariable('lbl_total_votes', $this::t('REPORTS_TOTAL_VOTES'));

                if ($allowVote) {
                    $btnVote =& Piwi::CreateWidget('Button', 'btn_vote', $this::t('VOTE'));
                    $btnVote->SetSubmit();
                    $tpl->SetVariable('btn_vote', $btnVote->Get());
                }

                if ($poll['result_view']) {
                    $link = $this->gadget->urlMap('ViewResult', array('id' => $poll['id']));
                    $viewRes =& Piwi::CreateWidget('Link', $this::t('REPORTS_RESULTS'), $link);
                    $tpl->SetVariable('result_link', $viewRes->Get());
                }

                $tpl->ParseBlock("poll/{$block}");
            }
        }

        if (!$allowVote) {
            $tpl->SetVariable('already_message', $this::t('ALREADY_VOTED'));
        }

        if (!$poll['result_view']) {
            $tpl->SetVariable('disabled_message', $this::t('RESULT_DISABLED'));
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

            $pgroups = array('0' => $this::t('LAYOUT_POLLS_ALL')) + $pgroups;
            $result[] = array(
                'title' => Jaws::t('CATEGORY'),
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
            $tpl->SetVariable('title', $this::t('ACTION_POLLS_INGROUP_TITLE', $group['title']));
        } else {
            $tpl->SetVariable('title', $this::t('ACTION_POLLS_TITLE'));
        }

        $polls = $pModel->GetPolls($gid, true);
        if (!Jaws_Error::isError($polls)) {
            foreach ($polls as $poll) {
                $tpl->SetBlock('Polls/poll');
                $tpl->SetVariable('url', $this->gadget->urlMap('Poll', array('id' => $poll['id'])));
                $tpl->SetVariable('title', $poll['title']);
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
        $post = $this->gadget->request->fetch(array('pid', 'answers:array'), 'post');
        $model = $this->gadget->model->load('Poll');
        $poll = $model->GetPoll((int)$post['pid']);
        if (!Jaws_Error::IsError($poll) && !empty($poll)) {
            $allowVote = false;
            switch ($poll['restriction']) {
                case Poll_Info::POLL_RESTRICTION_TYPE_IP:
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $allowVote = $model->CheckAllowVoteForIP($poll['id'], $ip);
                    break;
                case Poll_Info::POLL_RESTRICTION_TYPE_USER:
                    $currentUser = $this->app->session->user->id;
                    $allowVote = $model->CheckAllowVoteForUser($poll['id'], $currentUser);
                    break;
                case Poll_Info::POLL_RESTRICTION_TYPE_SESSION:
                    $session = $this->app->session->id;
                    $allowVote = $model->CheckAllowVoteForSession($poll['id'], $session);
                    break;
                case Poll_Info::POLL_RESTRICTION_TYPE_FREE:
                    $allowVote = true;
                    break;
            }


            if ($allowVote && is_array($post['answers']) && count($post['answers']) > 0) {
                $this->app->session->setCookie('poll_' . $poll['id'], 'voted',
                    (int)$this->gadget->registry->fetch('cookie_period') * 24 * 60);
                $res = $model->AddAnswerVotes($poll['id'], $post['answers']);
            }
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push(
                    $res->GetMessage(),
                    RESPONSE_ERROR,
                    'Vote'
                );
            } else {
                $this->gadget->session->push(
                    $this::t('THANKS'),
                    RESPONSE_NOTICE,
                    'Vote'
                );
            }
            Jaws_Header::Referrer();
        }
    }

    /**
     * Look for a term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewResult()
    {
        $pid = $this->gadget->request->fetch('id', 'get');

        $model = $this->gadget->model->load('Poll');
        $poll = $model->GetPoll($pid);
        if (Jaws_Error::IsError($poll) || empty($poll) || ($poll['result_view'] == false)) {
            return false;
        }

        $tpl = $this->gadget->template->load('Results.html');
        $tpl->SetBlock('results');
        $tpl->SetVariable('title', $this::t('ACTION_RESULT_TITLE'));
        $tpl->SetVariable('title', $poll['title']);

        $answers = $model->GetPollAnswers($poll['id']);
        if (!Jaws_Error::IsError($answers)) {
            $total_votes = array_sum(array_map(
                function($row) {
                    return $row['votes'];
                },
                $answers
            ));
            $tpl->SetVariable('total_votes', $total_votes);
            $tpl->SetVariable('lbl_total_votes', $this::t('REPORTS_TOTAL_VOTES'));

            foreach($answers as $answer) {
                $tpl->SetBlock('results/answer');
                $tpl->SetVariable('title', $answer['title']);
                $percent = (($total_votes==0)? 0 : floor(($answer['votes']/$total_votes)*100));
                $tpl->SetVariable('txt_percent', $this::t('REPORTS_PERCENT', $percent));
                $tpl->SetVariable('percent', $percent);
                $tpl->SetVariable('votes', $answer['votes']);
                $tpl->ParseBlock('results/answer');
            }
        }

        $tpl->ParseBlock ('results');
        return $tpl->Get();
    }
}