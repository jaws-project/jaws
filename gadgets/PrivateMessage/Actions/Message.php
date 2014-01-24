<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Actions_Message extends Jaws_Gadget_Action
{

    /**
     * Display a message Info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Message()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = jaws()->request->fetch('id', 'get');
        $date = Jaws_Date::getInstance();
        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id, true);

        if (empty($message)) {
            return Jaws_HTTPError::Get(404);
        }

        // Check permissions
        if (!($message['from'] == $user && $message['to'] == 0) && $message['to'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        if ($message['read'] == false) {
            $user = $GLOBALS['app']->Session->GetAttribute('user');
            $model->MarkMessages($id, true, $user);
        }

        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->template->load('Message.html');
        $tpl->SetBlock('message');
        $tpl->SetVariable('id', $id);

        $tpl->SetBlock('message');

        $tpl->SetVariable('confirmDelete', _t('PRIVATEMESSAGE_MESSAGE_CONFIRM_DELETE'));
        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));

        $tpl->SetVariable('from', $message['from_nickname']);
        $tpl->SetVariable('username', $message['from_username']);
        $tpl->SetVariable('nickname', $message['from_nickname']);
        $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));
        $tpl->SetVariable('subject', $message['subject']);
        $tpl->SetVariable('body', $this->gadget->ParseText($message['body'], 'PrivateMessage', 'index'));

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

        if(!empty($message['attachments'])) {
            $tpl->SetBlock('message/attachment');
            $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
            foreach($message['attachments'] as $file) {
                $tpl->SetBlock('message/attachment/file');
                $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                $tpl->SetVariable('file_name', $file['title']);
                $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));

                $tpl->SetVariable('file_download_link', $file['title']);
                $file_url = $this->gadget->urlMap('Attachment',
                    array(
                        'uid' => $message['user'],
                        'mid' => $message['id'],
                        'aid' => $file['id'],
                    ));
                $tpl->SetVariable('file_download_link', $file_url);

                $tpl->ParseBlock('message/attachment/file');
            }
            $tpl->ParseBlock('message/attachment');
        }

        if(!empty($message['parent'])) {
            $tpl->SetBlock('message/history');
            $tpl->SetVariable('history_url',    $this->gadget->urlMap('MessageHistory', array('id' => $message['id'])));
            $tpl->SetVariable('icon_history',   'gadgets/PrivateMessage/Resources/images/history-mini.png');
            $tpl->SetVariable('history',        _t('PRIVATEMESSAGE_HISTORY'));
            $tpl->ParseBlock('message/history');
        }

        $tpl->SetBlock('message/reply');
        $tpl->SetVariable('reply_url', $this->gadget->urlMap('Compose', array('id' => $message['id'], 'reply' => 'true')));
        $tpl->SetVariable('icon_reply', 'gadgets/PrivateMessage/Resources/images/reply-mini.png');
        $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
        $tpl->ParseBlock('message/reply');

        if (!empty($message['users']) || !empty($message['groups'])) {
            if ($message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED) {
                $tpl->SetBlock('message/archive');
                $tpl->SetVariable('icon_archive', 'gadgets/PrivateMessage/Resources/images/archive-mini.png');
                $tpl->SetVariable('archive', _t('PRIVATEMESSAGE_ARCHIVE'));
                $tpl->SetVariable('archive_url', $this->gadget->urlMap('ArchiveMessage', array('id' => $id)));
                $tpl->ParseBlock('message/archive');
            }
        }

        if ($message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT &&
            $message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH
        ) {
            $tpl->SetBlock('message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                'id' => $message['id'],
                'reply' => 'false')));
            $tpl->SetVariable('icon_forward', 'gadgets/PrivateMessage/Resources/images/forward-mini.png');
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
            $tpl->ParseBlock('message/forward');
        }

        $tpl->SetBlock('message/back');
        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));
        $tpl->SetVariable('icon_back', 'gadgets/PrivateMessage/Resources/images/back-mini.png');
        $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->ParseBlock('message/back');

        $tpl->ParseBlock('message');
        return $tpl->Get();
    }

    /**
     * Archive message
     *
     * @access  public
     * @return  void
     */
    function ArchiveMessage()
    {
        $this->gadget->CheckPermission('ArchiveMessage');

        $ids = jaws()->request->fetch('id', 'get');
        $post = jaws()->request->fetch(array('message_checkbox:array', 'status'), 'post');
        $status = $post['status'];
        if ($status == 'retrieve') {
            $status = false;
        } else {
            $status = true;
        }

        if(!empty($post['message_checkbox']) && count($post['message_checkbox'])>0) {
            $ids = $post['message_checkbox'];
        }

        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->ArchiveMessage($ids, $user, $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_ARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_ARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}