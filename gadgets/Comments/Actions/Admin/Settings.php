<?php
/**
 * Comments Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Settings extends Comments_Actions_Admin_Default
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('Settings');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('Settings');

        // comments site wide
        $comments =& Piwi::CreateWidget('Combo', 'allow_comments');
        $comments->setID('allow_comments');
        $comments->AddOption(_t('GLOBAL_YES'), 'true');
        $comments->AddOption(_t('COMMENTS_ALLOW_COMMENTS_RESTRICTED'), 'restricted');
        $comments->AddOption(_t('GLOBAL_NO'), 'false');
        $comments->SetDefault($this->gadget->registry->fetch('allow_comments'));
        $tpl->SetVariable('lbl_allow_comments', _t('COMMENTS_ALLOW_COMMENTS'));
        $tpl->SetVariable('allow_comments', $comments->Get());

        // comment default status
        $status =& Piwi::CreateWidget('Combo', 'default_comment_status');
        $status->setID('default_comment_status');
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), 1);
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), 2);
        $status->SetDefault($this->gadget->registry->fetch('default_comment_status'));
        $tpl->SetVariable('lbl_default_comment_status', _t('COMMENTS_STATUS_DEFAULT'));
        $tpl->SetVariable('default_comment_status', $status->Get());

        //Order by option
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('GLOBAL_CREATETIME'). ' &uarr;', '1');
        $orderType->AddOption(_t('GLOBAL_CREATETIME'). ' &darr;', '2');
        $orderType->SetDefault($this->gadget->registry->fetch('order_type'));
        $tpl->SetVariable('lbl_order_type', _t('GLOBAL_ORDERBY'));
        $tpl->SetVariable('order_type', $orderType->Get());

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:SaveSettings();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->SetVariable('menubar', $this->MenuBar('Settings'));

        $tpl->ParseBlock('Settings');

        return $tpl->Get();
    }

}