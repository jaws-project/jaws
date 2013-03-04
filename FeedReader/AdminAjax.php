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
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Gets information of the RSS site
     *
     * @access  public
     * @param   int     $id    RSS Site ID
     * @return  mixed   RSS Site information or false on error
     */
    function GetRSS($id)
    {
        $rssInfo = $this->_Model->GetRSS($id);
        if (Jaws_Error::IsError($rssInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $rssInfo;
    }

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
     * @return  array   Response array (notice or error)
     */
    function InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $this->gadget->CheckPermission('ManageRSSSite');
        $this->_Model->InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
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
     * @return  array   Response array (notice or error)
     */
    function UpdateRSS($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $this->gadget->CheckPermission('ManageRSSSite');
        $this->_Model->UpdateRSS($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the RSS site
     *
     * @access  public
     * @param   int    $id  RSS Site ID
     * @return  array  Response array (notice or error)
     */
    function DeleteRSS($id)
    {
        $this->gadget->CheckPermission('ManageRSSSite');
        $this->_Model->DeleteRSS($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets RSS sites for data grid
     *
     * @access  public
     * @param   int     $offset Data offset
     * @return  array   RSS Sites
     */
    function GetData($offset)
    {
        $gadget = $GLOBALS['app']->LoadGadget('FeedReader', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetRSSSites($offset);
    }
}