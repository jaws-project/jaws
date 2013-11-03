<?php
/**
 * Forums Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Admin_Forum extends Jaws_Gadget_Action
{
    /**
     * Show a form to edit a given forum
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetForumUI()
    {
        $this->gadget->CheckPermission('default');
        $tpl = $this->gadget->loadAdminTemplate('ForumUI.html');
        $tpl->SetBlock('ForumUI');

        $gModel = $this->gadget->loadModel('Groups');
        $groups = $gModel->GetGroups();
        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        foreach ($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('FORUMS_GROUP'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetID('title');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $description =& Piwi::CreateWidget('TextArea', 'description', '');
        $description->SetID('description');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $description->Get());

        $fasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fasturl->SetID('fast_url');
        $tpl->SetVariable('lbl_fast_url', _t('FORUMS_FASTURL'));
        $tpl->SetVariable('fast_url', $fasturl->Get());

        $order =& Piwi::CreateWidget('Combo', 'order');
        $order->SetID('order');
        $tpl->SetVariable('lbl_order', _t('FORUMS_ORDER'));
        $tpl->SetVariable('order', $order->Get());

        $locked =& Piwi::CreateWidget('Combo', 'locked');
        $locked->SetID('locked');
        $locked->AddOption(_t('GLOBAL_NO'),  0);
        $locked->AddOption(_t('GLOBAL_YES'), 1);
        $locked->SetDefault(0);
        $tpl->SetVariable('lbl_locked', _t('FORUMS_LOCKED'));
        $tpl->SetVariable('locked', $locked->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->AddOption(_t('GLOBAL_NO'),  0);
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->SetDefault(1);
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('ForumUI');
        return $tpl->Get();
    }

}