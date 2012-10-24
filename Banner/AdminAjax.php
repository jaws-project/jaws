<?php
/**
 * Banner AJAX API
 *
 * @category   Ajax
 * @package    Banner
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BannerAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Get a banner info
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  mixed   False or Banner Info
     */
    function GetBanner($bid)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $banner = $this->_Model->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($banner['id'])) {
            $objDate = $GLOBALS['app']->loadDate();
            if (!empty($banner['start_time'])) {
                $banner['start_time'] = $objDate->Format($banner['start_time'], 'Y-m-d H:i:s');
            }
            if (!empty($banner['stop_time'])) {
                $banner['stop_time'] = $objDate->Format($banner['stop_time'], 'Y-m-d H:i:s');
            }
        }

        return $banner;
    }

    /**
     * Get a list of banners
     *
     * @access  public
     * @param   int      $bid    banner ID
     * @param   int      $gid    group ID
     * @return  mixed   False or Banners list
     */
    function GetBanners($bid = -1, $gid = -1)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $res = $this->_Model->GetBanners($bid, $gid);
        if (Jaws_Error::IsError($res)) {
            return false; //we need to handle errors on ajax
        }

        return $res;
    }

    /**
     * Get a group's info
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   False or Group's info
     */
    function GetGroup($gid)
    {
        $this->CheckSession('Banner', 'ManageGroups');
        $group = $this->_Model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Get a list of groups
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $bid        banner ID
     * @param   int     $limit      limit
     * @param   int     $columns    columns
     * @return  mixed   False or Groups list
     */
    function GetGroups($gid = -1, $bid = -1, $limit = null, $columns = null)
    {
        $this->CheckSession('Banner', 'ManageGroups');
        $groups = $this->_Model->GetGroups($gid, $bid, $limit, $columns);
        if (Jaws_Error::IsError($groups)) {
            return false; //we need to handle errors on ajax
        }

        return $groups;
    }

    /**
     * Insert banners
     *
     * @access  public
     * @param   string  $title        banner title
     * @param   string  $url          url
     * @param   int     $gid          group ID
     * @param   string  $banner
     * @param   string  $template
     * @param   int     $views_limit
     * @param   int     $clicks_limit
     * @param   long    $start_time
     * @param   long    $stop_time
     * @param   int     $random
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function InsertBanner($title, $url, $gid, $banner, $template, $views_limit,
                          $clicks_limit, $start_time, $stop_time, $random, $published)
    {
        $this->CheckSession('Banner', 'ManageBanners');

        $request =& Jaws_Request::getInstance();
        $template = $request->get(4, 'post', false);
        $this->_Model->InsertBanner($title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update banners
     *
     * @access  public
     * @param   int     $bid          banner ID
     * @param   string  $title        banner title
     * @param   string  $url          url
     * @param   int     $gid          group ID
     * @param   string  $banner
     * @param   string  $template
     * @param   int     $views_limit
     * @param   int     $clicks_limit
     * @param   long    $start_time
     * @param   long    $stop_time
     * @param   bool    $random
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateBanner($bid, $title, $url, $gid, $banner, $template, $views_limit,
                          $clicks_limit, $start_time, $stop_time, $random, $published)
    {
        $this->CheckSession('Banner', 'ManageBanners');

        $request =& Jaws_Request::getInstance();
        $template = $request->get(5, 'post', false);
        $this->_Model->UpdateBanner($bid, $title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert groups
     *
     * @access  public
     * @param   string  $title       group title
     * @param   int     $limit_count
     * @param   bool    $show_title
     * @param   bool    $show_type
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $limit_count, $show_title, $show_type, $published)
    {
        $this->CheckSession('Banner', 'ManageGroups');
        $this->_Model->InsertGroup($title, $limit_count, $show_title, $show_type, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update groups
     *
     * @access  public
     * @param   int     $gid         group ID
     * @param   string  $title       group title
     * @param   int     $limit_count
     * @param   bool    $show_title
     * @param   bool    $show_type
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published)
    {
        $this->CheckSession('Banner', 'ManageGroups');
        $this->_Model->UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Add a group of banner (by they ids) to a certain group
     *
     * @access  public
     * @param   int     $gid     Group's ID
     * @param   array   $banners array with banner id
     * @return  array   Response array (notice or error)
     */
    function AddBannersToGroup($gid, $banners)
    {
        $this->CheckSession('Banner', 'BannersGrouping');
        $res = $this->_Model->AddBannersToGroup($gid, $banners);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an banner
     *
     * @access  public
     * @param   int     $bid     banner ID
     * @return  array   Response array (notice or error)
     */
    function DeleteBanner($bid)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $this->_Model->DeleteBanner($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Reset banner's views counter
     *
     * @access  public
     * @param   int     $bid   banner ID
     * @return  array   Response array (notice or error)
     */
    function ResetViews($bid)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $this->_Model->ResetViews($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Reset banner's clicks counter
     *
     * @access  public
     * @param   int     $bid   banner ID
     * @return  array   Response array (notice or error)
     */
    function ResetClicks($bid)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $this->_Model->ResetClicks($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @param   int     $gid   group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->CheckSession('Banner', 'ManageGroups');
        $this->_Model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template of bannerForm
     */
    function GetGroupUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Banner', 'AdminHTML');
        return $gadget->EditGroupUI();
    }

    /**
     * Get the banners-group form
     *
     * @access  public
     * @return  string    group banner UI template
     */
    function GetGroupBannersUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Banner', 'AdminHTML');
        return $gadget->GetGroupBannersUI();
    }

    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $name
     * @param   int     $offset
     * @param   int     $gid    group id
     * @return  array   data array
     */
    function getBannersDataGrid($name, $offset, $gid = -1)
    {
        $this->CheckSession('Banner', 'ViewReports');
        $gadget = $GLOBALS['app']->LoadGadget('Banner', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return ($name == 'banners_datagrid')?
                $gadget->GetBanners($gid, $offset) :
                $gadget->GetReportBanners($gid, $offset);
    }

    /**
     * Get count of banners
     *
     * @access  public
     * @param   int     $gid   Banner's group ID
     * @return  mixed   False or Banners count and false on error
     */
    function GetBannersCount($gid = -1)
    {
        $this->CheckSession('Banner', 'ManageBanners');
        $res = $this->_Model->GetBannersCount($gid);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

}