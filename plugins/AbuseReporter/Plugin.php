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
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
     */
    function NeedParsing($html)
    {
        if (stripos($html, 'JAWS_REPORT') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        if (!$this->NeedParsing($html)) {
            return $html;
        }
        $html = str_replace('JAWS_REPORT', '', $html);

        $tpl = new Jaws_Template();
        $tpl->Load('Report.html', 'plugins/AbuseReporter/Templates/');
        $tpl->SetBlock('Report');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_report', _t('PLUGINS_ABUSEREPORTER_REPORT'));

        $tpl->SetVariable('lbl_comment', _t('ABUSEREPORTER_COMMENT'));
        $tpl->SetVariable('lbl_type', _t('ABUSEREPORTER_TYPE'));
        $tpl->SetVariable('lbl_priority', _t('ABUSEREPORTER_PRIORITY'));
        $tpl->SetVariable('lbl_send', _t('PLUGINS_ABUSEREPORTER_SEND'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('Report');
        return $tpl->Get();

        return $html;
    }

}