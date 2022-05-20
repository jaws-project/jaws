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
            $folders[$i] = _t('PRIVATEMESSAGE_MESSAGE_FOLDER_'. $i);
        }

        $result[] = array(
            'title' => _t('PRIVATEMESSAGE_MESSAGE_FOLDER'),
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
        $this->gadget->define('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
        $this->gadget->define('lbl_mark_as_read', _t('PRIVATEMESSAGE_MARK_AS_READ'));
        $this->gadget->define('lbl_mark_as_unread', _t('PRIVATEMESSAGE_MARK_AS_UNREAD'));
        $this->gadget->define('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
        $this->gadget->define('lbl_restore_trash', _t('PRIVATEMESSAGE_RESTORE_TRASH'));
        $this->gadget->define('lbl_unarchive', _t('PRIVATEMESSAGE_UNARCHIVE'));
        $this->gadget->define('lbl_view_message', _t('PRIVATEMESSAGE_MESSAGE_VIEW'));
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
        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_recipients', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));
        $tpl->SetVariable('lbl_compose', _t('PRIVATEMESSAGE_COMPOSE'));
        $tpl->SetVariable('lbl_back', Jaws::t('BACK'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

        $gridColumns = array();
        switch ($folder) {
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS:
                $title = _t('PRIVATEMESSAGE_NOTIFICATIONS');

                $tpl->SetBlock('messages/filter_read');
                $tpl->SetVariable('lbl_all', Jaws::t('ALL'));
                $tpl->SetVariable('lbl_yes', Jaws::t('YES'));
                $tpl->SetVariable('lbl_no', Jaws::t('NO'));
                $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
                $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
                $tpl->ParseBlock('messages/filter_read');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX:
                $title = _t('PRIVATEMESSAGE_INBOX');

                $tpl->SetBlock('messages/filter_read');
                $tpl->SetVariable('lbl_all', Jaws::t('ALL'));
                $tpl->SetVariable('lbl_yes', Jaws::t('YES'));
                $tpl->SetVariable('lbl_no', Jaws::t('NO'));
                $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
                $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
                $tpl->ParseBlock('messages/filter_read');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX:
                $title = _t('PRIVATEMESSAGE_OUTBOX');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT:
                $title = _t('PRIVATEMESSAGE_DRAFT');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED:
                $title = _t('PRIVATEMESSAGE_ARCHIVED');
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH:
                $title = _t('PRIVATEMESSAGE_TRASH');
                break;
            default:
                $gridColumns[] = array(
                    'label' => _t('PRIVATEMESSAGE_MESSAGE_FOLDER'),
                    'property' => 'folder',
                );
                $title = _t('PRIVATEMESSAGE_ALL_MESSAGES');
        }

        $gridColumns[] = array(
            'label' => _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'),
            'property' => 'subject',
        );
        $gridColumns[] = array(
            'label' => _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'),
            'property' => 'have_attachment',
        );
        $gridColumns[] = array(
            'label' => _t('PRIVATEMESSAGE_MESSAGE_FROM'),
            'property' => 'from_nickname',
        );
        $gridColumns[] = array(
            'label' => _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'),
            'property' => 'recipients',
        );
        $gridColumns[] = array(
            'label' => _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'),
            'property' => 'send_time',
        );

        $this->gadget->define('grid', array('columns' => $gridColumns));

        $tpl->SetVariable('title', $title);
        // Menu navigation
        for ($i = 1; $i <= 6; $i++) {
            $url = $this->gadget->urlMap('Messages', array('folder' => $i));
            $options[$url] = array(
                'title'     => _t('PRIVATEMESSAGE_MESSAGE_FOLDER_'. $i),
                'url'       => $url,
                'separator' => ($i == 2 || $i == 6)? true: false,
                'active'    => $i == $folder
            );
        }
        $url = $this->gadget->urlMap('Compose');
        $options[$url] = array(
            'title'     => _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'),
            'url'       => $url,
            'separator' => true,
        );
        $this->gadget->action->load('MenuNavigation')->navigation($tpl, $options);

        $tpl->SetVariable('folder', $folder);
        $tpl->SetVariable('filter', Jaws::t('SEARCH'));
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));
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
                $message['folder'] = _t('PRIVATEMESSAGE_MESSAGE_FOLDER_' . $message['folder']);
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
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_MESSAGE_VIEW'));

        $tpl->SetBlock('message');

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));

        $tpl->SetVariable('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
        $tpl->SetVariable('lbl_trash', _t('PRIVATEMESSAGE_TRASH'));
        $tpl->SetVariable('lbl_restore_trash', _t('PRIVATEMESSAGE_RESTORE_TRASH'));
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
            $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
            foreach ($message['attachments'] as $file) {
                $tpl->SetBlock('message/attachment/file');
                $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
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
                $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
                $tpl->ParseBlock('message/reply');
            }

            $tpl->SetBlock('message/forward');
            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                'id' => $message['id'],
                'reply' => 'false')));
            $tpl->SetVariable('icon_forward', 'gadgets/PrivateMessage/Resources/images/forward-mini.png');
            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));
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
            return $this->gadget->session->response(_t('PRIVATEMESSAGE_ERROR_MESSAGE_READ_STATUS_NOT_CHANGED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('PRIVATEMESSAGE_MESSAGE_READ_MESSAGE_STATUS_CHANGED'), RESPONSE_NOTICE);
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
                $post['archive'] ? _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_ARCHIVED') :
                    _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_UNARCHIVED'),
                RESPONSE_ERROR
            );
        }
        return $this->gadget->session->response(
            $post['archive'] ? _t('PRIVATEMESSAGE_MESSAGE_ARCHIVED') : _t('PRIVATEMESSAGE_MESSAGE_UNARCHIVED'),
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
            return $this->gadget->session->response(_t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_TRASHED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('PRIVATEMESSAGE_MESSAGE_TRASHED'), RESPONSE_NOTICE);
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
            return $this->gadget->session->response(_t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_TRASH_RESTORED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('PRIVATEMESSAGE_MESSAGE_TRASH_RESTORED'), RESPONSE_NOTICE);
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
            return $this->gadget->session->response(_t('PRIVATEMESSAGE_MESSAGE_NOT_DELETED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('PRIVATEMESSAGE_MESSAGE_DELETED'), RESPONSE_NOTICE);
   }
}