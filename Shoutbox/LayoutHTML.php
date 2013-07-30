<?php
/**
 * Shoutbox Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Shoutbox
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays the shoutbox
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        return $this->GetMessages();
    }

    /**
     * Get the shoutbox messages list
     *
     * @access  public
     * @param   bool    $preview_mode       preview mode
     * @return  string  XHTML template content
     */
    function GetMessages($preview_mode = false)
    {
        $tpl = $this->gadget->loadTemplate('Shoutbox.html');
        $tpl->SetBlock('shoutbox');
        $tpl->SetVariable('title', _t('SHOUTBOX_SHOUTBOX'));
        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'HTML', 'Comments');

        $tpl->SetVariable('comments', $cHTML->ShowComments('Shoutbox', '', 0,
            array('action' => 'DefaultAction','params' => array())));

        if ($preview_mode) {
            $tpl->SetVariable('preview', $cHTML->ShowPreview());
        }

        $redirect_to = $this->gadget->GetURLFor('DefaultAction', array());
        $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm('Shoutbox', '', 0, $redirect_to));

        $tpl->ParseBlock('shoutbox');
        return $tpl->Get();
    }

}