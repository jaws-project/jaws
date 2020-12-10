<?php
/**
 * Menu Gadget
 *
 * @category    GadgetModel
 * @package     Menu
 */
class Menu_Model_Admin_Group extends Jaws_Gadget_Model
{
    /**
     * Inserts a new group
     *
     * @access  public
     * @param   string  $title
     * @param   int     $home
     * @param   bool    $title_view
     * @param   int     $view_type
     * @param   bool    $published      Published status
     * @return  bool    True on success or False on failure
     */
    function InsertGroup($title, $home, $title_view, $view_type, $published)
    {
        $mgroupsTable = Jaws_ORM::getInstance()->table('menus_groups');
        $gc = $mgroupsTable->select('count(id):integer')->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $this->gadget->session->push(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $gData['title']      = $title;
        $gData['home']       = (int)$home;
        $gData['title_view'] = (int)$title_view;
        $gData['view_type']  = (int)$view_type;
        $gData['published']  = (bool)$published;
        $gid = $mgroupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($gid)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $this->gadget->acl->insert('GroupAccess', $gid, true);
        $this->gadget->session->push(_t('MENU_NOTICE_GROUP_CREATED'), RESPONSE_NOTICE, $gid);
        return true;
    }

    /**
     * Updates menu group
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   string  $title      Group title
     * @param   int     $home
     * @param   bool    $title_view
     * @param   int     $view_type
     * @param   bool    $published     Published status
     * @return  bool    True on success or False on failure
     */
    function UpdateGroup($gid, $title, $home, $title_view, $view_type, $published)
    {
        $mgroupsTable = Jaws_ORM::getInstance()->table('menus_groups');
        $mgroupsTable->select('count(id):integer')->where('id', $gid, '<>')->and()->where('title', $title);
        $gc = $mgroupsTable->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $this->gadget->session->push(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $gData['title']      = $title;
        $gData['home']       = (int)$home;
        $gData['title_view'] = (int)$title_view;
        $gData['view_type']  = (int)$view_type;
        $gData['published']  = (bool)$published;
        $res = $mgroupsTable->update($gData)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $this->gadget->session->push(_t('MENU_NOTICE_GROUP_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes menu group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $this->gadget->session->push(_t('MENU_ERROR_GROUP_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $model = $this->gadget->model->load('Group');
        $group = $model->GetGroups($gid);
        if (Jaws_Error::IsError($group)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $this->gadget->session->push(_t('MENU_ERROR_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $objORM = Jaws_ORM::getInstance();
        $res = $objORM->delete()->table('menus')->where('gid', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $res = $objORM->delete()->table('menus_groups')->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $this->gadget->acl->delete('GroupAccess', $gid);
        $this->gadget->session->push(_t('MENU_NOTICE_GROUP_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }

}