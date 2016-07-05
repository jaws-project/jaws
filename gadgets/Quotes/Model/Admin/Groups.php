<?php
/**
 * Quotes Gadget
 *
 * @category    GadgetModel
 * @package     Quotes
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
     * Inserts a new group
     *
     * @access  public
     * @param   string  $title
     * @param   int     $view_mode
     * @param   int     $view_type
     * @param   bool    $show_title
     * @param   int     $limit_count
     * @param   bool    $random
     * @param   bool    $published
     * @return  bool    True on Success or False on failure
     */
    function InsertGroup($title, $view_mode, $view_type, $show_title, $limit_count, $random, $published)
    {
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $gc = $table->select('count(id)')->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $result = $table->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(
            _t('QUOTES_GROUPS_CREATED'),
            RESPONSE_NOTICE,
            array('id' => $result, 'title' => $title)
        );

        return true;
    }

    /**
     * Updates the group
     *
     * @access  public
     * @param   int     $gid         Group ID
     * @param   string  $title
     * @param   int     $view_mode
     * @param   int     $view_type
     * @param   bool    $show_title
     * @param   int     $limit_count
     * @param   bool    $random
     * @param   bool    $published
     * @return  bool    True on Success or False on failure
     */
    function UpdateGroup($gid, $title, $view_mode, $view_type, $show_title, $limit_count, $random, $published)
    {
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $gc = $table->select('count(id)')->where('title', $title)->and()->where('id', $gid, '!=')->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DUPLICATE_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $params['title']       = $title;
        $params['view_mode']   = $view_mode;
        $params['view_type']   = $view_type;
        $params['show_title']  = (bool)$show_title;
        $params['limit_count'] = ((empty($limit_count) || !is_numeric($limit_count))? 0 : $limit_count);
        $params['random']      = (bool)$random;
        $params['published']   = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $table->update($params)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_UPDATED', $gid), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  bool   True on Success or False on failure
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_ERROR_GROUP_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroups($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group[0]['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $model = $this->gadget->model->loadAdmin('Quotes');
        $model->UpdateQuoteGroup(-1, $gid, 0);
        $table = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $table->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('QUOTES_GROUPS_DELETED', $group[0]['title']), RESPONSE_NOTICE);

        return true;
    }
}