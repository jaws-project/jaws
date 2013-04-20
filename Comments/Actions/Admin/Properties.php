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

        //Allow duplicate
        $allowDuplicate =& Piwi::CreateWidget('Combo', 'allow_duplicate');
        $allowDuplicate->AddOption(_t('GLOBAL_YES'), 'yes');
        $allowDuplicate->AddOption(_t('GLOBAL_NO'), 'no');
        $allowDuplicate->SetDefault($this->gadget->registry->get('allow_duplicate'));
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