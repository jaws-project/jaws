<?php
/**
 * Forum Gadget
 *
 * @category   Gadget
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumHTML extends Jaws_GadgetHTML
{
    /**
     * Default action
     *
     * @acces   public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $forumHTML = $GLOBALS['app']->LoadGadget('Forum', 'HTML', 'Forums');
        return $forumHTML->Forums();
    }

}