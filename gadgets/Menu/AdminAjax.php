<?php
/**
 * Menu AJAX API
 *
 * @category    Ajax
 * @package     Menu
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Menu_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Get all menus and groups data
     *
     * @access  public
     * @return  mixed   Data array or False on error
     */
    function GetMenusTrees()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML', 'Menu');
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
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML', 'Menu');
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
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML', 'Menu');
        return $gadget->GetMenuUI();
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  mixed   Group information array or False on error
     */
    function GetGroups($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('Menu', 'Model', 'Group');
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
     * @param   int     $mid    Menu ID
     * @return  mixed   Menu data array or False on error
     */
    function GetMenu($mid)
    {
        $model = $GLOBALS['app']->LoadGadget('Menu', 'Model', 'Menu');
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
     * @param   string  $title          Menu title
     * @param   string  $title_view
     * @param   bool    $published      Published status
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $title_view, $published)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Group');
        $model->InsertGroup($title, $title_view, (bool)$published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert menu
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid            Group ID
     * @param   string  $type
     * @param   string  $title
     * @param   string  $url
     * @param   string  $url_target
     * @param   string  $rank
     * @param   bool    $published      Published status
     * @param   string  $image
     * @return  array   Response array (notice or error)
     */
    function InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, $published, $image)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Menu');
        $model->InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, (bool)$published, $image);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update group
     *
     * @access  public
     * @param   int     $gid            Group ID
     * @param   string  $title
     * @param   string  $title_view
     * @param   bool    $published      Published status
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $title_view, $published)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Group');
        $model->UpdateGroup($gid, $title, $title_view, (bool)$published);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update menu
     *
     * @access  public
     * @param   int     $mid            Menu ID
     * @param   int     $pid
     * @param   int     $gid            Group ID
     * @param   string  $type
     * @param   string  $title
     * @param   string  $url
     * @param   string  $url_target
     * @param   string  $rank
     * @param   bool    $published      Published status
     * @param   string  $image
     * @return  array   Response array (notice or error)
     */
    function UpdateMenu($mid, $pid, $gid, $type, $title, $url, $url_target, $rank, $published, $image)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Menu');
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
     * @param   int     $gid   group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Group');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an menu
     *
     * @access  public
     * @param   int     $mid   menu ID
     * @return  array   Response array (notice or error)
     */
    function DeleteMenu($mid)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Menu');
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
     * @param   int     $gid    group ID
     * @param   int     $mid    Menu ID
     * @return  array   Menu data array
     */
    function GetParentMenus($gid, $mid)
    {
        $result[] = array('pid'=> 0,
                          'title'=>'\\');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Menu');
        $model->GetParentMenus(0, $gid, $mid, $result);

        return $result;
    }

    /**
     * function for change gid, pid and rank of menus
     *
     * @access  public
     * @param   int     $mid        menu ID
     * @param   int     $new_gid    new group id
     * @param   int     $old_gid    old group id
     * @param   int     $new_pid
     * @param   int     $old_pid
     * @param   string  $new_rank
     * @param   string  $old_rank
     * @return  array   Response array (notice or error)
     */
    function MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_rank, $old_rank)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'AdminModel', 'Menu');
        $model->MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_rank, $old_rank);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a list of URLs of a gadget
     *
     * @access  public
     * @param   string  $request  Gadget's name
     * @return  array   URLs array on success or empty array on failure
     */
    function GetPublicURList($request)
    {
        if ($request == 'url') {
            $urls[] = array('url'   => '',
                            'title' => _t('MENU_REFERENCES_FREE_LINK'));
            $urls[] = array('url'   => 'javascript:void(0);',
                            'title' => _t('MENU_REFERENCES_NO_LINK'));
            return $urls;
        } else {
            if (Jaws_Gadget::IsGadgetUpdated($request)) {
                $objGadget = $GLOBALS['app']->LoadGadget($request, 'Info');
                if (!Jaws_Error::IsError($objGadget)) {
                    $objHook = $objGadget->load('Hook')->load('Menu');
                    if (!Jaws_Error::IsError($objHook)) {
                        return $objHook->Execute();
                    }
                }
            }
        }

        return array();
    }

}