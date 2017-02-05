<?php
/**
 * Bookmark plugin
 *
 * @category   Plugin
 * @package    Bookmark
 */
class Bookmark_Plugin extends Jaws_Plugin
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
        $GLOBALS['app']->Layout->addScript('gadgets/Users/Resources/index.js');
        $tpl = new Jaws_Template();
        $tpl->Load('Bookmark.html', 'plugins/Bookmark/Templates/');
        $tpl->SetBlock('Bookmark');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_title', _t('PLUGINS_BOOKMARK'));
        $tpl->SetVariable('lbl_bookmark', _t('PLUGINS_BOOKMARK'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('url', Jaws_Utils::getRequestURL(true));

        $tpl->ParseBlock('Bookmark');
        return $tpl->Get();
    }

}