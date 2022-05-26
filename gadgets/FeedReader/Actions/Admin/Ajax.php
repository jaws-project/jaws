<?php
/**
 * FeedReader AJAX API
 *
 * @category   Ajax
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function __construct($gadget)
    {
        parent::__construct($gadget);
        $this->_Model = $this->gadget->model->loadAdmin('Feed');
    }

    /**
     * Gets information of the feed site
     *
     * @access  public
     * @return  mixed   Feed site information or false on error
     */
    function GetFeed()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
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
     * @return  array   Response array (notice or error)
     */
    function InsertFeed()
    {
        @list($title, $url, $cache_time, $view_type, $count_entry,
            $title_view, $alias, $published
        ) = $this->gadget->request->fetchAll('post');
        $result = $this->_Model->InsertFeed(
            $title, $url, $cache_time, $view_type, $count_entry, $title_view, $alias, $published
        );
        if (Jaws_Error::IsError($result)) {
            return $this->gadget->session->response($result->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('SITE_ADDED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Updates the feed site information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateFeed()
    {
        @list($id, $title, $url, $cache_time, $view_type,
            $count_entry, $title_view, $alias, $published
        ) = $this->gadget->request->fetchAll('post');
        $result = $this->_Model->UpdateFeed(
            $id, $title, $url, $cache_time,
            $view_type, $count_entry, $title_view, $alias, $published
        );
        if (Jaws_Error::IsError($result)) {
            return $this->gadget->session->response($result->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('SITE_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Deletes the feed site
     *
     * @access  public
     * @return  array  Response array (notice or error)
     */
    function DeleteFeed()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $result = $this->_Model->DeleteFeed($id);
        if (Jaws_Error::IsError($result)) {
            return $this->gadget->session->response($result->getMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('SITE_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Gets feed sites for data grid
     *
     * @access  public
     * @return  array   Feed sites
     */
    function getData()
    {
        @list($offset) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Feed');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetFeedSites($offset);
    }
}