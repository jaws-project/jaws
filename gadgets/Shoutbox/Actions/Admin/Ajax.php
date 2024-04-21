<?php
/**
 * Shoutbox AJAX API
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Update the properties
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        @list($limit, $max_strlen, $authority) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $model->UpdateProperties($limit, $max_strlen, $authority == 'true');
        return $this->gadget->session->pop();
    }

}