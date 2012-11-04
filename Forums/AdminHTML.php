<?php
/**
 * Forums Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumsAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $forumHTML = $GLOBALS['app']->LoadGadget('Forums', 'AdminHTML', 'Forums');
        return $forumHTML->Forums();
    }

}