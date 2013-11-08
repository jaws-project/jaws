<?php
/**
 * Banner Admin Model
 *
 * @category   GadgetModel
 * @package    Banner
 */
class Banner_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
     * Insert a group
     *
     * @access  public
     * @param    string  $title
     * @param    int     $limit_count
     * @param    bool    $show_title
     * @param    bool    $show_type
     * @param    bool    $published
     * @return   bool    True on Success, False on Failure
     */
    function InsertGroup($title, $limit_count, $show_title, $show_type, $published)
    {
        $bgData['title']       = $title;
        $bgData['limit_count'] = (empty($limit_count)  || !is_numeric($limit_count))? 0: $limit_count;
        $bgData['show_title']  = (bool)$show_title;
        $bgData['show_type']   = (int)$show_type;
        $bgData['published']   = (bool)$published;

        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        $res = $bgroupsTable->insert($bgData)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_CREATED', $title), RESPONSE_NOTICE);

        return true;
    }

    /**
     * Update a group
     *
     * @access  public
     * @param    int     $gid         group ID
     * @param    string  $title       group title
     * @param    int     $limit_count
     * @param    bool    $show_title
     * @param    bool    $show_type
     * @param    bool    $published
     * @return   bool    True on Success, False on Failure
     */
    function UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published)
    {
        $bgData['title']       = $title;
        $bgData['limit_count'] = (empty($limit_count)  || !is_numeric($limit_count))? 0: $limit_count;
        $bgData['show_title']  = (bool)$show_title;
        $bgData['show_type']   = (int)$show_type;
        $bgData['published']   = (bool)$published;

        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        $res = $bgroupsTable->update($bgData)->where('id', $gid)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_UPDATED', $title), RESPONSE_NOTICE);

        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $gid     The banner that will be deleted
     * @return  bool    True if query was successful and False on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_ERROR_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }

        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $model = $this->gadget->model->loadAdmin('Banners');
        $model->UpdateBannerGroup(-1, $gid, 0, 0);

        $bannersTable = Jaws_ORM::getInstance()->table('banners_groups');
        $res = $bannersTable->delete()->where('id', $gid)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}