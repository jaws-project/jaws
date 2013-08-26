<?php
/**
 * Shoutbox Gadget
 *
 * @category    Gadget
 * @package     Shoutbox
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Jon Wood <jon@jellybob.co.uk>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Actions_Comments extends Jaws_Gadget_HTML
{
    /**
     * Get the shoutbox comments full interface
     *
     * @access  public
     * @param   bool    $preview    preview mode
     * @return  string  XHTML template content
     */
    function Comments($preview = false)
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Shoutbox.html');
        $tpl->SetBlock('shoutbox');
        $tpl->SetVariable('title', _t('SHOUTBOX_SHOUTBOX'));
        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'HTML', 'Comments');

        $tpl->SetVariable(
            'comments',
            $cHTML->ShowComments('Shoutbox', '', 0, array('action' => 'Comments', 'params' => array()))
        );

        if ($preview) {
            $tpl->SetVariable('preview', $cHTML->ShowPreview());
        }

        $redirect_to = $this->gadget->urlMap('Comments', array());
        $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm('Shoutbox', '', 0, $redirect_to));

        $tpl->ParseBlock('shoutbox');
        return $tpl->Get();
    }

    /**
     * Displays a preview of the given shoutbox message
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        return $this->Comments(true);
    }

    /**
     * Get the shoutbox comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetComments()
    {
        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'HTML', 'Comments');
        return $cHTML->ShowComments('Shoutbox', '', 0, array('action' => 'Comments','params' => array()));
    }

}