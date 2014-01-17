<?php
/**
 * Banner AJAX API
 *
 * @category   Ajax
 * @package    Banner
 */
class Banner_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get a banner info
     *
     * @access  public
     * @return  mixed   False or Banner Info
     */
    function GetBanner()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Banners');
        $banner = $model->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($banner['id'])) {
            $objDate = Jaws_Date::getInstance();
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
     * @return  mixed   False or Banners list
     */
    function GetBanners()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid, $gid) = jaws()->request->fetchAll('post');
        $bid = empty($bid)? -1 : $bid;
        $gid = empty($gid)? -1 : $gid;
        $model = $this->gadget->model->load('Banners');
        $res = $model->GetBanners($bid, $gid);
        if (Jaws_Error::IsError($res)) {
            return false; //we need to handle errors on ajax
        }

        return $res;
    }

    /**
     * Get a group's info
     *
     * @access  public
     * @return  mixed   False or Group's info
     */
    function GetGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Get a list of groups
     *
     * @access  public
     * @return  mixed   False or Groups list
     */
    function GetGroups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $bid, $limit, $columns) = jaws()->request->fetchAll('post');
        $bid = empty($bid)? -1 : $bid;
        $gid = empty($gid)? -1 : $gid;
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups($gid, $bid, $limit, $columns);
        if (Jaws_Error::IsError($groups)) {
            return false; //we need to handle errors on ajax
        }

        return $groups;
    }

    /**
     * Insert banners
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertBanner()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($title, $url, $gid, $banner, $template, $views_limit,
            $clicks_limit, $start_time, $stop_time, $random, $published
        ) = jaws()->request->fetchAll('post');
        $template = jaws()->request->fetch(4, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->InsertBanner($title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update banners
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateBanner()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid, $title, $url, $gid, $banner, $template, $views_limit,
            $clicks_limit, $start_time, $stop_time, $random, $published
        ) = jaws()->request->fetchAll('post');
        $template = jaws()->request->fetch(5, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->UpdateBanner($bid, $title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert groups
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $limit_count, $show_title, $show_type, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->InsertGroup($title, $limit_count, $show_title, $show_type, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update groups
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $title, $limit_count, $show_title, $show_type, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Add a group of banner (by they ids) to a certain group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddBannersToGroup($gid, $banners)
    {
        $this->gadget->CheckPermission('BannersGrouping');
        @list($gid, $banners) = jaws()->request->fetch(array('0', '1:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->AddBannersToGroup($gid, $banners);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an banner
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteBanner()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->DeleteBanner($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Reset banner's views counter
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function ResetViews()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Reports');
        $model->ResetViews($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Reset banner's clicks counter
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function ResetClicks()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($bid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Reports');
        $model->ResetClicks($bid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->DeleteGroup($gid);

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
        $gadget = $this->gadget->action->loadAdmin('Groups');
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
        $gadget = $this->gadget->action->loadAdmin('Groups');
        return $gadget->GetGroupBannersUI();
    }

    /**
     * Creates and returns some data
     *
     * @access  public
     * @return  array   data array
     */
    function getBannersDataGrid()
    {
        $this->gadget->CheckPermission('ViewReports');
        @list($name, $offset, $gid) = jaws()->request->fetchAll('post');
        $gid = empty($gid)? -1 : $gid;
        if (!is_numeric($offset)) {
            $offset = null;
        }
        if ($name == 'banners_datagrid') {
            $gadget = $this->gadget->action->loadAdmin('Banners');
            $dataGrid = $gadget->GetBanners($gid, $offset);
        } else {
            $gadget = $this->gadget->action->loadAdmin('Reports');
            $dataGrid = $gadget->GetReportBanners($gid, $offset);
        }

        return $dataGrid;
    }

    /**
     * Get count of banners
     *
     * @access  public
     * @return  mixed   False or Banners count and false on error
     */
    function GetBannersCount()
    {
        $this->gadget->CheckPermission('ManageBanners');
        @list($gid) = jaws()->request->fetchAll('post');
        $gid = empty($gid)? -1 : $gid;
        $model = $this->gadget->model->loadAdmin('Banners');
        $res = $model->GetBannersCount($gid);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

}