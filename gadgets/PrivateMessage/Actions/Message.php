<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Message extends PrivateMessage_Actions_Default
{
    /**
     * Get Messages action params
     *
     * @access  public
     * @return  array    list of Messages action params
     */
    function MessagesLayoutParams()
    {
        $result = array();
        $folders = array();
        for ($i = 1; $i <= 6; $i++) {
            $folders[$i] = $this::t('MESSAGE_FOLDER_'. $i);
        }

        $result[] = array(
            'title' => $this::t('MESSAGE_FOLDER'),
            'value' => $folders
        );

        return $result;
    }

    /**
     * Display messages list
     *
     * @access  public
     * @return  void
     */
    function Messages($folder = null)
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_delete', Jaws::t('DELETE'));
        $this->gadget->define('lbl_view', Jaws::t('VIEW'));
        $this->gadget->define('lbl_archive', $this::t('ARCHIVE'));
        $this->gadget->define('lbl_mark_as_read', $this::t('MARK_AS_READ'));
        $this->gadget->define('lbl_mark_as_unread', $this::t('MARK_AS_UNREAD'));
        $this->gadget->define('lbl_trash', $this::t('TRASH'));
        $this->gadget->define('lbl_restore_trash', $this::t('RESTORE_TRASH'));
        $this->gadget->define('lbl_unarchive', $this::t('UNARCHIVE'));
        $this->gadget->define('lbl_view_message', $this::t('MESSAGE_VIEW'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('Messages.html');
        $tpl->SetBlock('messages');

        $post = $this->gadget->request->fetch(array('folder', 'page', 'read', 'term', 'page_item'));
        $folder = is_null($folder) ? (int)$post['folder'] : $folder;
        $this->gadget->define('folder', $folder);
        $this->gadget->define('folders', array(
            'inbox' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX,
            'draft' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT,
            'outbox' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX,
            'archived' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED,
            'trash' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH,
            'notifications' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS,
        ));

        $tpl->SetVariable('lbl_term', Jaws::t('TERM'));
        $tpl->SetVariable('lbl_reload', Jaws::t('RELOAD'));
        $tpl->SetVariable('lbl_from', $this::t('MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', $this::t('MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', $this::t('MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_recipients', $this::t('MESSAGE_RECIPIENTS'));
        $tpl->SetVariable('lbl_compose', $this::t('COMPOSE'));
        $tpl->SetVariable('lbl_back', Jaws::t('BACK'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

        $gridColumns = array();
        switch ($folder) {
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS:
                $title = $this::t('NOTIFICATIONS');

                $tpl->SetBlock('messages/filter_read');
                $tpl->SetVariable('lbl_all', Jaws::t('ALL'));
                $tpl->SetVariable('lbl_yes', Jaws::t('YESS'));
                $tpl->SetVariable('lbl_no', Jaws::t('NOO'));
                $tpl->SetVariable('lbl_read', $this::t('STATUS_READ'));
                $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
                $tpl->ParseBlock('messages/filter_read');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX:
                $title = $this::t('INBOX');

                $tpl->SetBlock('messages/filter_read');
                $tpl->SetVariable('lbl_all', Jaws::t('ALL'));
                $tpl->SetVariable('lbl_yes', Jaws::t('YESS'));
                $tpl->SetVariable('lbl_no', Jaws::t('NOO'));
                $tpl->SetVariable('lbl_read', $this::t('STATUS_READ'));
                $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
                $tpl->ParseBlock('messages/filter_read');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX:
                $title = $this::t('OUTBOX');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT:
                $title = $this::t('DRAFT');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED:
                $title = $this::t('ARCHIVED');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH:
                $title = $this::t('TRASH');
                break;
            default:
                $gridColumns[] = array(
                    'label' => $this::t('MESSAGE_FOLDER'),
                    'property' => 'folder',
                );
                $title = $this::t('ALL_MESSAGES');
        }

        $gridColumns[] = array(
            'label' => $this::t('MESSAGE_SUBJECT'),
            'property' => 'subject',
        );
        $gridColumns[] = array(
            'label' => $this::t('MESSAGE_ATTACHMENT'),
            'property' => 'have_attachment',
        );
        $gridColumns[] = array(
            'label' => $this::t('MESSAGE_FROM'),
            'property' => 'from_nickname',
        );
        $gridColumns[] = array(
            'label' => $this::t('MESSAGE_RECIPIENTS'),
            'property' => 'recipients',
        );
        $gridColumns[] = array(
            'label' => $this::t('MESSAGE_SEND_TIME'),
            'property' => 'send_time',
        );

        $this->gadget->define('grid', array('columns' => $gridColumns));

        $tpl->SetVariable('title', $title);
        // Menu navigation
        for ($i = 1; $i <= 6; $i++) {
            $url = $this->gadget->urlMap('Messages', array('folder' => $i));
            $options[$url] = array(
                'title'     => $this::t('MESSAGE_FOLDER_'. $i),
                'url'       => $url,
                'separator' => ($i == 2 || $i == 6)? true: false,
                'active'    => $i == $folder
            );
        }
        $url = $this->gadget->urlMap('Compose');
        $options[$url] = array(
            'title'     => $this::t('COMPOSE_MESSAGE'),
            'url'       => $url,
            'separator' => true,
        );
        $this->gadget->action->load('MenuNavigation')->navigation($tpl, $options);

        $tpl->SetVariable('folder', $folder);
        $tpl->SetVariable('filter', Jaws::t('SEARCH'));
        $tpl->SetVariable('lbl_page_item', $this::t('ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', Jaws::t('ACTIONS'));
        $tpl->SetVariable('lbl_no_action', Jaws::t('NO_ACTION'));

        $model = $this->gadget->model->load('Message');
        $user = $this->app->session->user->id;

        // Statistics
        $unreadNotifyCount = $model->GetMessagesStatistics(
            $user,
            PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS,
            array('read' => 'no'));
        $unreadInboxCount = $model->GetMessagesStatistics(
            $user,
            PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX,
            array('read' => 'no'));
        $draftCount = $model->GetMessagesStatistics(
            $user,
            PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT);
        $tpl->SetVariable('unread_notify_count', ($unreadNotifyCount > 0)? $unreadNotifyCount : '');
        $tpl->SetVariable('unread_inbox_count', ($unreadInboxCount > 0)? $unreadInboxCount : '');
        $tpl->SetVariable('draft_count', ($draftCount > 0)? $draftCount : '');

        $tpl->ParseBlock('messages');
        return $tpl->Get();
    }

    /**
     * Get messages list
     *
     * @access  public
     * @return  JSON
     */
    function GetMessages()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'folder:integer', 'filters:array'),
            'post'
        );
        $filters = $post['filters'];

        $model = $this->gadget->model->load('Message');
        $user = $this->app->session->user->id;
        $messages = $model->GetMessages($user, $post['folder'], $filters, $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($messages)) {
            return $this->gadget->session->response(
                $messages->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $objDate = Jaws_Date::getInstance();
        $date_format = $this->gadget->registry->fetch('date_format');
        if (count($messages) > 0) {
            foreach ($messages as &$message) {
                $message['folder'] = $this::t('MESSAGE_FOLDER_' . $message['folder']);
                $message['send_time'] = $objDate->Format($message['insert_time'], $date_format);
                $message['have_attachment'] = $message['attachments'] > 0;

                $recipientsStr = '';
                $messageInfo = $model->GetMessage($message['id']);
                if(!Jaws_Error::IsError($messageInfo)) {
                    if (is_array($messageInfo['users']) > 0) {
                        // user's profile
                        $user_url = $this->app->map->GetMappedURL(
                            'Users',
                            'Profile',
                            array('user' => $messageInfo['users'][0]['username']));
                        $recipientsStr = '<a href=' . $user_url . '>' . $messageInfo['users'][0]['nickname'] . '<a/>';
                        if (count($messageInfo['users']) > 1) {
                            $recipientsStr .= ' , ...';
                        }
                    }

                }
                $message['recipients'] = $recipientsStr;

            }
        }

        $messagesCount = $model->GetMessagesStatistics($user, $post['folder'], $filters);
        if (Jaws_Error::IsError($messagesCount)) {
            return $this->gadget->session->response(
                $messagesCount->GetMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $messagesCount,
                'records' => $messages
            )
        );
    }

    /**
     * Display a message Info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Message()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }

        $resType = Jaws::getInstance()->request->fetch('restype');
        if ($resType == 'json') {
            $this->SetActionMode('Message', 'standalone', 'normal');
        }

        $this->AjaxMe('index.js');
        $id = $this->gadget->request->fetch('id:integer');
        $date = Jaws_Date::getInstance();
        $user = $this->app->session->user->id;
        $model = $this->gadget->model->load('Message');
        $message = $model->GetMessage($id, true);

        if (empty($message)) {
            return Jaws_HTTPError::Get(404);
        }

        // Check permissions
        if (!($message['from'] == $user && $message['to'] == 0) && $message['to'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        if ($message['folder'] == PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX && $message['read'] == false) {
            $user = $this->app->session->user->id;
            $model->MarkMessages($id, true, $user);
        }

        $this->gadget->define('folder', $message['folder']);
        $this->gadget->define('folders', array(
            'inbox' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX,
            'draft' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT,
            'outbox' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX,
            'archived' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED,
            'trash' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH,
            'notifications' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS,
        ));

        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->template->load('Message.html');
        $tpl->SetBlock('message');
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('title', $this::t('MESSAGE_VIEW'));

        $tpl->SetBlock('message');

        $tpl->SetVariable('lbl_from', $this::t('MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', $this::t('MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', $this::t('MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', $this::t('MESSAGE_BODY'));

        $tpl->SetVariable('lbl_archive', $this::t('ARCHIVE'));
        $tpl->SetVariable('lbl_trash', $this::t('TRASH'));
        $tpl->SetVariable('lbl_restore_trash', $this::t('RESTORE_TRASH'));
        $tpl->SetVariable('lbl_delete', Jaws::t('DELETE'));
        $tpl->SetVariable('icon_archive', 'gadgets/PrivateMessage/Resources/images/archive-mini.png');
        $tpl->SetVariable('icon_restore_archive', 'gadgets/PrivateMessage/Resources/images/unarchive-mini.png');
        $tpl->SetVariable('icon_trash', 'gadgets/PrivateMessage/Resources/images/trash-mini.png');
        $tpl->SetVariable('icon_restore_trash', STOCK_REVERT);
        $tpl->SetVariable('icon_delete', STOCK_DELETE);

        $tpl->SetVariable('from', $message['from_nickname']);
        $tpl->SetVariable('username', $message['from_username']);
        $tpl->SetVariable('nickname', $message['from_nickname']);
        $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));
        $tpl->SetVariable('subject', $message['subject']);
        $tpl->SetVariable('body', $this->gadget->plugin->parse($message['body']));

        // user's avatar
        $tpl->SetVariable(
            'avatar',
            Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $message['from_username']))
        );

        // user's profile
        $tpl->SetVariable(
            'user_url',
            $this->app->map->GetMappedURL(
                'Users',
                'Profile',
                array('user' => $message['from_username'])
            )
        );

        if (!empty($message['attachments'])) {
            $tpl->SetBlock('message/attachment');
            $tpl->SetVariable('lbl_attachments', $this::t('MESSAGE_ATTACHMENTS'));
            foreach ($message['attachments'] as $file) {
                $tpl->SetBlock('message/attachment/file');
                $tpl->SetVariable('lbl_file_size', $this::t('MESSAGE_FILE_SIZE'));
                $tpl->SetVariable('file_name', $file['title']);
                $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));

                $tpl->SetVariable('file_download_link', $file['title']);
                $file_url = $this->gadget->urlMap('Attachment',
                    array(
                        'uid' => $user,
                        'mid' => $message['id'],
                        'aid' => $file['id'],
                    ));
                $tpl->SetVariable('file_download_link', $file_url);

                $tpl->ParseBlock('message/attachment/file');
            }
            $tpl->ParseBlock('message/attachment');
        }

        if ($message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT &&
            $message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH
        ) {
            if ($message['folder'] == PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX) {
                $tpl->SetBlock('message/reply');
                $tpl->SetVariable('reply_url', $this->gadget->urlMap('Compose', array('id' => $message['id'], 'reply' => 'true')));
                $tpl->SetVariable('icon_reply', 'gadgets/PrivateMessage/Resources/images/reply-mini.png');
                $tpl->SetVariable('reply', $this::t('REPLY'));
                $tpl->ParseBlock('message/reply');
            }

            $tpl->SetBlock('message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                'id' => $message['id'],
                'reply' => 'false')));
            $tpl->SetVariable('icon_forward', 'gadgets/PrivateMessage/Resources/images/forward-mini.png');
            $tpl->SetVariable('forward', $this::t('FORWARD'));
            $tpl->ParseBlock('message/forward');
        }

        $tpl->ParseBlock('message');
        $htmlUI = $tpl->Get();

        if ($resType == 'json') {
            return $this->gadget->session->response(
                '',
                RESPONSE_NOTICE,
                array(
                    'ui' => $htmlUI,
                    'message' => $message
                )
            );
        }
        return $htmlUI;
    }

    /**
     * Change message read status
     *
     * @access  public
     * @return  void
     */
    function ChangeMessageRead()
    {
        $post = $this->gadget->request->fetch(array('ids:array', 'read:boolean'), 'post');
        $user = $this->app->session->user->id;

        $res = $this->gadget->model->load('Message')->MarkMessages($post['ids'], $post['read'], $user);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_MESSAGE_READ_STATUS_NOT_CHANGED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('MESSAGE_READ_MESSAGE_STATUS_CHANGED'), RESPONSE_NOTICE);
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
        $post = $this->gadget->request->fetch(array('ids:array', 'archive:boolean'), 'post');

        $res = $this->gadget->model->load('Message')->ArchiveMessage(
            $post['ids'],
            $this->app->session->user->id,
            $post['archive']
        );
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(
                $post['archive'] ? $this::t('ERROR_MESSAGE_NOT_ARCHIVED') :
                    $this::t('ERROR_MESSAGE_NOT_UNARCHIVED'),
                RESPONSE_ERROR
            );
        }
        return $this->gadget->session->response(
            $post['archive'] ? $this::t('MESSAGE_ARCHIVED') : $this::t('MESSAGE_UNARCHIVED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Trash message
     *
     * @access  public
     * @return  void
     */
    function TrashMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');
        $ids = $this->gadget->request->fetch('ids:array', 'post');

        $res = $this->gadget->model->load('Message')->TrashMessage($ids, $this->app->session->user->id, true);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_MESSAGE_NOT_TRASHED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('MESSAGE_TRASHED'), RESPONSE_NOTICE);
    }

    /**
     * Restore Trash message
     *
     * @access  public
     * @return  void
     */
    function RestoreTrashMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');
        $ids = $this->gadget->request->fetch('ids:array', 'post');

        $res = $this->gadget->model->load('Message')->TrashMessage($ids, $this->app->session->user->id, false);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_MESSAGE_NOT_TRASH_RESTORED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('MESSAGE_TRASH_RESTORED'), RESPONSE_NOTICE);
    }

    /**
     * Delete message permanently
     *
     * @access  public
     * @return  void
     */
    function DeleteMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');
        $ids = $this->gadget->request->fetch('ids:array', 'post');

        $res = $this->gadget->model->load('Message')->DeleteMessage($ids, $this->app->session->user->id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('MESSAGE_NOT_DELETED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('MESSAGE_DELETED'), RESPONSE_NOTICE);
   }
}