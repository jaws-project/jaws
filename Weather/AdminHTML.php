<?php
/**
 * Weather Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Default action of the gadget
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageRegions')) {
            $gadgetHTML = $GLOBALS['app']->LoadGadget('Weather', 'AdminHTML', 'Regions');
            return $gadgetHTML->Regions();
        }

        $this->gadget->CheckPermission('UpdateProperties');
        $gadgetHTML = $GLOBALS['app']->LoadGadget('Weather', 'AdminHTML', 'Properties');
        return $gadgetHTML->Properties();
    }

    /**
     * Builds the weather menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('Regions', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Regions';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageRegions')) {
            $menubar->AddOption('Regions', _t('WEATHER_REGIONS'),
                                BASE_SCRIPT . '?gadget=Weather&amp;action=Regions', 'gadgets/Weather/images/regions.png');
        }

        if ($this->gadget->GetPermission('UpdateProperties')) {
            $menubar->AddOption('Properties', _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Weather&amp;action=Properties', 'gadgets/Weather/images/properties.png');
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }
}