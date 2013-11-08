<?php
/**
 * LinkDump Gadget Admin
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
    * Insert a group
    * 
    * @access  public
    * @param    string  $title      group title
    * @param    string  $fast_url
    * @param    int     $limit_count
    * @param    string  $link_type
    * @param    string  $order_type
    * @return   bool    True Success and False on Failure
    */
    function InsertGroup($title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups');

        $gData['title']       = $title;
        $gData['fast_url']    = $fast_url;
        $gData['limit_count'] = $limit_count;
        $gData['link_type']   = $link_type;
        $gData['order_type']  = $order_type;

        $groupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $gid = $groupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($gid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_ADDED'), RESPONSE_NOTICE, $gid);
        return true;
    }

    /**
    * Update a group
    * 
    * @access  public
    * @param    int     $gid        group ID
    * @param    string  $title      group title
    * @param    string  $fast_url
    * @param    int     $limit_count
    * @param    string  $link_type
    * @param    string  $order_type
    * @return   bool    True on Success and False on Failure
    */
    function UpdateGroup($gid, $title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups', false);

        $gData['title']       = $title;
        $gData['fast_url']    = $fast_url;
        $gData['limit_count'] = $limit_count;
        $gData['link_type']   = $link_type;
        $gData['order_type']  = $order_type;

        $groupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $res = $groupsTable->update($gData)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $model = $this->gadget->model->loadAdmin('Links');
        $model->InvalidateFeed($gid);
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  bool    True if query was successful and false on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $links = $model->GetGroupLinks($gid);
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            foreach ($links as $link) {
                $res = $model->DeleteItemTags('LinkDump', 'link', $link['id']);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                    return false;
                }
            }
        }

        $objORM = Jaws_ORM::getInstance()->table('linkdump_links');
        $res = $objORM->delete()->where('gid', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $res = $objORM->delete()->table('linkdump_groups')->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }
}