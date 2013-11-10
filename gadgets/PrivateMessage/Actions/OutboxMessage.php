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
class PrivateMessage_Actions_OutboxMessage extends Jaws_Gadget_Action
{
    /**
     * Display an outbox message Info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function OutboxMessage()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = jaws()->request->fetch('id', 'get');
        $date = Jaws_Date::getInstance();
        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id, true, false);

        if (empty($message)) {
            return Jaws_HTTPError::Get(404);
        }

        // Check permissions
        if ($message['user'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->template->load('OutboxMessage.html');
        $tpl->SetBlock('outboxmessage');
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('confirmDelete', _t('PRIVATEMESSAGE_MESSAGE_CONFIRM_DELETE'));

        $tpl->SetBlock('outboxmessage/message');
        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));

        // check announcement
        if ($message['type'] == PrivateMessage_Info::PRIVATEMESSAGE_TYPE_ANNOUNCEMENT) {
            $tpl->SetBlock('outboxmessage/message/announcement');
            $tpl->SetVariable('lbl_message_is_announcement', _t('PRIVATEMESSAGE_MESSAGE_IS_ANNOUNCEMENT'));
            $tpl->ParseBlock('outboxmessage/message/announcement');
        }

        // fill recipients users
        $recipients = $model->GetMessageRecipientsInfo($message['id']);
        $tpl->SetBlock('outboxmessage/message/recipients');
        $tpl->SetVariable('lbl_recipients', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));

        if ($message['type'] == PrivateMessage_Info::PRIVATEMESSAGE_TYPE_MESSAGE) {
            $tpl->SetBlock('outboxmessage/message/recipients/recipients_list');
            $tpl->SetVariable('lbl_recipient', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT'));
            $tpl->SetVariable('lbl_view_time', _t('PRIVATEMESSAGE_MESSAGE_VIEW_TIME'));

            $i = 0;
            foreach ($recipients as $recipient) {
                $i++;
                $tpl->SetBlock('outboxmessage/message/recipients/recipients_list/recipient');
                $tpl->SetVariable('rownum', $i);

                // user's profile
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $recipient['username'])
                    )
                );
                $tpl->SetVariable('recipient', $recipient['nickname']);
                if(!empty($recipient['update_time'])) {
                    $tpl->SetVariable('view_time', $date->Format($recipient['update_time'], $date_format));
                } else {
                    $tpl->SetVariable('view_time', _t('PRIVATEMESSAGE_MESSAGE_NOT_VIEW'));
                }


                if ($i < count($recipients)) {
                    $tpl->SetVariable('separator', ',');
                } else {
                    $tpl->SetVariable('separator', '');
                }
                $tpl->ParseBlock('outboxmessage/message/recipients/recipients_list/recipient');
            }

            $tpl->ParseBlock('outboxmessage/message/recipients/recipients_list');

        } else {
            $tpl->SetVariable('lbl_all_users', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_ALL_USERS'));
        }
        $tpl->ParseBlock('outboxmessage/message/recipients');

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
            $tpl->SetBlock('outboxmessage/message/attachment');
            $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
            foreach($message['attachments'] as $file) {
                $tpl->SetBlock('outboxmessage/message/attachment/file');
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

                $tpl->ParseBlock('outboxmessage/message/attachment/file');
            }
            $tpl->ParseBlock('outboxmessage/message/attachment');
        }

        if(!empty($message['parent'])) {
            $tpl->SetBlock('outboxmessage/message/history');
            $tpl->SetVariable('history_url',    $this->gadget->urlMap('MessageHistory', array('id' => $message['id'])));
            $tpl->SetVariable('icon_history',   'gadgets/PrivateMessage/Resources/images/history-mini.png');
            $tpl->SetVariable('history',        _t('PRIVATEMESSAGE_HISTORY'));
            $tpl->ParseBlock('outboxmessage/message/history');
        }

        // View outbox message
        if (!$message['published']) {
            $tpl->SetBlock('outboxmessage/message/publish');
            $tpl->SetVariable('icon_publish', STOCK_OK);
            $tpl->SetVariable('publish', _t('PRIVATEMESSAGE_PUBLISH'));
            $tpl->SetVariable('publish_url', $this->gadget->urlMap('PublishMessage', array('id' => $id)));
            $tpl->ParseBlock('outboxmessage/message/publish');

            $tpl->SetBlock('outboxmessage/message/delete');
            $tpl->SetVariable('icon_delete', STOCK_DELETE);
            $tpl->SetVariable('delete', _t('GLOBAL_DELETE'));
            $tpl->ParseBlock('outboxmessage/message/delete');
            $tpl->SetVariable('delete_url', $this->gadget->urlMap(
                'DeleteOutboxMessage', array('id' => $id)));

        } else {
            if ($message['read_count'] < 1) {
                $tpl->SetBlock('outboxmessage/message/draft');
                $tpl->SetVariable('icon_draft', STOCK_STOP);
                $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_MESSAGE_UNDO_SENDING'));
                $tpl->SetVariable('draft_url', $this->gadget->urlMap('DraftMessage', array('id' => $id)));
                $tpl->ParseBlock('outboxmessage/message/draft');
            }
        }

        if ($message['published']) {
            $tpl->SetBlock('outboxmessage/message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                                                                   'id' => $message['id'],
                                                                   'reply'=>'false')));
            $tpl->SetVariable('icon_forward', 'gadgets/PrivateMessage/Resources/images/forward-mini.png');
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
            $tpl->ParseBlock('outboxmessage/message/forward');
        }

        $tpl->SetBlock('outboxmessage/message/back');
        $tpl->SetVariable('back_url', $this->gadget->urlMap('Outbox'));
        $tpl->SetVariable('icon_back', 'gadgets/PrivateMessage/Resources/images/back-mini.png');
        $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->ParseBlock('outboxmessage/message/back');

        $tpl->ParseBlock('outboxmessage/message');
        $tpl->ParseBlock('outboxmessage');
        return $tpl->Get();
    }

    /**
     * Delete outbox message
     *
     * @access  public
     * @return  void
     */
    function DeleteOutboxMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');

        $ids = jaws()->request->fetch('id', 'get');
        $post = jaws()->request->fetch('message_checkbox:array', 'post');

        if (!empty($post) && count($post) > 0) {
            $ids = $post;
        }
        $model = $this->gadget->model->load('Message');
        $res = $model->DeleteOutboxMessage($ids);
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
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_DELETED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Draft'));
    }

    /**
     * Publish a message
     *
     * @access  public
     * @return  void
     */
    function PublishMessage()
    {
        $this->gadget->CheckPermission('ComposeMessage');

        $id = jaws()->request->fetch('id', 'get');

        $model = $this->gadget->model->load('Message');
        $res = $model->MarkMessagesPublishStatus($id, true);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res === true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_PUBLISHED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_PUBLISHED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Draft'));
    }
}