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
class PrivateMessage_Actions_OutboxMessage extends Jaws_Gadget_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $id = jaws()->request->fetch('id', 'get');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id, true, false);

        $tpl = $this->gadget->loadTemplate('Message.html');
        $tpl->SetBlock('message');

        $tpl->SetVariable('id', $id);

        $tpl->SetVariable('confirmDelete', _t('PRIVATEMESSAGE_MESSAGE_CONFIRM_DELETE'));
        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));

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
            $tpl->SetVariable('icon_history',   STOCK_UNDO);
            $tpl->SetVariable('history',        _t('PRIVATEMESSAGE_HISTORY'));
            $tpl->ParseBlock('message/history');

//            $tpl->SetBlock('message/message_nav');
//            $tpl->SetVariable('message_nav_url', $this->gadget->urlMap('Message',
//                                                 array('id' => $message['parent'])));
//            $tpl->SetVariable('message_nav', _t('PRIVATEMESSAGE_PREVIOUS_MESSAGE'));
//            $tpl->ParseBlock('message/message_nav');
        }

        // View outbox message
        if (!$message['published']) {
            $tpl->SetBlock('message/publish');
            $tpl->SetVariable('icon_publish', STOCK_OK);
            $tpl->SetVariable('publish', _t('PRIVATEMESSAGE_PUBLISH'));
            $tpl->SetVariable('publish_url', $this->gadget->urlMap('PublishMessage', array('id' => $id)));
            $tpl->ParseBlock('message/publish');

            $tpl->SetBlock('message/delete');
            $tpl->SetVariable('icon_delete', STOCK_DELETE);
            $tpl->ParseBlock('message/delete');
            $tpl->SetVariable('delete_url', $this->gadget->urlMap(
                'DeleteOutboxMessage', array('id' => $id)));

        } else {
            if ($message['read_count'] < 1) {
                $tpl->SetBlock('message/draft');
                $tpl->SetVariable('icon_draft', STOCK_BOOK);
                $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT'));
                $tpl->SetVariable('draft_url', $this->gadget->urlMap('DraftMessage', array('id' => $id)));
                $tpl->ParseBlock('message/draft');
            }
        }

        if ($message['published']) {
            $tpl->SetBlock('message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                                                                   'id' => $message['id'],
                                                                   'reply'=>'false')));
            $tpl->SetVariable('icon_forward', STOCK_RIGHT);
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
            $tpl->ParseBlock('message/forward');
        }

        $tpl->SetVariable('back_url', $this->gadget->urlMap('Outbox'));

        $tpl->SetVariable('icon_back', STOCK_LEFT);

        $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));

        $tpl->ParseBlock('message');
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
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
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

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
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