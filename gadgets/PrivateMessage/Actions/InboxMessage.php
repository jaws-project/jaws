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
class PrivateMessage_Actions_InboxMessage extends Jaws_Gadget_Action
{
    /**
     * Display an inbox message Info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function InboxMessage()
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
        if ($message['recipient'] > 0 && $message['recipient'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        if ($message['read'] == false) {
            $user = $GLOBALS['app']->Session->GetAttribute('user');
            $model->MarkMessages($id, true, $user);
        }

        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->template->load('InboxMessage.html');
        $tpl->SetBlock('inboxmessage');
        $tpl->SetVariable('id', $id);

        $tpl->SetBlock('inboxmessage/message');

        $tpl->SetVariable('confirmDelete', _t('PRIVATEMESSAGE_MESSAGE_CONFIRM_DELETE'));
        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));

        // check announcement
        if ($message['type'] == PrivateMessage_Info::PRIVATEMESSAGE_TYPE_ANNOUNCEMENT) {
            $tpl->SetBlock('inboxmessage/message/announcement');
            $tpl->SetVariable('lbl_message_is_announcement', _t('PRIVATEMESSAGE_MESSAGE_IS_ANNOUNCEMENT'));
            $tpl->ParseBlock('inboxmessage/message/announcement');
        }

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
            $tpl->SetBlock('inboxmessage/message/attachment');
            $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
            foreach($message['attachments'] as $file) {
                $tpl->SetBlock('inboxmessage/message/attachment/file');
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

                $tpl->ParseBlock('inboxmessage/message/attachment/file');
            }
            $tpl->ParseBlock('inboxmessage/message/attachment');
        }

        if(!empty($message['parent'])) {
            $tpl->SetBlock('inboxmessage/message/history');
            $tpl->SetVariable('history_url',    $this->gadget->urlMap('MessageHistory', array('id' => $message['id'])));
            $tpl->SetVariable('icon_history',   'gadgets/PrivateMessage/Resources/images/history-mini.png');
            $tpl->SetVariable('history',        _t('PRIVATEMESSAGE_HISTORY'));
            $tpl->ParseBlock('inboxmessage/message/history');
        }

        $tpl->SetBlock('inboxmessage/message/reply');
        $tpl->SetVariable('reply_url', $this->gadget->urlMap('Compose', array('id' => $message['id'], 'reply' => 'true')));
        $tpl->SetVariable('icon_reply', 'gadgets/PrivateMessage/Resources/images/reply-mini.png');
        $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
        $tpl->ParseBlock('inboxmessage/message/reply');

        if ($message['recipient'] != 0) {
            if (!$message['archived']) {
                $tpl->SetBlock('inboxmessage/message/archive');
                $tpl->SetVariable('icon_archive', 'gadgets/PrivateMessage/Resources/images/archive-mini.png');
                $tpl->SetVariable('archive', _t('PRIVATEMESSAGE_ARCHIVE'));
                $tpl->SetVariable('archive_url', $this->gadget->urlMap('ArchiveInboxMessage', array('id' => $id)));
                $tpl->ParseBlock('inboxmessage/message/archive');
            }
        }

        if ($message['published']) {
            $tpl->SetBlock('inboxmessage/message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                                                                   'id' => $message['id'],
                                                                   'reply'=>'false')));
            $tpl->SetVariable('icon_forward', 'gadgets/PrivateMessage/Resources/images/forward-mini.png');
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
            $tpl->ParseBlock('inboxmessage/message/forward');
        }

        $tpl->SetBlock('inboxmessage/message/back');
        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));
        $tpl->SetVariable('icon_back', 'gadgets/PrivateMessage/Resources/images/back-mini.png');
        $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->ParseBlock('inboxmessage/message/back');

        $tpl->ParseBlock('inboxmessage/message');
        $tpl->ParseBlock('inboxmessage');
        return $tpl->Get();
    }

    /**
     * Archive Inbox message
     *
     * @access  public
     * @return  void
     */
    function ArchiveInboxMessage()
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
        $res = $model->ArchiveInboxMessage($ids, $user, $status);
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

    /**
     * Delete Inbox message
     *
     * @access  public
     * @return  void
     */
    function DeleteInboxMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');

        $ids = jaws()->request->fetch('id', 'get');
        $post = jaws()->request->fetch('message_checkbox:array', 'post');

        if(!empty($post) && count($post)>0) {
            $ids = $post;
        }
        $model = $this->gadget->model->load('Message');
        $res = $model->DeleteInboxMessage($ids);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_DELETED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_DELETED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

    /**
     * Change message read status
     *
     * @access  public
     * @return  void
     */
    function ChangeMessageRead()
    {
        $get = jaws()->request->fetch(array('id', 'status'), 'get');
        $post = jaws()->request->fetch(array('message_checkbox:array', 'status'), 'post');
        $status = $post['status'];
        if ($status == 'read') {
            $status = true;
        } else {
            $status = false;
        }

        if(!empty($post['message_checkbox']) && count($post['message_checkbox'])>0) {
            $ids = $post['message_checkbox'];
        } else {
            $ids = $get['id'];
        }

        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $this->gadget->model->load('Message');
        $res = $model->MarkMessages($ids, $status, $user);
        if ($res === true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_READ_MESSAGE_STATUS_CHANGED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_READ_STATUS_NOT_CHANGED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}