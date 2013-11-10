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
class Weather_Actions_Admin_Properties extends Weather_Actions_Admin_Default
{

    /**
     * Builds Properties section of the gadget
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Properties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Properties.html');
        $tpl->SetBlock('Properties');

        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $unit =& Piwi::CreateWidget('Combo', 'unit');
        $unit->AddOption(_t('WEATHER_UNIT_METRIC'), 'metric');
        $unit->AddOption(_t('WEATHER_UNIT_IMPERIAL'), 'imperial');
        $unit->SetDefault($this->gadget->registry->fetch('unit'));
        $tpl->SetVariable('lbl_unit', _t('WEATHER_UNIT'));
        $tpl->SetVariable('unit', $unit->Get());

        $period =& Piwi::CreateWidget('Combo', 'update_period');
        $period->AddOption(_t('GLOBAL_DISABLE'),              0);
        $period->AddOption(_t('GLOBAL_DATE_MINUTES', 30),  1800);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   1),   3600);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   3),  10800);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   6),  21600);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   8),  28800);
        $period->AddOption(_t('GLOBAL_DATE_DAYS',    1),  86400);
        $period->SetDefault($this->gadget->registry->fetch('update_period'));
        $tpl->SetVariable('lbl_update_period', _t('WEATHER_UPDATE_PERIOD'));
        $tpl->SetVariable('update_period', $period->Get());

        $now = time();
        $objDate = Jaws_Date::getInstance();
        $dFormat =& Piwi::CreateWidget('Combo', 'date_format');
        $dFormat->setStyle('width:208px;');
        $dFormat->AddOption($objDate->Format($now, 'DN'), 'DN');
        $dFormat->AddOption($objDate->Format($now, 'd MN'), 'd MN');
        $dFormat->AddOption($objDate->Format($now, 'DN d MN'), 'DN d MN');
        $dFormat->SetDefault($this->gadget->registry->fetch('date_format'));
        $tpl->SetVariable('lbl_date_format', _t('WEATHER_DATE_FORMAT'));
        $tpl->SetVariable('date_format', $dFormat->Get());

        $apikey =& Piwi::CreateWidget('Entry',
            'api_key',
            $this->gadget->registry->fetch('api_key'));
        $apikey->setStyle('width:200px; direction: ltr;');
        $tpl->SetVariable('lbl_api_key', _t('WEATHER_API_KEY'));
        $tpl->SetVariable('lbl_api_key_desc', _t('WEATHER_API_KEY_DESC'));
        $tpl->SetVariable('api_key', $apikey->Get());

        if ($this->gadget->GetPermission('UpdateSetting')) {
            $btnupdate =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnupdate->AddEvent(ON_CLICK, 'updateProperties();');
            $tpl->SetVariable('btn_save', $btnupdate->Get());
        }

        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }
}