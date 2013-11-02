<?php
require_once JAWS_PATH. 'gadgets/PrivateMessage/Actions/Admin/Default.php';
/**
 * PrivateMessage Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
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