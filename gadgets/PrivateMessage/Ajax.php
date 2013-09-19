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
     * Compose message
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function ComposeMessage()
    {
        $post = jaws()->request->fetch('0:array', 'post');
        $uploaded_files = jaws()->request->fetch('1:array', 'post');
        $attachments = $post['selected_attachments'];
        foreach($uploaded_files as $file) {
            if ($file==false) {
                continue;
            }
            $attachments[] = $file;
        }
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $res = $model->ComposeMessage($user, $post, $attachments);
        $url = $this->gadget->urlMap('Outbox');
        if ($res === true) {
            if($post['published']==true) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_MESSAGE_SEND'),
                    'PrivateMessage.Message',
                    RESPONSE_NOTICE
                );
            }
            $GLOBALS['app']->Session->PushLastResponse(
                _t('PRIVATEMESSAGE_DRAFT_SAVED'),
                RESPONSE_NOTICE, array('published' => $post['published'], 'url'=> $url));

        } else {

            if($post['published']==true) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_SEND'),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            }
            $GLOBALS['app']->Session->PushLastResponse(
                _t('PRIVATEMESSAGE_DRAFT_NOT_SAVED'),
                RESPONSE_ERROR, array('published' => $post['published'], 'url'=> $url));

        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}