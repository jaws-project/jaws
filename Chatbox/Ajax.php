<?php
/**
 * Chatbox AJAX API
 *
 * @category   Ajax
 * @package    Chatbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Chatbox_Ajax extends Jaws_Gadget_Ajax
{
    /**
     * Get messages list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetMessages()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Chatbox', 'LayoutHTML');
        $messages = $layoutGadget->GetMessages();
        return $messages;
    }

}