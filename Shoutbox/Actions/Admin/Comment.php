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
class Shoutbox_Actions_Admin_Comment extends Shoutbox_AdminHTML
{
    /**
     * Displays shoutbox admin (comments manager)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $GLOBALS['app']->Layout->AddScriptLink('gadgets/Comments/resources/script.js');

        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHTML->Comments('shoutbox', $this->MenuBar('Comments'));
    }



}