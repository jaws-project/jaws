<?php
/**
 * Comments Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
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
        $tpl = $this->gadget->loadAdminTemplate('Settings.html');
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

        //Allow duplicate
        $allowDuplicate =& Piwi::CreateWidget('Combo', 'allow_duplicate');
        $allowDuplicate->AddOption(_t('GLOBAL_YES'), 'yes');
        $allowDuplicate->AddOption(_t('GLOBAL_NO'), 'no');
        $allowDuplicate->SetDefault($this->gadget->registry->fetch('allow_duplicate'));
        $tpl->SetVariable('lbl_allow_duplicate', _t('COMMENTS_ANTISPAM_ALLOWDUPLICATE'));
        $tpl->SetVariable('allow_duplicate', $allowDuplicate->Get());

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:SaveSettings();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->SetVariable('menubar', $this->MenuBar('Settings'));

        $tpl->ParseBlock('Settings');

        return $tpl->Get();
    }

}