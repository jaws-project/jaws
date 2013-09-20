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
class PrivateMessage_Actions_Compose extends Jaws_Gadget_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('ComposeMessage');
        $this->AjaxMe('site_script.js');
        $get = jaws()->request->fetch(array('id', 'reply'), 'get');
        $id = $get['id'];

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $tpl = $this->gadget->loadTemplate('Compose.html');
        $tpl->SetBlock('compose');
        $body_value = "";
        if (!empty($id) && $id > 0) {
            $message = $model->GetMessage($id, true, false);
            $tpl->SetVariable('parent', $message['parent']);

            // edit draft
            if (empty($get['reply'])) {
                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
                $tpl->SetVariable('recipient_users', $message['recipient_users']);
                $tpl->SetVariable('recipient_groups', $message['recipient_groups']);
                $tpl->SetVariable('id', $id);
                $body_value = $message['body'];
                $tpl->SetVariable('subject', $message['subject']);

                if (!empty($message['attachments'])) {
                    $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                    $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI($id));
                }

            // reply a message
            } else if (!empty($get['reply']) && $get['reply'] == 'true') {
                $tpl->SetVariable('parent', $id);
                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_REPLY'));
                $tpl->SetVariable('recipient_users', $message['user']);
                $tpl->SetVariable('subject', _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']));
            // forward a message
            } else if (!empty($get['reply']) && $get['reply'] == 'false') {
                $tpl->SetVariable('title', _t('PRIVATEMESSAGE_FORWARD_MESSAGE'));
                $body_value = $message['body'];
                $tpl->SetVariable('subject', $message['subject']);

                if (!empty($message['attachments'])) {
                    $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
                    $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI($id));
                }
            }
        } else {
            $tpl->SetVariable('title', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
            $tpl->SetVariable('attachment_ui', $this->GetMessageAttachmentUI());
        }

        $body =& $GLOBALS['app']->LoadEditor('PrivateMessage', 'body', $body_value);
        $body->setID('body');
        $body->TextArea->SetStyle('width: 99%;');
        $body->SetWidth('100%');
        $tpl->SetVariable('body', $body->Get());


        // User List
        $bUsers =& Piwi::CreateWidget('Combo', 'recipient_users');
        $bUsers->SetID('recipient_users');
        $bUsers->AddOption('None user', '');
        $bUsers->setMultiple(true);
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $users = $userModel->GetUsers();
        foreach($users as $user) {
            $bUsers->AddOption($user['nickname'], $user['id']);
        }
        $tpl->SetVariable('recipient_users_opt', $bUsers->Get());

        // Group List
        $bGroups =& Piwi::CreateWidget('Combo', 'recipient_groups');
        $bGroups->SetID('recipient_groups');
        $bGroups->AddOption('None group', '');
        $bGroups->setMultiple(true);
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $groups = $userModel->GetGroups(true);
        foreach($groups as $group) {
            $bGroups->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('recipient_groups_opt', $bGroups->Get());



        $tpl->SetVariable('lbl_recipient_users', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_USERS'));
        $tpl->SetVariable('lbl_recipient_groups', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_GROUPS'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));
        $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
        $tpl->SetVariable('lbl_save_draft', _t('PRIVATEMESSAGE_SAVE_DRAFT'));
        $tpl->SetVariable('lbl_send', _t('PRIVATEMESSAGE_SEND'));
        $tpl->SetVariable('lbl_back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->SetVariable('lbl_file', _t('PRIVATEMESSAGE_FILE'));
        $tpl->SetVariable('lbl_add_file', _t('PRIVATEMESSAGE_ADD_ANOTHER_FILE'));

        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

        $tpl->SetVariable('icon_add', STOCK_ADD);
        $tpl->SetVariable('icon_remove', STOCK_REMOVE);

        $tpl->ParseBlock('compose');
        return $tpl->Get();
    }

    /**
     * Get Message Attachment UI
     *
     * @access  public
     * @param   integer $message_id   Message Id
     * @return  string XHTML template content
     */
    function GetMessageAttachmentUI($message_id = null)
    {
        $this->gadget->CheckPermission('ComposeMessage');

        if(empty($message_id)) {
            $message_id = jaws()->request->fetch('id', 'post');
        }

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $tpl = $this->gadget->loadTemplate('Compose.html');
        $tpl->SetBlock('attachments');

        if (!empty($message_id)) {
            $message = $model->GetMessage($message_id, true, false);

            foreach ($message['attachments'] as $file) {
                $tpl->SetBlock('attachments/file');
                $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                $tpl->SetVariable('file_name', $file['title']);
                $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));
                $tpl->SetVariable('file_id', $file['id']);

                $tpl->SetVariable('file_download_link', $file['title']);
                $file_url = $this->gadget->urlMap('Attachment',
                    array(
                        'uid' => $message['user'],
                        'mid' => $message_id,
                        'aid' => $file['id'],
                    ));
                $tpl->SetVariable('file_download_link', $file_url);

                $tpl->ParseBlock('attachments/file');
            }
        }
        $tpl->ParseBlock('attachments');

        return $tpl->Get();
    }

    /**
     * Compose a message
     *
     * @access  public
     * @return  void
     */
    function ComposeMessage()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
        $this->gadget->CheckPermission('ComposeMessage');

        $post = jaws()->request->fetch(array('id', 'parent', 'published', 'recipient_users', 'recipient_groups',
                                             'subject', 'body', 'attachments:array'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');


        $message_id = $model->ComposeMessage($user, $post);
        $url = $this->gadget->urlMap('Outbox');
        if (is_numeric($message_id) && $message_id > 0) {
            if($post['published']==true) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_MESSAGE_SEND'),
                    'PrivateMessage.Message',
                    RESPONSE_NOTICE
                );
            }
            return $GLOBALS['app']->Session->GetResponse(
                _t('PRIVATEMESSAGE_DRAFT_SAVED'),
                RESPONSE_NOTICE,
                array('published' => $post['published'], 'url' => $url, 'message_id' => $message_id));

        } else {
            if($post['published']==true) {
                return $GLOBALS['app']->Session->GetResponse(
                                        _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_SEND'), RESPONSE_ERROR);
            }
            return $GLOBALS['app']->Session->GetResponse(
                _t('PRIVATEMESSAGE_DRAFT_NOT_SAVED'),
                RESPONSE_ERROR);

        }
    }

}