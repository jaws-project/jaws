<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumsHTML extends Jaws_Gadget_HTML
{
    /**
     * Default action
     *
     * @acces   public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $forumHTML = $GLOBALS['app']->LoadGadget('Forums', 'HTML', 'Forums');
        return $forumHTML->Forums();
    }

}