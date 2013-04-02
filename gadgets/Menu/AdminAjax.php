<?php
/**
 * Menu AJAX API
 *
 * @category   Ajax
 * @package    Menu
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Menu_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Menu_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Get all menus and groups data
     *
     * @access  public
     * @return  mixed   Data array or False on error
     */
    function GetMenusTrees()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML');
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
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML');
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
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML');
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
        $groupInfo = $this->_Model->GetGroups($gid);
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
        $menuInfo = $this->_Model->GetMenu($mid);
        if (Jaws_Error::IsError($menuInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $menuInfo;
    }

    /**
     * Insert group
     *
     * @access  public
     * @param   string  $title          menu title
     * @param   string  $title_view
     * @param   bool    $visible        is visible
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $title_view, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->_Model->InsertGroup($title, $title_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert menu
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid            group ID
     * @param   string  $type
     * @param   string  $title
     * @param   string  $url
     * @param   string  $url_target
     * @param   string  $rank
     * @param   bool    $visible        is visible
     * @param   string  $image
     * @return  array   Response array (notice or error)
     */
    function InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $this->_Model->InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update group
     *
     * @access  public
     * @param   int     $gid            group ID
     * @param   string  $title
     * @param   string  $title_view
     * @param   bool    $visible        is visible
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $title_view, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->_Model->UpdateGroup($gid, $title, $title_view, $visible);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update menu
     *
     * @access  public
     * @param   int     $mid            menu ID
     * @param   int     $pid
     * @param   int     $gid            group ID
     * @param   string  $type
     * @param   string  $title
     * @param   string  $url
     * @param   string  $url_target
     * @param   string  $rank
     * @param   bool    $visible        is visible
     * @param   string  $image
     * @return  array   Response array (notice or error)
     */
    function UpdateMenu($mid, $pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image)
    {
        $this->gadget->CheckPermission('ManageMenus');
        $this->_Model->UpdateMenu($mid, $pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image);

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
        $this->_Model->DeleteGroup($gid);

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
        $result = $this->_Model->DeleteMenu($mid);
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
        $this->_Model->GetParentMenus(0, $gid, $mid, $result);

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
        $this->_Model->MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_rank, $old_rank);

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