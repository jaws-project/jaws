<?php
/**
 * Poll - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Poll
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url'   => $this->gadget->urlMap('Poll'),
                        'title' => $this::t('LAYOUT_LAST'));
        $urls[] = array('url'   => $this->gadget->urlMap('Polls'),
                        'title' => $this::t('ACTIONS_POLLS'));

        $model  = $this->gadget->model->load('Poll');
        $polls = $model->GetPolls(null, true);
        if (!Jaws_Error::isError($polls)) {
            $max_size = 20;
            foreach ($polls as $poll) {
                $url   = $this->gadget->urlMap('ViewPoll', array('id' => $poll['id']));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($poll['title']) > $max_size)?
                                            Jaws_UTF8::substr($poll['title'], 0, $max_size).'...' :
                                            $poll['title']);
            }
        }
        return $urls;
    }

}