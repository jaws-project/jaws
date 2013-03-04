<?php
require_once JAWS_PATH . 'gadgets/RssReader/Model.php';
/**
 * RssReader Gadget
 *
 * @category   GadgetModel
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class RssReader_AdminModel extends RssReader_Model
{
    /**
     * Inserts a new RSS site
     *
     * @access  public
     * @param   string  $title          Name of the RSS Site
     * @param   string  $url            URL of the RSS Site
     * @param   int     $cache_time     Cache time period in seconds
     * @param   int     $view_type      Display type (0-4)
     * @param   int     $count_entry    Number of viewable RSS title
     * @param   int     $title_view     Display title or not
     * @param   int     $visible        The visibility status of the RSS Site
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            INSERT INTO [[rss_sites]]
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
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_ADDED'),_t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the RSS Site information
     *
     * @access  public
     * @param   string  $id             RSS Site ID
     * @param   string  $title          Name of the RSS Site
     * @param   string  $url            URL of the RSS Site
     * @param   int     $cache_time     Cache time period in seconds
     * @param   int     $view_type      Display type (0-4)
     * @param   int     $count_entry    Number of viewable RSS title
     * @param   int     $title_view     Display title or not
     * @param   int     $visible        The visibility status of the RSS Site
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRSS($RSSid, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            UPDATE [[rss_sites]] SET
                [title]       = {title},
                [url]         = {url},
                [cache_time]  = {cache_time},
                [view_type]   = {view_type},
                [count_entry] = {count_entry},
                [title_view]  = {title_view},
                [visible]     = {visible}
            WHERE [id] = {id}';

        $params = array();
        $params['id']          = (int)$RSSid;
        $params['title']       = $title;
        $params['url']         = $url;
        $params['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $params['view_type']   = $view_type;
        $params['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_ERROR_SITE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_UPDATED'), _t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the RSS site
     *
     * @access  public
     * @param   int     $id  RSS Site ID
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteRSS($id)
    {
        $sql = 'DELETE FROM [[rss_sites]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_ERROR_SITE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_DELETED'), _t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}