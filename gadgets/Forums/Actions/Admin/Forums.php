<?php
/**
 * Forums Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Admin_Forums extends Jaws_Gadget_Action
{
    /**
     * Show forums administration interface
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function Forums()
    {
        $this->AjaxMe('script.js');
        // set default value of javascript variables
        $this->gadget->define('addGroupTitle',   $this::t('GROUP_NEW'));
        $this->gadget->define('editGroupTitle',  $this::t('GROUP_EDIT'));
        $this->gadget->define('addForumTitle',   $this::t('FORUM_NEW'));
        $this->gadget->define('editForumTitle',  $this::t('FORUM_EDIT'));
        $this->gadget->define('forumImageSrc',    'gadgets/Forums/Resources/images/menu-item.png');
        $this->gadget->define('incompleteFields',   $this::t('INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmForumDelete', $this::t('CONFIRM_DELETE_FORUM'));
        $this->gadget->define('confirmGroupDelete', $this::t('CONFIRM_DELETE_GROUP'));

        $tpl = $this->gadget->template->loadAdmin('Forums.html');
        $tpl->SetBlock('forums');

        $gModel = $this->gadget->model->load('Groups');
        $fModel = $this->gadget->model->load('Forums');
        $groups = $gModel->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('forums/group');
            $tpl->SetVariable('gid', $group['id']);
            $tpl->SetVariable('icon', 'gadgets/Forums/Resources/images/menu-group.png');
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('js_edit_func', "editGroup({$group['id']})");
            $tpl->SetVariable('add_title', $this::t('FORUM_NEW'));
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->SetVariable('js_add_func', "addForum({$group['id']})");
            $forums = $fModel->GetForums($group['id']);
            if (!Jaws_Error::IsError($forums)) {
                foreach ($forums as $forum) {
                    if(!$this->gadget->GetPermission('ForumManage', $forum['id'])) {
                        continue;
                    }
                    $tpl->SetBlock('forums/group/forum');
                    $tpl->SetVariable('fid', $forum['id']);
                    $tpl->SetVariable('icon', 'gadgets/Forums/Resources/images/menu-item.png');
                    $tpl->SetVariable('title', $forum['title']);
                    $tpl->SetVariable('js_edit_func', "editForum(this, {$forum['id']})");
                    $tpl->ParseBlock('forums/group/forum');
                }
            }
            $tpl->ParseBlock('forums/group');
        }

        $add_btn =& Piwi::CreateWidget('Button','btn_add', $this::t('GROUP_NEW'), STOCK_NEW);
        $add_btn->AddEvent(ON_CLICK, 'javascript:addGroup();');
        $tpl->SetVariable('add', $add_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript:saveForums();');
        $tpl->SetVariable('save', $save_btn->Get());

        $del_btn =& Piwi::CreateWidget('Button','btn_del', Jaws::t('DELETE'), STOCK_DELETE);
        $del_btn->SetStyle('display: none;');
        $del_btn->AddEvent(ON_CLICK, 'javascript:delForums();');
        $tpl->SetVariable('del', $del_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());
        $tpl->SetVariable('forum_tree_title', $this::t('TREE_TITLE'));

        $tpl->ParseBlock('forums');
        return $tpl->Get();
    }

}