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
class PrivateMessage_Actions_Message extends Jaws_Gadget_Action
{
    /**
     * Display a message history
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MessageHistory()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = jaws()->request->fetch('id', 'get');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $date = $GLOBALS['app']->loadDate();
        $model = $this->gadget->loadModel('Message');
        $usrModel = new Jaws_User;
        $messages = array();
        $message = $model->GetMessage($id, false, false);

        // Check permissions
        $messageRecipients = $model->GetMessageRecipients($id, true, false);
        if (!in_array('0', $messageRecipients) && !in_array($user, $messageRecipients) && $message['user'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        $model->GetParentMessages($message['id'], true, $messages);
        if(empty($messages)) {
            return false;
        }
        $messages = array_reverse($messages, true);

        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->loadTemplate('MessageHistory.html');
        $tpl->SetBlock('history');

        $i = 0;
        foreach ($messages as $message) {
            $i++;
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
            $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));
            $tpl->SetVariable('subject', $message['subject']);
            $tpl->SetVariable('body', $this->gadget->ParseText($message['body'], 'PrivateMessage', 'index'));

            if ($i < count($messages)) {
                $tpl->SetBlock('history/message/splitter');
                $tpl->ParseBlock('history/message/splitter');
            }

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


            if ($message['user'] != $user) {
                $tpl->SetBlock('history/message/reply');
                $tpl->SetVariable('reply_url', $this->gadget->urlMap('Compose', array('id' => $message['id'], 'reply' => 'true')));
                $tpl->SetVariable('icon_reply', 'gadgets/PrivateMessage/Resources/images/reply-mini.png');
                $tpl->SetVariable('reply', _t('PRIVATEMESSAGE_REPLY'));
                $tpl->ParseBlock('history/message/reply');
            }

            $tpl->SetVariable('forward_url', $this->gadget->urlMap('Compose', array(
                'id' => $message['id'],
                'reply'=>'false')));

            $tpl->SetVariable('icon_forward',   'gadgets/PrivateMessage/Resources/images/forward-mini.png');

            $tpl->SetVariable('forward', _t('PRIVATEMESSAGE_FORWARD'));

            $tpl->ParseBlock('history/message');
        }

        $tpl->SetVariable('back',       _t('PRIVATEMESSAGE_BACK'));
        $tpl->SetVariable('back_url',   $this->gadget->urlMap('Inbox'));
        $tpl->SetVariable('icon_back',  'gadgets/PrivateMessage/Resources/images/back-mini.png');

        $tpl->ParseBlock('history');
        return $tpl->Get();
    }


}