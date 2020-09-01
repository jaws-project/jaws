<?php
/**
 * AbuseReport plugin
 *
 * @category   Plugin
 * @package    AbuseReport
 */
class AbuseReport_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version  = '0.1';
    var $onlyNormalMode = true;
    var $pluginType = JAWS_PLUGIN::PLUGIN_TYPE_ATTACHER;

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
    {
        if (!Jaws_Gadget::IsGadgetInstalled('AbuseReporter')) {
            return $html;
        }

        $this->app->layout->addScript('gadgets/AbuseReporter/Resources/index.js');
        $tpl = new Jaws_Template();
        $tpl->Load('Report.html', 'plugins/AbuseReport/Templates/');
        $tpl->SetBlock('Report');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_title', $this->plugin::t('REPORT'));
        $tpl->SetVariable('lbl_report', $this->plugin::t('REPORT'));
        $tpl->SetVariable('lbl_send', $this->plugin::t('SEND'));
        $tpl->SetVariable('lbl_cancel', $this->app::t('CANCEL'));

        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('url', Jaws_Utils::getRequestURL(true));

        $tpl->ParseBlock('Report');
        return $tpl->Get();
    }

}