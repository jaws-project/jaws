<?php
/**
 * UrlMapper Core Gadget Admin
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string   $action_selected   Selected action
     * @return  string   XHTML template content
     */
    function MenuBar($action_selected)
    {
        $actions = array('Maps', 'Aliases', 'ErrorMaps','Properties');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'Maps';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Maps', _t('URLMAPPER_MAPS'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Maps', STOCK_DOCUMENTS);
        $menubar->AddOption('Aliases', _t('URLMAPPER_ALIASES'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Aliases', 'gadgets/UrlMapper/Resources/images/aliases.png');
        $menubar->AddOption('ErrorMaps', _t('URLMAPPER_ERRORMAPS'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=ErrorMaps', STOCK_DOCUMENTS);
        $menubar->AddOption('Properties', _t('GLOBAL_PROPERTIES'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Properties', STOCK_PREFERENCES);
        $menubar->Activate($action_selected);
        return $menubar->Get();
    }
}