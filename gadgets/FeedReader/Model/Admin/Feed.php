<?php
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
class FeedReader_Model_Admin_Feed extends FeedReader_Model_Feed
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
        $fData = array();
        $fData['title']       = $title;
        $fData['url']         = $url;
        $fData['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $fData['view_type']   = (int)$view_type;
        $fData['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $fData['title_view']  = (int)$title_view;
        $fData['visible']     = (int)$visible;

        $objORM = Jaws_ORM::getInstance()->table('feeds');
        return $objORM->insert($fData)->exec();
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
        $fData = array();
        $fData['title']       = $title;
        $fData['url']         = $url;
        $fData['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $fData['view_type']   = (int)$view_type;
        $fData['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $fData['title_view']  = (int)$title_view;
        $fData['visible']     = (int)$visible;

        $objORM = Jaws_ORM::getInstance()->table('feeds');
        return $objORM->update($fData)->where('id', (int)$id)->exec();
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
        $objORM = Jaws_ORM::getInstance()->table('feeds');
        return $objORM->delete()->where('id', (int)$id)->exec();
    }
}