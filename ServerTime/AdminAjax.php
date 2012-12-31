<?php
/**
 * ServerTime AJAX API
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function ServerTime_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Updates properties
     *
     * @access  public
     * @param   string  $format The format of date and time being displayed
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($format)
    {
        $this->_Model->UpdateProperties($format);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}