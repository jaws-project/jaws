<?php
/**
 * RSSReader AJAX API
 *
 * @category   Ajax
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class RssReaderAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function RssReaderAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Get information of a RSS site
     *
     * @access  public
     * @param   int     $id    RSS Site ID
     * @return  array   RSS Site information
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
     * Insert the information of a RSS Site
     *
     * @access  public
     * @param   string  $title    Name of the RSS Site
     * @param   string  $url     URL of the RSS Site
     * @param   int     $cache_time
     * @param   int     $view_type
     * @param   int     $count_entry   The count of the viewable RSS title
     * @param   int     $title_view
     * @param   int     $visible The visible of the RSS Site
     * @return  bool    True on success and Jaws_Error on failure
     */
    function InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $this->CheckSession('RssReader', 'ManageRSSSite');
        $this->_Model->InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update RSS Site information
     *
     * @access  public
     * @param   string $id   ID of the RSS Site
     * @param   string  $title    Name of the RSS Site
     * @param   string  $url     URL of the RSS Site
     * @param   int     $cache_time
     * @param   int     $view_type
     * @param   int     $count_entry   The count of the viewable RSS title
     * @param   int     $title_view
     * @param   int     $visible The visible of the RSS Site
     * @return  array   Response (notice or error)
     */
    function UpdateRSS($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $this->CheckSession('RssReader', 'ManageRSSSite');
        $this->_Model->UpdateRSS($id, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a RSS site
     *
     * @access  public
     * @param   int    $id  RSS Site Id
     * @return  array  Response (notice or error)
     */
    function DeleteRSS($id)
    {
        $this->CheckSession('RssReader', 'ManageRSSSite');
        $this->_Model->DeleteRSS($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get RSS sites
     *
     * @access  public
     * @return  array  RSS Sites
     */
    function GetData($offset)
    {
        $gadget = $GLOBALS['app']->LoadGadget('RssReader', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetRSSSites($offset);
    }
}