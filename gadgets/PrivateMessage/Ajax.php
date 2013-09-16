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
        @list($term) = jaws()->request->fetchAll('post');
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
        @list($term) = jaws()->request->fetchAll('post');
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Users');
        return $model->GetGroups($term);
    }

    /**
     * Save draft message
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveDraftMessage()
    {
        $post = jaws()->request->fetchAll('post');
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $res = $model->ComposeMessage($user, $post, array());
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PRIVATEMESSAGE_DRAFT_SAVED'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('PRIVATEMESSAGE_DRAFT_NOT_SAVED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}