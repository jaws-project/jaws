<?php
/**
 * LinkDump Admin Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Admin_Groups extends Jaws_Gadget_Action
{
    /**
     * Providing a list of groups
     *
     * @access  public
     * @return  string XHTML Template content
     */
    function GetGroupsList()
    {
        $tpl = $this->gadget->loadAdminTemplate('LinkDump.html');
        $tpl->SetBlock('linkdump');

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('linkdump/link_group');
            $tpl->SetVariable('lg_id', 'group_'.$group['id']);
            $tpl->SetVariable('icon', STOCK_ADD);
            $tpl->SetVariable('js_list_func', "listLinks({$group['id']})");
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('js_edit_func', "editGroup({$group['id']})");
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->SetVariable('js_add_func', "addLink({$group['id']})");
            $tpl->SetVariable('add_title', _t('LINKDUMP_LINKS_ADD'));
            $tpl->ParseBlock('linkdump/link_group');
        }

        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetGroupUI()
    {
        $tpl = $this->gadget->loadAdminTemplate('LinkDump.html');
        $tpl->SetBlock('linkdump');
        $tpl->SetBlock('linkdump/GroupsUI');

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 200px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $btnDown =& Piwi::CreateWidget('Button','btn_down', '', STOCK_DOWN);
        $btnDown->AddEvent(ON_CLICK, 'javascript: downCount();');
        $tpl->SetVariable('btn_down', $btnDown->Get());

        $tpl->SetVariable('lbl_limit_count', _t('LINKDUMP_GROUPS_LIMIT_COUNT'));
        $limitCount =& Piwi::CreateWidget('Entry', 'limit_count', '10');
        $limitCount->SetSize(3);
        $tpl->SetVariable('limit_count', $limitCount->Get());

        $btnUp =& Piwi::CreateWidget('Button','btn_up', '', STOCK_UP);
        $btnUp->AddEvent(ON_CLICK, 'javascript: upCount();');
        $tpl->SetVariable('btn_up', $btnUp->Get());

        $linksType =& Piwi::CreateWidget('Combo', 'links_type');
        $linksType->AddOption(_t('LINKDUMP_GROUPS_LINKS_TYPE_NOLINK'),  0);
        $linksType->AddOption(_t('LINKDUMP_GROUPS_LINKS_TYPE_RAWLINK'), 1);
        $linksType->AddOption(_t('LINKDUMP_GROUPS_LINKS_TYPE_MAPPED'),  2);
        $linksType->SetDefault(1);
        $tpl->SetVariable('lbl_links_type', _t('LINKDUMP_GROUPS_LINKS_TYPE'));
        $tpl->SetVariable('links_type', $linksType->Get());

        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('LINKDUMP_GROUPS_ORDER_BY_RANK'),   0);
        $orderType->AddOption(_t('LINKDUMP_GROUPS_ORDER_BY_ID'),     1);
        $orderType->AddOption(_t('LINKDUMP_GROUPS_ORDER_BY_TITLE'),  2);
        $orderType->AddOption(_t('LINKDUMP_GROUPS_ORDER_BY_CLICKS'), 3);
        $orderType->SetDefault(0);
        $tpl->SetVariable('lbl_order_type', _t('LINKDUMP_GROUPS_ORDER_TYPE'));
        $tpl->SetVariable('order_type', $orderType->Get());

        $tpl->SetVariable('lbl_fast_url', _t('LINKDUMP_FASTURL'));
        $gfasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $gfasturl->SetStyle('direction: ltr; width: 200px;');
        $tpl->SetVariable('fast_url', $gfasturl->Get());

        $tpl->ParseBlock('linkdump/GroupsUI');
        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }
}