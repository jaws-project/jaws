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
class Poll_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default action
     *
     * @acces  public
     * @return  string  XHTML template result
     */
    function DefaultAction()
    {
        $this->SetTitle(_t('POLL_NAME'));
        $pollHTML = $GLOBALS['app']->LoadGadget('Poll', 'HTML', 'Polls');
        return $pollHTML->Polls();
    }

    /**
     * Adds a new vote to an answer of a certain poll
     *
     * @access  public
     */
    function Vote()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('pid', 'answers'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $poll = $model->GetPoll($post['pid']);
        if (!Jaws_Error::IsError($poll) && isset($poll['id'])) {
            if ((($poll['poll_type'] == 1) || (!$GLOBALS['app']->Session->GetCookie('poll_'.$post['pid']))) &&
                is_array($post['answers']) && count($post['answers'])>0)
            {
                $GLOBALS['app']->Session->SetCookie('poll_'.$post['pid'], 'voted',
                            (int) $this->gadget->GetRegistry('cookie_period')*24*60);
                foreach ($post['answers'] as $aid) {
                    $model->AddAnswerVote($post['pid'], $aid);
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
        $request =& Jaws_Request::getInstance();
        $pid = $request->get('id', 'get');

        $model = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $poll = $model->GetPoll($pid);
        if (Jaws_Error::IsError($poll) || !isset($poll['id']) || ($poll['result_view'] == 0)) {
            return '';
        }

        $tpl = new Jaws_Template('gadgets/Poll/templates/');
        $tpl->Load('Results.html');
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