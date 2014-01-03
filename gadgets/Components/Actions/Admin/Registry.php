<?php
/**
 * Components Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Admin_Registry extends Jaws_Gadget_Action
{
    /**
     * Builds registry UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function RegistryUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Registry.html');
        $tpl->SetBlock('registry');

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $button->AddEvent(ON_CLICK, 'javascript:saveRegistry();');
        $tpl->SetVariable('save', $button->Get());

        $button =& Piwi::CreateWidget('Button', '', _t('GLOBAL_RESET'), STOCK_REFRESH);
        $button->AddEvent(ON_CLICK, 'componentRegistry(true);');
        $tpl->SetVariable('reset', $button->Get());

        $tpl->ParseBlock('registry');
        return $tpl->Get();
    }

}