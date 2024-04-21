<?php
/**
 * PrivateMessage Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     PrivateMessage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Admin_Properties extends PrivateMessage_Actions_Admin_Default
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->gadget->CheckPermission('ManageProperties');
        $this->AjaxMe('script.js');

        return;
    }

}