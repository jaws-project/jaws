<?php
/**
 * Components Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Admin_ACL extends Jaws_Gadget_Action
{
    /**
     * Builds ACL UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ACLUI()
    {
        $tpl = $this->gadget->template->loadAdmin('ACL.html');
        $tpl->SetBlock('acl');

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:saveACL();');
        $tpl->SetVariable('save', $button->Get());

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_RESET'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'componentACL(true);');
        $tpl->SetVariable('reset', $button->Get());

        $tpl->ParseBlock('acl');
        return $tpl->Get();
    }

}