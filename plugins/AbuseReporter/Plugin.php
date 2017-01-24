<?php
/**
 * Jaws AbuseReporter plugin
 *
 * @category   Plugin
 * @package    AbuseReporter
 */
class AbuseReporter_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version  = '0.1';
    var $_DefaultFrontendEnabled = true;

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        if (JAWS_SCRIPT == 'admin') {
            return $html;
        }

        $GLOBALS['app']->Layout->addScript('gadgets/AbuseReporter/Resources/index.js');

        $tpl = new Jaws_Template();
        $tpl->Load('Report.html', 'plugins/AbuseReporter/Templates/');
        $tpl->SetBlock('Report');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_title', _t('PLUGINS_ABUSEREPORTER_REPORT'));
        $tpl->SetVariable('lbl_report', _t('PLUGINS_ABUSEREPORTER_REPORT'));
        $tpl->SetVariable('lbl_send', _t('PLUGINS_ABUSEREPORTER_SEND'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        $tpl->SetVariable('gadget', $GLOBALS['app']->requestedGadget);
        $tpl->SetVariable('action', $GLOBALS['app']->requestedAction);
        $tpl->SetVariable('reference', '0');
        $tpl->SetVariable('url', Jaws_Utils::getRequestURL(true));

        $tpl->ParseBlock('Report');
        return $tpl->Get();

        return $html;
    }

}