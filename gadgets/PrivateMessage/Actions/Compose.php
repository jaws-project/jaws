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
     * @return  void
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
                    foreach ($message['attachments'] as $file) {
                        $tpl->SetBlock('compose/file');
                        $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                        $tpl->SetVariable('file_name', $file['title']);
                        $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));
                        $tpl->SetVariable('file_id', $file['id']);

                        $tpl->SetVariable('file_download_link', $file['title']);
                        $file_url = $this->gadget->urlMap('Attachment',
                            array(
                                'uid' => $message['user'],
                                'mid' => $id,
                                'aid' => $file['id'],
                            ));
                        $tpl->SetVariable('file_download_link', $file_url);

                        $tpl->ParseBlock('compose/file');
                    }
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
                    foreach ($message['attachments'] as $file) {
                        $tpl->SetBlock('compose/file');
                        $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                        $tpl->SetVariable('file_name', $file['title']);
                        $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['filesize']));
                        $tpl->SetVariable('file_id', $file['id']);

                        $tpl->SetVariable('file_download_link', $file['title']);
                        $file_url = $this->gadget->urlMap('Attachment',
                            array(
                                'uid' => $message['user'],
                                'mid' => $id,
                                'aid' => $file['id'],
                            ));
                        $tpl->SetVariable('file_download_link', $file_url);

                        $tpl->ParseBlock('compose/file');
                    }
                }
            }
        } else {
            $tpl->SetVariable('title', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
        }

        $body =& $GLOBALS['app']->LoadEditor('PrivateMessage', 'body', $body_value);
        $body->setID('body');
        $body->TextArea->SetStyle('width: 99%;');
        $body->SetWidth('100%');
        $tpl->SetVariable('body', $body->Get());

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

        $tpl->ParseBlock('compose');
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

        $attachments = array();
        $post = jaws()->request->fetch(array('id', 'parent', 'recipient_users', 'recipient_groups', 'subject',
                                             'body', 'selected_files:array'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');

        $pm_dir = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $user . DIRECTORY_SEPARATOR;
        if (!file_exists($pm_dir)) {
            if (!Jaws_Utils::mkdir($pm_dir)) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('GLOBAL_ERROR_FAILED_CREATING_DIR', $pm_dir),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );

                Jaws_Header::Location($this->gadget->urlMap('Inbox'));
            }
        }

        // detect message have attachment(s)?
        if (!empty($_FILES['file1']['name'])) {
            $files = Jaws_Utils::UploadFiles(
                $_FILES,
                $pm_dir,
                '',
                'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                null
            );

            if (Jaws_Error::IsError($files)) {
                $GLOBALS['app']->Session->PushResponse(
                    $files->GetMessage(),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            } else if ($files === false || count($files) < 1) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('PRIVATEMESSAGE_ERROR_NO_FILE_UPLOADED'),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            } else {
                for ($i = 1; $i <= count($files); $i++) {
                    if (!isset($files['file' . $i])) {
                        continue;
                    }
                    $user_filename  = $files['file' . $i][0]['user_filename'];
                    $host_filename  = $files['file' . $i][0]['host_filename'];
                    $host_filesize  = $files['file' . $i][0]['host_filesize'];
                    $host_filetype  = $files['file' . $i][0]['host_filetype'];
                    if (!empty($host_filename)) {
                        $attachments[] = array(
                            'title' => $user_filename,
                            'filename' => $host_filename,
                            'filesize' => $host_filesize,
                            'filetype' => $host_filetype,
                        );
                    }
                }
            }

        }

        // check forward with pre attachments
        if (!empty($post['selected_files'])) {
            $aModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Attachment');

            foreach ($post['selected_files'] as $attachment_id) {
                if ($attachment_id < 1) {
                    continue;
                }
                $attachment_info = $aModel->GetMessageAttachment($attachment_id);
                $message_info = $model->GetMessage($attachment_info['message']);
                $filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $message_info['user'] . DIRECTORY_SEPARATOR .
                    $attachment_info['filename'];

                $host_filename = Jaws_Utils::RandomText(15, true, false, true) . '.' . $files['extension'];
                $new_filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $user . DIRECTORY_SEPARATOR .
                    $host_filename;
                $cres = Jaws_Utils::copy($filepath, $new_filepath);
                if ($cres) {
                    $attachments[] = array(
                        'title' => $attachment_info['title'],
                        'filename' => $host_filename,
                        'filesize' => $attachment_info['filesize'],
                        'filetype' => $attachment_info['filetype'],
                    );
                }
            }
        }

        $post['published'] = true;
        $res = $model->ComposeMessage($user, $post, $attachments);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->GetMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}