<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Compose extends PrivateMessage_Actions_Default
{
    /**
     * Display Compose page
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Compose()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(401);
        }

        $this->gadget->CheckPermission('SendMessage');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $this->AjaxMe('index.js');
        $data = jaws()->request->fetch(array('id', 'user', 'reply', 'users:array'));
        $id = $data['id'];

        $userModel = new Jaws_User();
        $model = $this->gadget->model->load('Message');
        $tpl = $this->gadget->template->load('Compose.html');
        $tpl->SetBlock('compose');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Compose'));

        $body_value = "";
        $recipient_users = array();
        $recipient_friends = array();
        $show_recipient = true;
        // draft or reply
        if (!empty($id)) {
            $message = $model->GetMessage($id, true, false);

            // Check permissions
            if (!($message['from'] == $user && $message['to'] == 0) && $message['to'] != $user) {
                return Jaws_HTTPError::Get(403);
            }

            // open draft
            if (empty($data['reply'])) {

                // Check draft status
                if ($message['folder'] != PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT) {
                    return Jaws_HTTPError::Get(404);
                }

                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
                $tpl->SetVariable('id', $id);
                $recipient_users = array_map('intval', explode(',', $message['recipient_users']));
                $recipient_friends = array_map('intval', explode(',', $message['recipient_friends']));
                $body_value = $message['body'];
                $tpl->SetVariable('subject', $message['subject']);
                $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI($id));

            // reply a message
            } else if (!empty($data['reply']) && $data['reply'] == 'true') {
                $date_format = $this->gadget->registry->fetch('date_format');
                $date = Jaws_Date::getInstance();
                $usrModel = new Jaws_User;
                $show_recipient = false;
                $body_value = '[quote]' . $message['body'] . "[/quote]\r\n";

                // show parent message
                $tpl->SetBlock('compose/parent_message');
                $tpl->SetBlock('compose/parent_message/message');

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
                    $tpl->SetBlock('compose/parent_message/message/attachment');
                    $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                    foreach($message['attachments'] as $file) {
                        $tpl->SetBlock('compose/parent_message/message/attachment/file');
                        $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                        $tpl->SetVariable('file_name', $file['title']);
                        $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));

                        $tpl->SetVariable('file_download_link', $file['title']);
                        $file_url = $this->gadget->urlMap('Attachment',
                            array(
                                'uid' => $message['to'],
                                'mid' => $message['id'],
                                'aid' => $file['id'],
                            ));
                        $tpl->SetVariable('file_download_link', $file_url);

                        $tpl->ParseBlock('compose/parent_message/message/attachment/file');
                    }
                    $tpl->ParseBlock('compose/parent_message/message/attachment');
                }

                $tpl->ParseBlock('compose/parent_message/message');
                $tpl->ParseBlock('compose/parent_message');

                //
                $tpl->SetVariable('parent', $id);
                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_REPLY'));
                $tpl->SetVariable('subject', _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']));
                $tpl->SetVariable('recipient_user', $message['from']);
                $recipient_users = array($message['from']);

                $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI($id, false));

            // forward a message
            } else if (!empty($data['reply']) && $data['reply'] == 'false') {
                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_FORWARD_MESSAGE'));
                $body_value = $message['body'];
                $tpl->SetVariable('subject', _t('PRIVATEMESSAGE_FORWARD_ABBREVIATION') . ' ' .$message['subject']);

                $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI($id));
            }
        } else {
            if (!empty($data['users'])) {
                $recipient_users = $data['users'];
            } else if (!empty($data['user'])) {
                $recipient_users = array($data['user']);
            }

            $tpl->SetVariable('title', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
            $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI());
        }

        $body =& $GLOBALS['app']->LoadEditor('PrivateMessage', 'body', $body_value);
        $body->TextArea->SetRows(8);
        $body->setID('body');
        $body->SetWidth('100%');
        $tpl->SetVariable('body', $body->Get());

        if ($show_recipient) {
            $tpl->SetBlock('compose/recipients');
            $tpl->SetVariable('lbl_recipient', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));
            $tpl->SetVariable('lbl_recipient_users', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_USERS'));
            $tpl->SetVariable('delete-icon', STOCK_DELETE);

            if (!empty($recipient_users)) {
                foreach ($recipient_users as $userId) {
                    $user_info = $userModel->GetUser($userId, true);
                    $tpl->SetBlock('compose/recipients/user');
                    $tpl->SetVariable('title', $user_info['nickname']);
                    $tpl->SetVariable('value', $user_info['id']);
                    $tpl->ParseBlock('compose/recipients/user');
                }
            }

            // Friends List
            $groups = $userModel->GetGroups($user, true);
            if (!Jaws_Error::IsError($groups) && count($groups) > 0) {
                foreach ($groups as $group) {
                    $tpl->SetBlock('compose/recipients/friend');
                    $tpl->SetVariable('value', $group['id']);
                    $tpl->SetVariable('title', $group['title']);

                    $tpl->SetVariable('checked', '');
                    if (in_array($group['id'], $recipient_friends)) {
                        $tpl->SetVariable('checked', 'checked');
                    }
                    $tpl->ParseBlock('compose/recipients/friend');
                }
            }
            $tpl->SetVariable('lbl_recipient_friends', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_FRIENDS'));
            $tpl->ParseBlock('compose/recipients');
        } else {
            $tpl->SetBlock('compose/recipient');
            $tpl->SetVariable('lbl_recipient', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));
            $user_info = $userModel->GetUser($recipient_users[0]);

            // user's profile
            $tpl->SetVariable(
                'recipient_user_url',
                $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'Profile',
                    array('user' => $user_info['username'])
                )
            );
            $tpl->SetVariable('recipient_user', $user_info['nickname']);

            $tpl->ParseBlock('compose/recipient');
        }

        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));
        $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
        $tpl->SetVariable('lbl_save_draft', _t('PRIVATEMESSAGE_SAVE_DRAFT'));
        $tpl->SetVariable('lbl_send', _t('PRIVATEMESSAGE_SEND'));
        $tpl->SetVariable('lbl_back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->SetVariable('lbl_file', _t('PRIVATEMESSAGE_FILE'));
        $tpl->SetVariable('lbl_add_file', _t('PRIVATEMESSAGE_ADD_ANOTHER_FILE'));

        $tpl->SetVariable('back_url', $this->gadget->urlMap(
            'Messages',
            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX)));

        $tpl->SetVariable('icon_add', STOCK_ADD);
        $tpl->SetVariable('icon_remove', STOCK_REMOVE);

        $tpl->ParseBlock('compose');
        return $tpl->Get();
    }

    /**
     * Get Message Attachment UI
     *
     * @access  public
     * @param   integer $message_id         Message Id
     * @param   bool    $loadAttachments    Load and show message attachments (parent message attachments)
     * @return  string XHTML template content
     */
    function GetMessageAttachmentUI($message_id = null, $loadAttachments = true)
    {
        $this->gadget->CheckPermission('SendMessage');

        if(empty($message_id)) {
            $message_id = jaws()->request->fetch('id', 'post');
        }

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Message');
        $tpl = $this->gadget->template->load('Compose.html');
        $tpl->SetBlock('attachments');

        if ($loadAttachments && !empty($message_id)) {
            $message = $model->GetMessage($message_id, true, false);

            if (isset($message['attachments'])) {
                foreach ($message['attachments'] as $file) {
                    $tpl->SetBlock('attachments/file');
                    $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                    $tpl->SetVariable('file_name', $file['title']);
                    $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));
                    $tpl->SetVariable('file_id', $file['id']);

                    $tpl->SetVariable('file_download_link', $file['title']);
                    $file_url = $this->gadget->urlMap('Attachment',
                        array(
                            'uid' => $user,
                            'mid' => $message_id,
                            'aid' => $file['id'],
                        ));
                    $tpl->SetVariable('file_download_link', $file_url);

                    $tpl->ParseBlock('attachments/file');
                }
            }
        }
        $tpl->ParseBlock('attachments');

        return $tpl->Get();
    }

    /**
     * Send a message
     *
     * @access  public
     * @return  void
     */
    function SendMessage()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(401);
        }
        $this->gadget->CheckPermission('SendMessage');

        $post = jaws()->request->fetch(
            array(
                'id', 'recipient_users', 'recipient_friends', 'folder',
                'subject', 'body', 'attachments:array', 'is_draft:bool'
            ),
            'post'
        );
        $post['body'] = jaws()->request->strip_crlf($post['body']);

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Message');

        if (empty($post['folder'])) {
            $post['folder'] = $post['is_draft']?
                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT:
                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX;
        }
        $message_id = $model->SendMessage($user, $post);

        $url = $this->gadget->urlMap('Messages', array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX));
        if (Jaws_Error::IsError($message_id)) {
            $GLOBALS['app']->Session->PushResponse(
                $message_id->getMessage(),
                'PrivateMessage.Compose',
                RESPONSE_ERROR
            );
        } else {
            if ($post['is_draft']) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_DRAFT_SAVED'),
                    'PrivateMessage.Compose',
                    RESPONSE_NOTICE,
                    array('is_draft' => true, 'message_id' => $message_id)
                );
            } else {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_MESSAGE_SEND'),
                    'PrivateMessage.Compose',
                    RESPONSE_NOTICE,
                    array('url' => $url)
                );
            }
        }

        Jaws_Header::Location($url, 'PrivateMessage.Compose');
    }

    /**
     * Search users
     *
     * @access  public
     * @return  void
     */
    function GetUsers()
    {
        $term = jaws()->request->fetch('term', 'post');
        $userModel = new Jaws_User();
        $users = $userModel->GetUsers(false, null, null, $term, 'nickname', 5);
        return $users;
    }
}