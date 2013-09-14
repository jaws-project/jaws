<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Actions_Reply extends Jaws_Gadget_HTML
{
    /**
     * Display Reply message form
     *
     * @access  public
     * @return  void
     */
    function Reply()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('SendMessage');
        $this->AjaxMe('site_script.js');

        $id = jaws()->request->fetch('id', 'get');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id);

        $tpl = $this->gadget->loadTemplate('Reply.html');
        $tpl->SetBlock('reply');
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_REPLY'));

        $tpl->SetVariable('id', $id);

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));
        $tpl->SetVariable('lbl_reply', _t('PRIVATEMESSAGE_REPLY'));
        $tpl->SetVariable('lbl_send', _t('PRIVATEMESSAGE_SEND'));
        $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
        $tpl->SetVariable('lbl_file', _t('PRIVATEMESSAGE_FILE'));
        $tpl->SetVariable('lbl_add_file', _t('PRIVATEMESSAGE_ADD_ANOTHER_FILE'));
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_draft', _t('GLOBAL_DRAFT'));
        $tpl->SetVariable('reply_subject', _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']));
        $tpl->SetVariable('from', $message['from_nickname']);
        $tpl->SetVariable('username', $message['from_username']);
        $tpl->SetVariable('nickname', $message['from_nickname']);
        $tpl->SetVariable('send_time', $date->Format($message['insert_time']));
        $tpl->SetVariable('subject', $message['subject']);
        $tpl->SetVariable('body', $message['body']);

        // user's avatar
        $tpl->SetVariable(
            'avatar',
            $usrModel->GetAvatar(
                $message['avatar'],
                $message['email'],
                80
            )
        );

        // user's profile
        $tpl->SetVariable(
            'user_url',
            $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'Profile',
                array('user' => $message['from_username'])
            )
        );

        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

        $tpl->ParseBlock('reply');
        return $tpl->Get();
    }

    /**
     * Reply a message
     *
     * @access  public
     * @return  void
     */
    function ReplyMessage()
    {
        $this->gadget->CheckPermission('ReplyMessage');

        $post = jaws()->request->fetch(array('id', 'subject', 'body', 'status'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if($post['status']=='published') {
            $post['published'] = true;
        } else {
            $post['published'] = false;
        }
        unset($post['status']);

        $pm_dir = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $user . DIRECTORY_SEPARATOR;
        if (!file_exists($pm_dir)) {
            if (!Jaws_Utils::mkdir($pm_dir)) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('GLOBAL_ERROR_FAILED_CREATING_DIR', $pm_dir),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );

                Jaws_Header::Location($this->gadget->urlMap('Inbox'));
            }
        }

        // detect message have attachment(s)?
        if(!empty($_FILES['file1']['name'])) {
            $files = Jaws_Utils::UploadFiles(
                $_FILES,
                $pm_dir,
                '',
                'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                null
            );

            if (Jaws_Error::IsError($files)) {
                $GLOBALS['app']->Session->PushResponse(
                    $files->GetMessage(),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            } else if ($files === false || count($files)<1) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_ERROR_NO_FILE_UPLOADED'),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            } else {
                for ($i = 1; $i <= count($files); $i++) {
                    if (!isset($files['file'.$i])) {
                        continue;
                    }
                    $user_filename = $files['file'.$i][0]['user_filename'];
                    $host_filename = $files['file'.$i][0]['host_filename'];
                    if (!empty($host_filename)) {
                        $attachments[] = array(
                            'title'=> $user_filename,
                            'filename'=> $host_filename,
                            'filesize'=> $_FILES['file'.$i]['size'],
                        );
                    }
                }
            }

        }

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $message = $model->GetMessage($post['id']);
        $post['parent']          = $message['id'];
        $post['recipient_users'] = $message['user'];
        $res = $model->SendMessage($user, $post, $attachments);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Message', array('id' => $post['id'])));
    }
}