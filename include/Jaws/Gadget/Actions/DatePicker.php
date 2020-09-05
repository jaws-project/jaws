<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_DatePicker extends Jaws_Gadget_Class
{
    /**
     * Get DatePicker calendar
     *
     * @access  public
     * @param   object  $tpl        (Optional) Jaws Template object
     * @param   array   $options    (Optional) Menu options
     * @return  string  XHTML template content
     */
    function calendar($tpl, $options = array())
    {
        if (empty($tpl)) {
            $tpl = new Jaws_Template();
            $tpl->Load('DatePicker.html', 'include/Jaws/Resources');
            $block = '';
        } else {
            $block = $tpl->GetCurrentBlockPath();
        }

        $options['id'] = isset($options['id'])? $options['id'] : $options['name'];
        // set default calendar if not set
        if (!isset($options['calendar'])) {
            $options['calendar'] = $this->gadget->registry->fetch('calendar', 'Settings');
        }
        $calendar = strtoupper($options['calendar']);

        $tpl->SetBlock("$block/datepicker");
        $tpl->SetVariable('id', $options['id']);
        $tpl->SetVariable('name', $options['name']);
        $tpl->SetVariable('value', isset($options['value'])? $options['value'] : '');
        $tpl->SetVariable('calendar', strtolower($calendar));
        $tpl->SetVariable('lbl_today', Jaws::t('TODAY'));
        $tpl->SetVariable('lbl_month', Jaws::t('MONTH'));
        $tpl->SetVariable('lbl_year', Jaws::t('YEAR'));
        $tpl->SetVariable('lbl_select_month_year', Jaws::t('SELECT_MONTH_YEAR'));
        if (isset($options['label'])) {
            $tpl->SetBlock("$block/datepicker/label");
            $tpl->SetVariable('label', $options['label']);
            $tpl->SetVariable('id', $options['id']);
            $tpl->ParseBlock("$block/datepicker/label");
        }

        // fill months name
        $tpl->SetBlock("$block/datepicker/months");
        for ($i = 0; $i < 12; $i++) {
            $tpl->SetBlock("$block/datepicker/months/month");
            $tpl->SetVariable('i', $i);
            $tpl->SetVariable('name', _t("GLOBAL_{$calendar}_MONTH_$i"));
            $tpl->ParseBlock("$block/datepicker/months/month");
        }
        $tpl->ParseBlock("$block/datepicker/months");

        // fill months short name
        $tpl->SetBlock("$block/datepicker/short_months");
        for ($i = 0; $i < 12; $i++) {
            $tpl->SetBlock("$block/datepicker/short_months/month");
            $tpl->SetVariable('i', $i);
        $tpl->SetVariable('name', _t("GLOBAL_{$calendar}_MONTH_SHORT_$i"));
            $tpl->ParseBlock("$block/datepicker/short_months/month");
        }
        $tpl->ParseBlock("$block/datepicker/short_months");

        // fill week days name
        $tpl->SetBlock("$block/datepicker/week_days");
        for ($i = 0; $i < 7; $i++) {
            $tpl->SetBlock("$block/datepicker/week_days/day");
            $tpl->SetVariable('i', $i);
            $tpl->SetVariable('name', _t("GLOBAL_{$calendar}_DAY_SHORT_$i"));
            $tpl->ParseBlock("$block/datepicker/week_days/day");
        }
        $tpl->ParseBlock("$block/datepicker/week_days");

        $tpl->ParseBlock("$block/datepicker");
        return $tpl->Get();
    }

    /**
     * Get DatePicker calendar assign array
     *
     * @access  public
     * @param   array   $options    (Optional) Menu options
     * @return  array   DatePicker calendar array
     */
    function xcalendar($options = array())
    {
        $options['id'] = isset($options['id'])? $options['id'] : $options['name'];
        // set default calendar if not set
        if (!isset($options['calendar'])) {
            $options['calendar'] = $this->gadget->registry->fetch('calendar', 'Settings');
        }
        $options['value'] = isset($options['value'])? $options['value'] : '';

        $assigns = array();
        $assigns = array_merge($assigns, $options);
        return $assigns;
    }

}