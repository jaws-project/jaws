<?php
/**
 * Chatbox AJAX API
 *
 * @category   Ajax
 * @package    Chatbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Chatbox_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Chatbox_Ajax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('Model');
    }

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