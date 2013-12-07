<?php
/**
 * Tags Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Tags
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tags_Actions_Admin_Default extends Jaws_Gadget_Action
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
        $actions = array('Tags', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Tags';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Tags',
                            _t('TAGS_TITLE'),
                            BASE_SCRIPT . '?gadget=Tags&amp;action=Tags',
                            STOCK_NEW);
        if ($this->gadget->GetPermission('ManageProperties')) {
            $menubar->AddOption('Properties',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Tags&amp;action=Properties',
                                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

}