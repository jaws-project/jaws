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
class Comments_Actions_Admin_Properties extends Comments_AdminHTML
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->gadget->CheckPermission('ManageProperties');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Comments/templates/');
        $tpl->Load('Admin/Properties.html');
        $tpl->SetBlock('Properties');

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
        $save->AddEvent(ON_CLICK, 'javascript: saveProperties();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $tpl->ParseBlock('Properties');

        return $tpl->Get();
    }

}