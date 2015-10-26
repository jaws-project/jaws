<?php
/**
 * Shoutbox Gadget
 *
 * @category    Gadget
 * @package     Shoutbox
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Jon Wood <jon@jellybob.co.uk>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Actions_Comments extends Jaws_Gadget_Action
{
    /**
     * Get the shoutbox comments full interface
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Comments()
    {
        $this->AjaxMe('site_script.js');
        $limit = (int)$this->gadget->registry->fetch('limit');
        $tpl = $this->gadget->template->load('Shoutbox.html');
        $tpl->SetBlock('shoutbox');
        $tpl->SetVariable('title', _t('SHOUTBOX_SHOUTBOX'));
        $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
        $tpl->SetVariable(
            'comments',
            $cHTML->ShowComments(
                'Shoutbox',
                '',
                0,
                array('action' => 'Comments', 'params' => array()),
                null,
                $limit,
                2
            )
        );

        $redirect_to = $this->gadget->urlMap('Comments', array());
        $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm('Shoutbox', '', 0, $redirect_to));

        $tpl->ParseBlock('shoutbox');
        return $tpl->Get();
    }

    /**
     * Get the shoutbox comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetComments()
    {
        $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
        return $cHTML->ShowComments('Shoutbox', '', 0, array('action' => 'Comments','params' => array()));
    }

}