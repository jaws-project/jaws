<?php
/**
 * Menu AJAX API
 *
 * @category    Ajax
 * @package     Menu
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Menu_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get all menus and groups data
     *
     * @access  public
     * @return  mixed   Data array or False on error
     */
    function GetMenusTrees()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        $data = $gadget->GetMenusTrees();
        unset($gadget);
        if (Jaws_Error::IsError($data)) {
            return false;
        }
        return $data;
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template of groupForm
     */
    function GetGroupUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        return $gadget->GetGroupUI();
    }

    /**
     * Returns the menu form
     *
     * @access  public
     * @return  string  XHTML template of groupForm
     */
    function GetMenuUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        return $gadget->GetMenuUI();
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @return  mixed   Group information array or False on error
     */
    function GetGroups()
    {
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Group');
        $groupInfo = $model->GetGroups($gid);
        if (Jaws_Error::IsError($groupInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $groupInfo;
    }

    /**
     * Get menu data
     *
     * @access  public
     * @return  mixed   Menu data array or False on error
     */
    function GetMenu()
    {
        @list($mid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Menu');
        $menuInfo = $model->GetMenu($mid);
        if (Jaws_Error::IsError($menuInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $menuInfo;
    }

    /**
     * Insert group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $title_view, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->InsertGroup($title, $title_view, (bool)$published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($pid, $gid, $type, $title, $url, $url_target,
            $rank, $published, $image
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, (bool)$published, $image);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid, $title, $title_view, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->UpdateGroup($gid, $title, $title_view, (bool)$published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid, $pid, $gid, $type, $title, $url, $url_target,
            $rank, $published, $image
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->UpdateMenu(
            $mid, $pid, $gid, $type, $title,
            $url, $url_target, $rank, (bool)$published, $image
        );

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
        $model = $this->gadget->model->loadAdmin('Group');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $result = $model->DeleteMenu($mid);
        if ($result) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_MENU_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get menu data
     *
     * @access  public
     * @return  array   Menu data array
     */
    function GetParentMenus()
    {
        @list($gid, $mid) = jaws()->request->fetchAll('post');
        $result[] = array('pid'=> 0,
                          'title'=>'\\');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->GetParentMenus(0, $gid, $mid, $result);

        return $result;
    }

    /**
     * function for change gid, pid and rank of menus
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MoveMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid, $new_gid, $old_gid, $new_pid, $old_pid,
            $new_rank, $old_rank
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_rank, $old_rank);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a list of URLs of a gadget
     *
     * @access  public
     * @return  array   URLs array on success or empty array on failure
     */
    function GetPublicURList()
    {
        @list($request) = jaws()->request->fetchAll('post');
        if ($request == 'url') {
            $urls[] = array('url'   => '',
                            'title' => _t('MENU_REFERENCES_FREE_LINK'));
            $urls[] = array('url'   => 'javascript:void(0);',
                            'title' => _t('MENU_REFERENCES_NO_LINK'));
            return $urls;
        } else {
            if (Jaws_Gadget::IsGadgetUpdated($request)) {
                $objGadget = Jaws_Gadget::getInstance($request);
                if (!Jaws_Error::IsError($objGadget)) {
                    $objHook = $objGadget->hook->load('Menu');
                    if (!Jaws_Error::IsError($objHook)) {
                        return $objHook->Execute();
                    }
                }
            }
        }

        return array();
    }

}