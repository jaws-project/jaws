<?php
/**
 * FeedReader - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    FeedReader
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls[] = array('url'   => $this->gadget->urlMap('DisplayFeeds'),
                        'title' => $this->gadget->title);

        $urls[] = array('url'   => $this->gadget->urlMap('UserFeedsList'),
            'title' => $this::t('USER_FEEDS'));

        $model  = $this->gadget->model->load('Feed');
        $feeds = $model->GetFeeds();
        if (!Jaws_Error::isError($feeds)) {
            $max_size = 20;
            foreach ($feeds as $feed) {
                $url = $this->gadget->urlMap('GetFeed', array('id' => $feed['id']));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($feed['title']) > $max_size)?
                                            Jaws_UTF8::substr($feed['title'], 0, $max_size).'...' :
                                            $feed['title']);
            }
        }

        return $urls;
    }
}
