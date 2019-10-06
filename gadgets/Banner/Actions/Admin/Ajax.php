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
        @list($bid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Banners');
        $banner = $model->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            return false; //we need to handle errors on ajax
        }

        if (isset($banner['id'])) {
            $objDate = Jaws_Date::getInstance();
            if (!empty($banner['start_time'])) {
                $banner['start_time'] = $objDate->Format($banner['start_time'], 'Y/m/d');
            }
            if (!empty($banner['stop_time'])) {
                $banner['stop_time'] = $objDate->Format($banner['stop_time'], 'Y/m/d');
            }
        }

        // revert filtered URL
        $banner['url'] = Jaws_XSS::defilterURL($banner['url']);
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
        @list($bid, $gid, $domain) = $this->gadget->request->fetchAll('post');
        $bid = empty($bid)? -1 : $bid;
        $gid = empty($gid)? -1 : $gid;
        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $domain = 0;
        }
        $model = $this->gadget->model->load('Banners');
        $res = $model->GetBanners($bid, $gid, $domain);
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
        @list($gid) = $this->gadget->request->fetchAll('post');
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
        @list($gid, $bid, $limit, $columns) = $this->gadget->request->fetchAll('post');
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
        @list($domain, $title, $url, $gid, $banner, $template, $views_limit,
            $clicks_limit, $start_time, $stop_time, $random, $published
        ) = $this->gadget->request->fetchAll('post');
        $template = $this->gadget->request->fetch(5, 'post', 'strip_crlf');
        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $domain = 0;
        }

        // parse & encode given url
        $url = Jaws_XSS::filterURL($url);

        $model = $this->gadget->model->loadAdmin('Banners');
        $model->InsertBanner($domain, $title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $this->app->session->PopLastResponse();
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
        @list($bid, $domain, $title, $url, $gid, $banner, $template, $views_limit,
            $clicks_limit, $start_time, $stop_time, $random, $published
        ) = $this->gadget->request->fetchAll('post');
        $template = $this->gadget->request->fetch(6, 'post', 'strip_crlf');
        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $domain = 0;
        }

        // parse & encode given url
        $url = Jaws_XSS::filterURL($url);

        $model = $this->gadget->model->loadAdmin('Banners');
        $model->UpdateBanner($bid, $domain, $title, $url, $gid, $banner, $template, $views_limit,
                                    $clicks_limit, $start_time, $stop_time, $random, $published);

        return $this->app->session->PopLastResponse();
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
        @list($title, $limit_count, $show_title, $show_type, $published) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->InsertGroup($title, $limit_count, $show_title, $show_type, $published);

        return $this->app->session->PopLastResponse();
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
        @list($gid, $title, $limit_count, $show_title, $show_type, $published) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published);

        return $this->app->session->PopLastResponse();
    }

    /**
     * Add a group of banner (by they ids) to a certain group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddBannersToGroup()
    {
        $this->gadget->CheckPermission('BannersGrouping');
        @list($gid, $banners) = $this->gadget->request->fetch(array('0', '1:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->AddBannersToGroup($gid, $banners);
        return $this->app->session->PopLastResponse();
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
        @list($bid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Banners');
        $model->DeleteBanner($bid);

        return $this->app->session->PopLastResponse();
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
        @list($bid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Reports');
        $model->ResetViews($bid);

        return $this->app->session->PopLastResponse();
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
        @list($bid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Reports');
        $model->ResetClicks($bid);

        return $this->app->session->PopLastResponse();
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
        @list($gid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->DeleteGroup($gid);

        return $this->app->session->PopLastResponse();
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
        @list($name, $offset, $gid, $domain) = $this->gadget->request->fetchAll('post');
        $gid = empty($gid)? -1 : $gid;
        if (!is_numeric($offset)) {
            $offset = null;
        }

        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $domain = 0;
        }

        if ($name == 'banners_datagrid') {
            $gadget = $this->gadget->action->loadAdmin('Banners');
            $dataGrid = $gadget->GetBanners($gid, $domain, $offset);
        } else {
            $gadget = $this->gadget->action->loadAdmin('Reports');
            $dataGrid = $gadget->GetReportBanners($gid, $domain, $offset);
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
        @list($gid, $domain) = $this->gadget->request->fetchAll('post');
        $gid = empty($gid)? -1 : $gid;
        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $domain = 0;
        }

        $model = $this->gadget->model->loadAdmin('Banners');
        $res = $model->GetBannersCount($gid, $domain);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

}