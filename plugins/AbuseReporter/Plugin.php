<?php
/**
 * Jaws AbuseReporter plugin
 *
 * @category   Plugin
 * @package    AbuseReporter
 */
class AbuseReporter_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version  = '0.1';
    var $_DefaultFrontendEnabled = false;
    var $_DefaultBackendEnabled  = false;

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
        $GLOBALS['app']->Layout->addScript('gadgets/AbuseReporter/Resources/index.js');

        $tpl = new Jaws_Template();
        $tpl->Load('Report.html', 'plugins/AbuseReporter/Templates/');
        $tpl->SetBlock('Report');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_title', _t('PLUGINS_ABUSEREPORTER_REPORT'));
        $tpl->SetVariable('lbl_report', _t('PLUGINS_ABUSEREPORTER_REPORT'));
        $tpl->SetVariable('lbl_send', _t('PLUGINS_ABUSEREPORTER_SEND'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('url', Jaws_Utils::getRequestURL(true));

        $tpl->ParseBlock('Report');
        return $html . $tpl->Get();
    }

}