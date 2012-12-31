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
class Poll_Actions_Polls extends Jaws_Gadget_HTML
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function PollsLayoutParams()
    {
        $result = array();
        $pModel = $GLOBALS['app']->LoadGadget('Poll', 'Model');
        $pollGroups = $pModel->GetPollGroups();
        if (!Jaws_Error::isError($pollGroups)) {
            $pgroups = array();
            foreach ($pollGroups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $pgroups = array('0' => _t('POLL_LAYOUT_POLLS_ALL')) + $pgroups;
            $result[] = array(
                'title' => _t('POLL_LAYOUT_POLLS'),
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

        $polls = $model->GetPolls($gid, true);
        if (!Jaws_Error::isError($polls)) {
            foreach ($polls as $poll) {
                $tpl->SetBlock('Polls/poll');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Poll', 'Poll', array('id' => $poll['id'])));
                $tpl->SetVariable('question', $poll['question']);
                $tpl->ParseBlock('Polls/poll');
            }
        }
        $tpl->ParseBlock('Polls');
        return $tpl->Get();
    }

}