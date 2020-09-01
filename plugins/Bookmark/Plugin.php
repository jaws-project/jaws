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
        $this->app->layout->addScript('gadgets/Users/Resources/index.js');
        $tpl = new Jaws_Template();
        $tpl->Load('Bookmark.html', 'plugins/Bookmark/Templates/');
        $tpl->SetBlock('Bookmark');
        $tpl->SetVariable('content', $html);

        $tpl->SetVariable('lbl_title', $this->plugin::t('BOOKMARK'));
        $tpl->SetVariable('lbl_bookmark', $this->plugin::t('BOOKMARK'));
        $tpl->SetVariable('lbl_save', $this->app::t('SAVE'));
        $tpl->SetVariable('lbl_cancel', $this->app::t('CANCEL'));

        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('url', Jaws_Utils::getRequestURL(true));

        $tpl->ParseBlock('Bookmark');
        return $tpl->Get();
    }

}