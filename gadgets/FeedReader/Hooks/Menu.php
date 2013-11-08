<?php
/**
 * FeedReader - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    FeedReader
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
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
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('FeedReader', 'DisplayFeeds'),
                        'title' => _t('FEEDREADER_NAME'));

        $model  = $this->gadget->model->load('Feed');
        $feeds = $model->GetFeeds();
        if (!Jaws_Error::isError($feeds)) {
            $max_size = 20;
            foreach ($feeds as $feed) {
                $url = $GLOBALS['app']->Map->GetURLFor('FeedReader', 'GetFeed', array('id' => $feed['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($feed['title']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($feed['title'], 0, $max_size).'...' :
                                            $feed['title']);
            }
        }

        return $urls;
    }
}
