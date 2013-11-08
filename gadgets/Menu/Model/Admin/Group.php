<?php
/**
 * Menu Gadget
 *
 * @category    GadgetModel
 * @package     Menu
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Menu_Model_Admin_Group extends Jaws_Gadget_Model
{
    /**
     * Inserts a new group
     *
     * @access  public
     * @param   string   $title
     * @param   string   $title_view
     * @param   bool     $published     Published status
     * @return  bool     True on success or False on failure
     */
    function InsertGroup($title, $title_view, $published)
    {
        $mgroupsTable = Jaws_ORM::getInstance()->table('menus_groups');
        $gc = $mgroupsTable->select('count(id):integer')->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $gData['title']      = $title;
        $gData['title_view'] = $title_view;
        $gData['published']  = (bool)$published;
        $gid = $mgroupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($gid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_CREATED'), RESPONSE_NOTICE, $gid);
        return true;
    }

    /**
     * Updates menu group
     *
     * @access  public
     * @param    int     $gid           Group ID
     * @param    string  $title         Group title
     * @param    string  $title_view
     * @param    bool    $published     Published status
     * @return   bool    True on success or False on failure
     */
    function UpdateGroup($gid, $title, $title_view, $published)
    {
        $mgroupsTable = Jaws_ORM::getInstance()->table('menus_groups');
        $mgroupsTable->select('count(id):integer')->where('id', $gid, '<>')->and()->where('title', $title);
        $gc = $mgroupsTable->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $gData['title']      = $title;
        $gData['title_view'] = $title_view;
        $gData['published']  = $published;
        $res = $mgroupsTable->update($gData)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_UPDATED'), RESPONSE_NOTICE);
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
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GROUP_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $model = $this->gadget->model->load('Group');
        $group = $model->GetGroups($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $objORM = Jaws_ORM::getInstance();
        $res = $objORM->delete()->table('menus')->where('gid', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $res = $objORM->delete()->table('menus_groups')->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }
}
