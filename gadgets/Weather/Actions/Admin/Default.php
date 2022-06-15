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
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Actions_Admin_Default extends Jaws_Gadget_Action
{
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

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageRegions')) {
            $menubar->AddOption(
                'Regions',
                $this::t('REGIONS'),
                $this->gadget->url('Regions'),
                'gadgets/Weather/Resources/images/regions.png'
            );
        }

        if ($this->gadget->GetPermission('UpdateProperties')) {
            $menubar->AddOption(
                'Properties',
                Jaws::t('PROPERTIES'),
                $this->gadget->url('Properties'),
                'gadgets/Weather/Resources/images/properties.png'
            );
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }
}