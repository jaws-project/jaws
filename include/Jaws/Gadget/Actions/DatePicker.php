<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_DatePicker
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;


    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
    }


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
        // set default calendar if not set
        if (!isset($options['calendar'])) {
            $options['calendar'] = $this->gadget->registry->fetch('calendar', 'Settings');
        }
        $calendar = strtoupper($options['calendar']);

        $tpl->SetBlock("$block/datepicker");
        $tpl->SetVariable('id', isset($options['id'])? $options['id'] : $options['name']);
        $tpl->SetVariable('name', $options['name']);
        $tpl->SetVariable('value', isset($options['value'])? $options['value'] : '');
        $tpl->SetVariable('calendar', strtolower($calendar));
        $tpl->SetVariable('lbl_today', _t('GLOBAL_TODAY'));
        $tpl->SetVariable('lbl_month', _t('GLOBAL_MONTH'));
        $tpl->SetVariable('lbl_year', _t('GLOBAL_YEAR'));
        $tpl->SetVariable('lbl_select_month_year', _t('GLOBAL_SELECT_MONTH_YEAR'));

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

}