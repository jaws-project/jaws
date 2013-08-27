<?php
/**
 * FeedReader AJAX API
 *
 * @category   Ajax
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function FeedReader_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $GLOBALS['app']->LoadGadget('FeedReader', 'AdminModel', 'Feed');
    }

    /**
     * Gets information of the feed site
     *
     * @access  public
     * @param   int     $id    Feed site ID
     * @return  mixed   Feed site information or false on error
     */
    function GetFeed($id)
    {
        $feed = $this->_Model->GetFeed($id);
        if (Jaws_Error::IsError($feed)) {
            return false; //we need to handle errors on ajax
        }

        return $feed;
    }

    /**
     * Inserts a new feed site
     *
     * @access  public
     * @param   string  $title          Name of the feed site
     * @param   string  $url            URL of the feed site
     * @param   int     $cache_time     Cache time period in seconds
     * @param   int     $view_type      Display type (0-4)
     * @param   int     $count_entry    Number of viewable feed title
     * @param   int     $title_view     Display title or not
     * @param   int     $visible        The visibility status of the feed site
     * @return  array   Response array (notice or error)
     */
    function InsertFeed($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $result = $this->_Model->InsertFeed($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FEEDREADER_SITE_ADDED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Updates the feed site information
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
     * @return  array   Response array (notice or error)
     */
    function UpdateFeed($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $result = $this->_Model->UpdateFeed($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FEEDREADER_SITE_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Deletes the feed site
     *
     * @access  public
     * @param   int    $id  Feed site ID
     * @return  array  Response array (notice or error)
     */
    function DeleteFeed($id)
    {
        $result = $this->_Model->DeleteFeed($id);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FEEDREADER_SITE_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Gets feed sites for data grid
     *
     * @access  public
     * @param   int     $offset Data offset
     * @return  array   Feed sites
     */
    function GetData($offset)
    {
        $gadget = $GLOBALS['app']->LoadGadget('FeedReader', 'AdminHTML', 'Feed');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetFeedSites($offset);
    }
}