<?php
/**
 * Logs Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Logs
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the users menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('Logs', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Logs';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Logs',
                            _t('LOGS_TITLE'),
                            BASE_SCRIPT . '?gadget=Logs&amp;action=Logs',
                            STOCK_NEW);
        if ($this->gadget->GetPermission('ManageSettings')) {
            $menubar->AddOption('Settings',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Logs&amp;action=Settings',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}