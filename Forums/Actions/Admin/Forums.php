<?php
/**
 * Forums Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Admin_Forums extends ForumsAdminHTML
{
    /**
     * Show forums administration interface
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function Forums()
    {
        $this->CheckPermission('ManageForums');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('AdminForums.html');
        $tpl->SetBlock('forums');

        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Groups');
        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $groups = $gModel->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('forums/group');
            $tpl->SetVariable('mg_id', 'group_'.$group['id']);
            $tpl->SetVariable('icon', 'gadgets/Forum/images/menu-group.png');
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('js_edit_func', "editGroup({$group['id']})");
            $tpl->SetVariable('add_title', _t('FORUM_ADD_FORUM'));
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->SetVariable('js_add_func', "addForum({$group['id']}, 0)");
            $forums = $fModel->GetForums($group['id']);
            if (!Jaws_Error::IsError($forums)) {
                foreach ($forums as $forum) {
                    $tpl->SetBlock('forums/group/forum');
                    $tpl->SetVariable('fid', 'forum_'.$forum['id']);
                    $tpl->SetVariable('icon', 'gadgets/Forum/images/menu-item.png');
                    $tpl->SetVariable('title', $forum['title']);
                    $tpl->SetVariable('js_edit_func', "editForum(this, {$forum['id']})");
                    $tpl->ParseBlock('forums/group/forum');
                }
            }
            $tpl->ParseBlock('forums/group');
        }

        $add_btn =& Piwi::CreateWidget('Button','btn_add', _t('FORUM_ADD_GROUP'), STOCK_NEW);
        $add_btn->AddEvent(ON_CLICK, 'javascript: addGroup();');
        $tpl->SetVariable('add', $add_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript: saveForums();');
        $tpl->SetVariable('save', $save_btn->Get());

        $del_btn =& Piwi::CreateWidget('Button','btn_del', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $del_btn->SetStyle('display: none;');
        $del_btn->AddEvent(ON_CLICK, 'javascript: delForums();');
        $tpl->SetVariable('del', $del_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $tpl->SetVariable('forum_tree_title', _t('FORUM_TREE_TITLE'));
        $tpl->SetVariable('addGroupTitle',   _t('FORUM_ADD_GROUP'));
        $tpl->SetVariable('editGroupTitle',  _t('FORUM_EDIT_GROUP'));
        $tpl->SetVariable('addForumTitle',   _t('FORUM_ADD_FORUM'));
        $tpl->SetVariable('editForumTitle',  _t('FORUM_EDIT_FORUM'));
        $tpl->SetVariable('delForumTitle',   _t('FORUM_DELETE_FORUM'));
        $tpl->SetVariable('forumImageSrc',    'gadgets/Forum/images/menu-item.png');
        $tpl->SetVariable('incompleteFields',   _t('FORUM_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmDeleteForum', _t('FORUM_CONFIRM_DELETE_GROUP'));

        $tpl->ParseBlock('forums');
        return $tpl->Get();
    }

}