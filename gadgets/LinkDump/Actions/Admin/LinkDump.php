<?php
/**
 * LinkDump Admin Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Admin_LinkDump extends Jaws_Gadget_Action
{
    /**
     * Administration section
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function LinkDump()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('LinkDump.html');
        $tpl->SetBlock('linkdump');

        $tpl->SetBlock('linkdump/links_base');
        $gadget = $this->gadget->action->loadAdmin('Groups');
        $tpl->SetVariable('links_tree', $gadget->GetGroupsList());
        $add_btn =& Piwi::CreateWidget('Button','btn_add', _t('LINKDUMP_GROUPS_ADD'), STOCK_NEW);
        $add_btn->AddEvent(ON_CLICK, 'javascript:addGroup();');
        $tpl->SetVariable('add', $add_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript:saveLink();');
        $tpl->SetVariable('save', $save_btn->Get());

        $del_btn =& Piwi::CreateWidget('Button','btn_del', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $del_btn->SetStyle('display: none;');
        $del_btn->AddEvent(ON_CLICK, 'javascript:delLinks();');
        $tpl->SetVariable('del', $del_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $tpl->SetVariable('links_tree_image', 'gadgets/LinkDump/Resources/images/logo.mini.png');
        $tpl->SetVariable('links_tree_title', _t('LINKDUMP_LINKS_TITLE'));

        $this->gadget->define('max_limit_count', $this->gadget->registry->fetch('max_limit_count'));
        $this->gadget->define('addLinkTitle',     _t('LINKDUMP_LINKS_ADD'));
        $this->gadget->define('editLinkTitle',    _t('LINKDUMP_LINKS_EDIT'));
        $this->gadget->define('addGroupTitle',    _t('LINKDUMP_GROUPS_ADD'));
        $this->gadget->define('editGroupTitle',   _t('LINKDUMP_GROUPS_EDIT'));
        $this->gadget->define('linkImageSrc',     'gadgets/LinkDump/Resources/images/logo.mini.png');
        $this->gadget->define('linksListCloseImageSrc', STOCK_REMOVE);
        $this->gadget->define('linksListOpenImageSrc',  STOCK_ADD);
        $this->gadget->define('incompleteFields',   _t('LINKDUMP_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmGroupDelete', _t('LINKDUMP_GROUPS_DELETE_CONFIRM'));
        $this->gadget->define('confirmLinkDelete',  _t('LINKDUMP_LINKS_DELETE_CONFIRM'));
        $this->gadget->define('noLinkExists',       _t('LINKDUMP_LINKS_NOEXISTS'));

        $tpl->ParseBlock('linkdump/links_base');
        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

}