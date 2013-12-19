<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetAdmin
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Actions_Admin_Comments extends Shoutbox_Actions_Admin_Default
{
    /**
     * Displays shoutbox admin (comments manager)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Comments()
    {
        $cHTML = Jaws_Gadget::getInstance('Comments')->action->loadAdmin('Comments');
        return $cHTML->Comments($this->gadget->name, $this->MenuBar('Comments'));
    }

}