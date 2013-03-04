<?php
require_once JAWS_PATH . 'gadgets/FeedReader/Model.php';
/**
 * FeedReader Gadget
 *
 * @category   GadgetModel
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_AdminModel extends FeedReader_Model
{
    /**
     * Inserts a new feed site
     *
     * @access  public
     * @param   string  $title          Name of the feed Site
     * @param   string  $url            URL of the feed Site
     * @param   int     $cache_time     Cache time period in seconds
     * @param   int     $view_type      Display type (0-4)
     * @param   int     $count_entry    Number of viewable feed title
     * @param   int     $title_view     Display title or not
     * @param   int     $visible        The visibility status of the feed Site
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertFeed($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            INSERT INTO [[feeds]]
                ([title], [url], [cache_time], [view_type], [count_entry], [title_view], [visible])
            VALUES
                ({title}, {url}, {cache_time}, {view_type}, {count_entry}, {title_view}, {visible})';

        $params = array();
        $params['title']       = $title;
        $params['url']         = $url;
        $params['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $params['view_type']   = $view_type;
        $params['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('FEEDREADER_ERROR_SITE_NOT_ADDED'),_t('FEEDREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FEEDREADER_SITE_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the feed Site information
     *
     * @access  public
     * @param   string  $id             Feed site ID
     * @param   string  $title          Name of the feed site
     * @param   string  $url            URL of the feed site
     * @param   int     $cache_time     Cache time period in seconds
     * @param   int     $view_type      Display type (0-4)
     * @param   int     $count_entry    Number of viewable feed title
     * @param   int     $title_view     Display title or not
     * @param   int     $visible        The visibility status of the feed site
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateFeed($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            UPDATE [[feeds]] SET
                [title]       = {title},
                [url]         = {url},
                [cache_time]  = {cache_time},
                [view_type]   = {view_type},
                [count_entry] = {count_entry},
                [title_view]  = {title_view},
                [visible]     = {visible}
            WHERE [id] = {id}';

        $params = array();
        $params['id']          = (int)$id;
        $params['title']       = $title;
        $params['url']         = $url;
        $params['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $params['view_type']   = $view_type;
        $params['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FEEDREADER_ERROR_SITE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FEEDREADER_ERROR_SITE_NOT_UPDATED'), _t('FEEDREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FEEDREADER_SITE_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the feed site
     *
     * @access  public
     * @param   int     $id  Feed site ID
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteFeed($id)
    {
        $sql = 'DELETE FROM [[feeds]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FEEDREADER_ERROR_SITE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FEEDREADER_ERROR_SITE_NOT_DELETED'), _t('FEEDREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FEEDREADER_SITE_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}