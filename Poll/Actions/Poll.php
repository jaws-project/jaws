<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Actions_Poll extends Jaws_Gadget_HTML
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
        $pModel = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $polls = $pModel->GetPolls();
        if (!Jaws_Error::isError($polls)) {
            $ppollss = array();
            foreach ($polls as $poll) {
                $ppollss[$poll['id']] = $poll['question'];
            }

            $ppollss = array('0' => _t('POLL_LAYOUT_LAST')) + $ppollss;
            $result[] = array(
                'title' => _t('POLL_LAYOUT_POLL'),
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

        $tpl->SetVariable('pid', $poll['id']);
        $tpl->SetVariable('question', $poll['question']);
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
                    $tpl->SetVariable('answer', $answer['answer']);
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