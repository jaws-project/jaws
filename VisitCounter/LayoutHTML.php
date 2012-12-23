<?php
/**
 * VisitCounter Gadget (layout actions in client side)
 *
 * @category   GadgetLayout
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounterLayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Builds the visits report
     *
     * @access  public
     * @param   array   $visit_counters  Types of reports
     * @return  string  XHTML content
     */
    function GetVisitorsFormat($visit_counters)
    {
        $tpl = new Jaws_Template('gadgets/VisitCounter/templates/');
        $tpl->Load('VisitCounter.html');
        $tpl->SetBlock("VisiCounter");
        $tpl->SetVariable('title', _t('VISITCOUNTER_ACTION_TITLE'));

        $model    = $GLOBALS['app']->LoadGadget('VisitCounter', 'Model');
        $viewMode = strtolower($this->GetRegistry('mode'));
        $theme    = $GLOBALS['app']->GetTheme();
        if (is_dir($theme['path'] . 'VisitCounter/images/')) {
            $counter_image = $theme['url'] . 'VisitCounter/images/';
        } else {
            $counter_image = $GLOBALS['app']->getSiteURL('/gadgets/VisitCounter/images/', true);
        }

        $online_count = $model->GetOnlineVisitors();
        $today_count  = $model->GetTodayVisitors();
        $total_count  = $model->GetTotalVisitors();

        $date = $GLOBALS['app']->loadDate();
        $startdate = $date->Format($model->GetStartDate());

        if (in_array('online', $visit_counters)) {
            $tpl->SetBlock("VisiCounter/classic");
            $tpl->SetVariable('label', _t('VISITCOUNTER_ONLINE_VISITORS'));
            $tpl->SetVariable('value', $viewMode=='text'?
                                       $this->gadget->ParseText($online_count, 'VisitCounter') :
                                       preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $online_count));
            $tpl->ParseBlock("VisiCounter/classic");
        }

        if (in_array('today', $visit_counters)) {
            $tpl->SetBlock("VisiCounter/classic");
            $tpl->SetVariable('label', _t('VISITCOUNTER_TODAY_VISITORS'));
            $tpl->SetVariable('value', $viewMode=='text'?
                                       $this->gadget->ParseText($today_count, 'VisitCounter') :
                                       preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $today_count));
            $tpl->ParseBlock("VisiCounter/classic");
        }

        if (in_array('total', $visit_counters)) {
            $tpl->SetBlock("VisiCounter/classic");
            $tpl->SetVariable('label', _t('VISITCOUNTER_TOTAL_VISITORS'));
            $tpl->SetVariable('value', $viewMode=='text'?
                                       $this->gadget->ParseText($total_count, 'VisitCounter') :
                                       preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $total_count));
            $tpl->ParseBlock("VisiCounter/classic");
        }

        if (in_array('custom', $visit_counters)) {
            $custom = stripslashes($this->GetRegistry('custom_text'));
            if (trim($custom) == '') {
                $res = "$total_count - $startdate";
            } else {
                $tp = new Jaws_Template();
                $tp->LoadFromString("<!-- BEGIN x -->$custom<!-- END x -->");
                $tp->SetBlock('x');
                $tp->SetVariable('online', $viewMode=='text'?
                                           $this->gadget->ParseText($online_count, 'VisitCounter') :
                                           preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $online_count));
                $tp->SetVariable('today',  $viewMode=='text'?
                                           $this->gadget->ParseText($today_count, 'VisitCounter') :
                                           preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $today_count));
                $tp->SetVariable('total',  $viewMode=='text'?
                                           $this->gadget->ParseText($total_count, 'VisitCounter') :
                                           preg_replace('/([0-9])/', '<img src="'.$counter_image.'$1.png" alt="$1" />', $total_count));
                $tp->SetVariable('date',   $this->gadget->ParseText($startdate,    'VisitCounter'));
                $tp->ParseBlock('x');
                $res = $tp->Get();
                $tp = null;
            }
            $tpl->SetBlock('VisiCounter/custom');
            $tpl->SetVariable('custom_text', $res);
            $tpl->ParseBlock('VisiCounter/custom');
        }

        $tpl->ParseBlock("VisiCounter");
        return $tpl->Get();
    }

    /**
     * Displays the visit counter output
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Display()
    {
        $visit_counters = $this->GetRegistry('visit_counters');
        return $this->GetVisitorsFormat(explode(',', $visit_counters));
    }

    /**
     * Displays number of online visitors
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DisplayOnline()
    {
        return $this->GetVisitorsFormat(array('online'));
    }

    /**
     * Displays number of today visitors
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DisplayToday()
    {
        return $this->GetVisitorsFormat(array('today'));
    }

    /**
     * Displays number of total visitors
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DisplayTotal()
    {
        return $this->GetVisitorsFormat(array('total'));
    }

}
