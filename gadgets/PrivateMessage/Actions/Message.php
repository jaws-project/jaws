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
class PrivateMessage_Actions_Message extends Jaws_Gadget_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $get = jaws()->request->fetch(array('id', 'view'), 'get');
        $id = $get['id'];
        $view = $get['view'];
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        if ($view != 'reference') {
            $message = $model->GetMessage($id, true);
            if ($message['read'] == false) {
                $user = $GLOBALS['app']->Session->GetAttribute('user');
                $model->MarkMessages($id, true, $user);
            }
        } else {
            $message = $model->GetMessage($id, true, false);
        }

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

            $tpl->SetBlock('message/message_nav');
            $tpl->SetVariable('message_nav_url', $this->gadget->urlMap('Message',
                                                 array('id' => $message['parent'])));
            $tpl->SetVariable('message_nav', _t('PRIVATEMESSAGE_PREVIOUS_MESSAGE'));
            $tpl->ParseBlock('message/message_nav');
        }

        if ($view != 'reference') {
            $tpl->SetBlock('message/reply');
            $tpl->SetVariable('reply_url', $this->gadget->urlMap('Reply', array('id' => $id)));
            $tpl->SetVariable('icon_reply', STOCK_JUMP_TO);
            $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
            $tpl->ParseBlock('message/reply');

            $tpl->SetBlock('message/unread');
            $tpl->SetVariable('unread_url', $this->gadget->urlMap('UnreadMessage', array('id' => $id)));
            $tpl->SetVariable('icon_unread', STOCK_EMPTY);
            $tpl->SetVariable('unread', _t('PRIVATEMESSAGE_UNREAD'));
            $tpl->ParseBlock('message/unread');

            $tpl->SetBlock('message/delete');
            $tpl->SetVariable('icon_delete', STOCK_DELETE);
            $tpl->ParseBlock('message/delete');
            $tpl->SetVariable('delete_url', $this->gadget->urlMap('DeleteMessage', array('id' => $id)));

        } else {
            if (!$message['published']) {
                $tpl->SetBlock('message/publish');
                $tpl->SetVariable('icon_publish', STOCK_OK);
                $tpl->SetVariable('publish', _t('PRIVATEMESSAGE_PUBLISH'));
                $tpl->ParseBlock('message/publish');
                $tpl->SetVariable('publish_url', $this->gadget->urlMap('PublishMessage', array('id' => $id)));
            }
        }

        if ($message['published']) {
            $tpl->SetBlock('message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Send', array('id' => $message['id'])));
            $tpl->SetVariable('icon_forward', STOCK_RIGHT);
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
            $tpl->ParseBlock('message/forward');
        }

        $tpl->SetVariable('back_url',       $this->gadget->urlMap('Inbox'));

        $tpl->SetVariable('icon_back', STOCK_LEFT);

        $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));

        $tpl->ParseBlock('message');
        return $tpl->Get();
    }

    /**
     * Display a message history
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MessageHistory()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $id = jaws()->request->fetch('id', 'get');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $messages = array();
        $message = $model->GetMessage($id, false, false);
        $model->GetParentMessages($message['id'], true, $messages);
        if(empty($messages)) {
            return false;
        }
        $messages = array_reverse($messages, true);

        $tpl = $this->gadget->loadTemplate('MessageHistory.html');
        $tpl->SetBlock('history');

        foreach ($messages as $message) {
            $tpl->SetBlock('history/message');
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

            if (!empty($message['attachments'])) {
                $tpl->SetBlock('history/message/attachment');
                $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                foreach ($message['attachments'] as $file) {
                    $tpl->SetBlock('history/message/attachment/file');
                    $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                    $tpl->SetVariable('file_name', $file['title']);
                    $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));

                    $tpl->SetVariable('file_download_link', $file['title']);
                    $file_url = $this->gadget->urlMap('Attachment',
                        array(
                            'uid' => $message['user'],
                            'mid' => $id,
                            'aid' => $file['id'],
                        ));
                    $tpl->SetVariable('file_download_link', $file_url);

                    $tpl->ParseBlock('history/message/attachment/file');
                }
                $tpl->ParseBlock('history/message/attachment');
            }

            $tpl->SetVariable('reply_url', $this->gadget->urlMap('Reply', array('id' => $id)));
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Send', array('id' => $id)));
            $tpl->SetVariable('delete_url', $this->gadget->urlMap('DeleteMessage', array('id' => $id)));
            $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

            $tpl->SetVariable('icon_back',      STOCK_LEFT);
            $tpl->SetVariable('icon_forward',   STOCK_RIGHT);
            $tpl->SetVariable('icon_reply',     STOCK_JUMP_TO);
            $tpl->SetVariable('icon_delete',    STOCK_DELETE);

            $tpl->SetVariable('back', _t('PRIVATEMESSAGE_BACK'));
            $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));

            $tpl->ParseBlock('history/message');
        }

        $tpl->ParseBlock('history');
        return $tpl->Get();
    }

    /**
     * Delete a message
     *
     * @access  public
     * @return  void
     */
    function DeleteMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');

        $id = jaws()->request->fetch('id', 'get');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->DeleteMessage($id, $user);
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
     * Publish a message
     *
     * @access  public
     * @return  void
     */
    function PublishMessage()
    {
        $this->gadget->CheckPermission('SendMessage');

        $id = jaws()->request->fetch('id', 'get');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->PublishMessage($id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_PUBLISHED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
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


    /**
     * Unread a message
     *
     * @access  public
     * @return  void
     */
    function UnreadMessage()
    {
        $id = jaws()->request->fetch('id', 'get');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->MarkMessages($id, false, $user);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_UNREAD'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_UNREAD'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}