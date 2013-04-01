<?php
/**
 * Poll - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Poll
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PollURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Poll', 'LastPoll'),
                        'title' => _t('POLL_LAYOUT_DISPLAY_LAST'));
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Poll', 'ListOfPolls'),
                        'title' => _t('POLL_LAYOUT_LIST_POLLS'));

        $model  = $GLOBALS['app']->loadGadget('Poll', 'Model');
        $polls = $model->GetPolls(null, true);
        if (!Jaws_Error::isError($polls)) {
            $max_size = 20;
            foreach ($polls as $poll) {
                $url   = $GLOBALS['app']->Map->GetURLFor('Poll', 'ViewPoll', array('id' => $poll['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($poll['question']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($poll['question'], 0, $max_size).'...' :
                                            $poll['question']);
            }
        }
        return $urls;
    }

}