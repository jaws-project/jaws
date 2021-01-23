<?php
/**
 * ServerTime AJAX API
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Updates properties
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties()
    {
        @list($format) = $this->gadget->request->fetchAll('post');
        $modelServerTime = $this->gadget->model->loadAdmin('Properties');
        $modelServerTime->UpdateProperties($format);
        return $this->gadget->session->pop();
    }

}