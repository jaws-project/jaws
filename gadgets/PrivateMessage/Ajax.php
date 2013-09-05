<?php
/**
 * PrivateMessage AJAX API
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Fetches users list
     *
     * @access  public
     * @return  array   Returns an array of the available users
     */
    function GetUsers()
    {
        @list($term) = jaws()->request->getAll('post');
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        return $userModel->GetUsers(false, null, null, $term);
    }

    /**
     * Fetches groups list
     *
     * @access  public
     * @return  array   Returns an array of the available groups
     */
    function GetGroups()
    {
        @list($term) = jaws()->request->getAll('post');
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Users');
        return $model->GetGroups($term);
    }
}