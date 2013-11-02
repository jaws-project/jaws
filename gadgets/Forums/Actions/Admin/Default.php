<?php
/**
 * Forums Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $forumHTML = $this->gadget->loadAdminAction('Forums');
        return $forumHTML->Forums();
    }

}