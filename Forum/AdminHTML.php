<?php
/**
 * Forum Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $forumHTML = $GLOBALS['app']->LoadGadget('Forum', 'AdminHTML', 'Forums');
        return $forumHTML->Forums();
    }

}