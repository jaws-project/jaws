<?php
/**
 * ServerTime AJAX API
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTimeAdminAjax extends Jaws_Gadget_Ajax
{
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