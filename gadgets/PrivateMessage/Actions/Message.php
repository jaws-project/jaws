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
class PrivateMessage_Actions_Message extends PrivateMessage_Actions_Default
{

    /**
     * Display messages list
     *
     * @access  public
     * @return  void
     */
    function Messages()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->template->load('Messages.html');
        $tpl->SetBlock('messages');

        $post = jaws()->request->fetch(array('folder', 'page', 'read', 'term', 'page_item'));
        $page = $post['page'];
        $folder = $post['folder'];

        $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
        $tpl->SetVariable('txt_term', $post['term']);

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_recipients', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));

        switch ($folder) {
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX:
                $menubar = $this->MenuBar('Inbox');
                $title = _t('PRIVATEMESSAGE_INBOX');

                $tpl->SetBlock('messages/inbox_action');
                $tpl->SetVariable('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
                $tpl->SetVariable('lbl_mark_as_read', _t('PRIVATEMESSAGE_MARK_AS_READ'));
                $tpl->SetVariable('lbl_mark_as_unread', _t('PRIVATEMESSAGE_MARK_AS_UNREAD'));
                $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
                $tpl->ParseBlock('messages/inbox_action');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX:
                $menubar = $this->MenuBar('Outbox');
                $title = _t('PRIVATEMESSAGE_OUTBOX');

                $tpl->SetBlock('messages/outbox_action');
                $tpl->SetVariable('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
                $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
                $tpl->ParseBlock('messages/outbox_action');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT:
                $menubar = $this->MenuBar('Draft');
                $title = _t('PRIVATEMESSAGE_DRAFT');

                $tpl->SetBlock('messages/draft_action');
                $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
                $tpl->ParseBlock('messages/draft_action');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED:
                $menubar = $this->MenuBar('Archived');
                $title = _t('PRIVATEMESSAGE_ARCHIVED');

                $tpl->SetBlock('messages/archive_action');
                $tpl->SetVariable('lbl_unarchive', _t('PRIVATEMESSAGE_UNARCHIVE'));
                $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
                $tpl->ParseBlock('messages/archive_action');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH:
                $menubar = $this->MenuBar('Trash');
                $title = _t('PRIVATEMESSAGE_TRASH');

                $tpl->SetBlock('messages/trash_action');
                $tpl->SetVariable('lbl_restore', _t('PRIVATEMESSAGE_RESTORE'));
                $tpl->SetVariable('lbl_delete', _t('PRIVATEMESSAGE_DELETE'));
                $tpl->ParseBlock('messages/trash_action');
                break;
            default:
                $menubar = $this->MenuBar('AllMessages');
                $title = _t('PRIVATEMESSAGE_ALL_MESSAGES');

                $tpl->SetBlock('messages/in_out_lbl');
                $tpl->SetVariable('lbl_in_out', _t('PRIVATEMESSAGE_IN_OUT'));
                $tpl->ParseBlock('messages/in_out_lbl');

                $tpl->SetBlock('messages/all_action');
                $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
                $tpl->ParseBlock('messages/all_action');
        }
        $tpl->SetVariable('menubar', $menubar);
        $tpl->SetVariable('title', $title);


        $page = empty($page) ? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = $this->gadget->registry->fetch('paging_limit');
            if(empty($limit)) {
                $limit = 10;
            }
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $tpl->SetVariable('page', $page);
        $tpl->SetVariable('folder', $folder);
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));

        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);

        $date = Jaws_Date::getInstance();
        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $messages = $model->GetMessages(
            $user,
            $folder,
            $post,
            $limit,
            ($page - 1) * $limit);

        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('messages/message');
                $tpl->SetVariable('rownum', $i);
                $tpl->SetVariable('id',  $message['id']);
                $tpl->SetVariable('from', $message['from_nickname']);

                if (empty($folder)) {
                    $tpl->SetBlock('messages/message/in_out');
                    if ($message['from'] == $user) {
                        $tpl->SetVariable('in_out', _t('PRIVATEMESSAGE_OUT'));
                    } else {
                        $tpl->SetVariable('in_out', _t('PRIVATEMESSAGE_IN'));
                    }
                    $tpl->ParseBlock('messages/message/in_out');
                }


                if($message['read']) {
                    $subject = $message['subject'];
                    $tpl->SetVariable('status', 'read');
                } else {
                    $subject = '<strong>' . $message['subject'] . '</strong>';
                    $tpl->SetVariable('status', 'unread');
                }
                $tpl->SetVariable('subject', $subject);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));

                $tpl->SetVariable('message_url', $this->gadget->urlMap('Message', array('id' => $message['id'])));

                if ($message['attachments'] > 0) {
                    $tpl->SetBlock('messages/message/have_attachment');
                    $tpl->SetVariable('attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
                    $tpl->SetVariable('icon_attachment', STOCK_ATTACH);
                    $tpl->ParseBlock('messages/message/have_attachment');
                } else {
                    $tpl->SetBlock('messages/message/no_attachment');
                    $tpl->ParseBlock('messages/message/no_attachment');
                }

                $messageInfo = $model->GetMessage($message['id']);
                // user's profile
                $user_url = $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'Profile',
                    array('user' => $messageInfo['users'][0]['username']));
                $recipients_str = '<a href=' . $user_url . '>' . $messageInfo['users'][0]['nickname'] . '<a/>';
                if (count($messageInfo['users']) > 1) {
                    $recipients_str .= ' , ...';
                }
                $tpl->SetVariable('recipients', $recipients_str);

                $tpl->ParseBlock('messages/message');
            }
        }


        $inboxTotal = $model->GetMessagesStatistics($user, PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX, $post);

        $params = array();
        if(!empty($post['read'])) {
            $params['read'] = $post['read'];
        }
        if(!empty($post['replied'])) {
            $params['replied'] = $post['replied'];
        }
        if(!empty($post['term'])) {
            $params['term'] = $post['term'];
        }
        if (!empty($post['page_item'])) {
            $params['page_item'] = $post['page_item'];
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'messages',
            $page,
            $limit,
            $inboxTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $inboxTotal),
            'Inbox',
            $params
        );

        $tpl->ParseBlock('messages');
        return $tpl->Get();
    }


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

        if ($message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH) {
            $tpl->SetBlock('message/archive');

            if ($message['folder'] == PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED) {
                $tpl->SetVariable('icon_archive', 'gadgets/PrivateMessage/Resources/images/unarchive-mini.png');
                $tpl->SetVariable('archive', _t('PRIVATEMESSAGE_UNARCHIVE'));
                $tpl->SetVariable('archive_url', $this->gadget->urlMap(
                    'UnArchiveMessage',
                    array('id' => $id)));
            } else {
                $tpl->SetVariable('icon_archive', 'gadgets/PrivateMessage/Resources/images/archive-mini.png');
                $tpl->SetVariable('archive', _t('PRIVATEMESSAGE_ARCHIVE'));
                $tpl->SetVariable('archive_url', $this->gadget->urlMap(
                    'ArchiveMessage',
                    array('id' => $id)));
            }

            $tpl->ParseBlock('message/archive');
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
        $messagesSelected = jaws()->request->fetch('message_checkbox:array', 'post');
        if (!empty($messagesSelected) && count($messagesSelected) > 0) {
            $ids = $messagesSelected;
        }

        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->ArchiveMessage($ids, $user, true);
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
        Jaws_Header::Location($this->gadget->urlMap('Inbox', array('view' => 'archived')));
    }


    /**
     * UnArchive message
     *
     * @access  public
     * @return  void
     */
    function UnArchiveMessage()
    {
        $this->gadget->CheckPermission('ArchiveMessage');

        $ids = jaws()->request->fetch('id', 'get');
        $messagesSelected = jaws()->request->fetch('message_checkbox:array', 'post');
        if (!empty($messagesSelected) && count($messagesSelected) > 0) {
            $ids = $messagesSelected;
        }

        $model = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->ArchiveMessage($ids, $user, false);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_UNARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_UNARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}