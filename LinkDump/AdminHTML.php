<?php
/**
 * LinkDump Admin Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDumpAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Administration section
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('AdminLinkDump.html');
        $tpl->SetBlock('linkdump');

        $tpl->SetBlock('linkdump/links_base');

        $tpl->SetVariable('links_tree', $this->GetGroupsList());
        $add_btn =& Piwi::CreateWidget('Button','btn_add', _t('LINKDUMP_GROUPS_ADD'), STOCK_NEW);
        $add_btn->AddEvent(ON_CLICK, 'javascript: addGroup();');
        $tpl->SetVariable('add', $add_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript: saveLink();');
        $tpl->SetVariable('save', $save_btn->Get());

        $del_btn =& Piwi::CreateWidget('Button','btn_del', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $del_btn->SetStyle('display: none;');
        $del_btn->AddEvent(ON_CLICK, 'javascript: delLinks();');
        $tpl->SetVariable('del', $del_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $tpl->SetVariable('links_tree_image', 'gadgets/LinkDump/images/logo.mini.png');
        $tpl->SetVariable('links_tree_title', _t('LINKDUMP_LINKS_TITLE'));
        $tpl->SetVariable('addLinkTitle',     _t('LINKDUMP_LINKS_ADD'));
        $tpl->SetVariable('editLinkTitle',    _t('LINKDUMP_LINKS_EDIT'));
        $tpl->SetVariable('delLinkTitle',     _t('LINKDUMP_LINKS_DELETE'));
        $tpl->SetVariable('addGroupTitle',    _t('LINKDUMP_GROUPS_ADD'));
        $tpl->SetVariable('editGroupTitle',   _t('LINKDUMP_GROUPS_EDIT'));
        $tpl->SetVariable('delGroupTitle',    _t('LINKDUMP_GROUPS_DELETE'));
        $tpl->SetVariable('linkImageSrc',     'gadgets/LinkDump/images/logo.mini.png');
        $tpl->SetVariable('linksListOpenImageSrc',  STOCK_ADD);
        $tpl->SetVariable('linksListCloseImageSrc', STOCK_REMOVE);
        $tpl->SetVariable('noLinkExists',       _t('LINKDUMP_LINKS_NOEXISTS'));
        $tpl->SetVariable('incompleteFields',   _t('LINKDUMP_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmDeleteGroup', _t('LINKDUMP_GROUPS_DELETE_CONFIRM'));
        $tpl->SetVariable('confirmDeleteLink',  _t('LINKDUMP_LINKS_DELETE_CONFIRM'));
        $tpl->SetVariable('max_limit_count', $GLOBALS['app']->Registry->Get('/gadgets/LinkDump/max_limit_count'));

        $tpl->ParseBlock('linkdump/links_base');
        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

    /**
     * Providing a list of groups
     *
     * @access  public
     * @return  string XHTML Template content
     */
    function GetGroupsList()
    {
        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('AdminLinkDump.html');
        $tpl->SetBlock('linkdump');

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel');
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
     * Links List Action
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  string  XHTML template content
     */
    function GetLinksList($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel');
        $links = $model->GetGroupLinks($gid);
        if (Jaws_Error::IsError($links) || empty($links)) {
            return '';
        }

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('AdminLinkDump.html');
        $tpl->SetBlock('linkdump');

        foreach ($links as $link) {
            $tpl->SetBlock('linkdump/link_list');
            $tpl->SetVariable('lid', 'link_'.$link['id']);
            $tpl->SetVariable('icon', 'gadgets/LinkDump/images/logo.mini.png');
            $tpl->SetVariable('title', $link['title']);
            $tpl->SetVariable('js_edit_func', "editLink(this, {$link['id']})");
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->ParseBlock('linkdump/link_list');
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
        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('AdminLinkDump.html');
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

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetLinkUI()
    {
        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('AdminLinkDump.html');
        $tpl->SetBlock('linkdump');
        $tpl->SetBlock('linkdump/LinksUI');

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel');
        $groups = $model->GetGroups();
        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $groupCombo->setStyle('width: 256px;');
        foreach ($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $groupCombo->AddEvent(ON_CHANGE, 'setRanksCombo(this.value);');

        $tpl->SetVariable('lbl_gid', _t('LINKDUMP_GROUPS_GROUP'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlEntry->SetStyle('direction: ltr;width: 256px;');
        $tpl->SetVariable('url', $urlEntry->Get());

        $tpl->SetVariable('lbl_fast_url', _t('LINKDUMP_FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fasturl->SetStyle('direction: ltr; width: 256px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        $linkdesc =& Piwi::CreateWidget('TextArea', 'description', '');
        $linkdesc->SetRows(4);
        $linkdesc->SetStyle('width: 256px;');
        $tpl->SetVariable('desc', $linkdesc->Get());
        $tpl->SetVariable('lbl_desc', _t('GLOBAL_DESCRIPTION'));

        $rank =& Piwi::CreateWidget('Combo', 'rank');
        $rank->SetID('rank');
        $rank->setStyle('width: 128px;');
        $tpl->SetVariable('lbl_rank', _t('LINKDUMP_RANK'));
        $tpl->SetVariable('rank', $rank->Get());

        $tpl->SetVariable('lbl_tag', _t('LINKDUMP_LINKS_TAGS'));
        $linktags  =& Piwi::CreateWidget('Entry', 'tags', '');
        $linktags->SetStyle('direction: ltr; width: 256px;');
        $tpl->SetVariable('tag', $linktags->Get());

        $tpl->SetVariable('lbl_clicks', _t('LINKDUMP_LINKS_CLICKS'));
        $linkclicks  =& Piwi::CreateWidget('Entry', 'clicks', '');
        $linkclicks->SetEnabled(false);
        $linkclicks->SetStyle('direction: ltr; width: 128px;');
        $tpl->SetVariable('clicks', $linkclicks->Get());

        $tpl->ParseBlock('linkdump/LinksUI');
        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

}