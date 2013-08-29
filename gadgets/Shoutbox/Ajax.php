<?php
/**
 * Shoutbox AJAX API
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Get comments list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetComments()
    {
        $gadgetHTML = $GLOBALS['app']->LoadGadget('Shoutbox', 'HTML', 'Comments');
        return $gadgetHTML->GetComments();
    }

}