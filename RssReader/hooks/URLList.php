<?php
/**
 * RssReader - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    RssReader
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class RssReaderURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('RssReader', 'DefaultAction'),
                        'title' => _t('RSSREADER_NAME'));

        $model  = $GLOBALS['app']->loadGadget('RssReader', 'Model');
        $feeds = $model->GetRSSs();
        if (!Jaws_Error::isError($feeds)) {
            $max_size = 20;
            foreach ($feeds as $feed) {
                $url = $GLOBALS['app']->Map->GetURLFor('RssReader', 'GetFeed', array('id' => $feed['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($feed['title']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($feed['title'], 0, $max_size).'...' :
                                            $feed['title']);
            }
        }

        return $urls;
    }
}
