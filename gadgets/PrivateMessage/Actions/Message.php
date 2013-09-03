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
     * @return  void
     */
    function ViewMessage()
    {
        $id = jaws()->request->get('id', 'get');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id);

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model->MarkMessages($id, PrivateMessage_Info::PM_STATUS_READ, $user);

        $tpl = $this->gadget->loadTemplate('Message.html');
        $tpl->SetBlock('message');

        $tpl->SetVariable('id', $id);

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
                $tpl->SetVariable('lbl_hints_count', _t('PRIVATEMESSAGE_FILE_HINTS_COUNT'));
                $tpl->SetVariable('lbl_file_size', _t('PRIVATEMESSAGE_MESSAGE_FILE_SIZE'));
                $tpl->SetVariable('file_name', $file['user_filename']);
                $tpl->SetVariable('file_size', Jaws_Utils::FormatSize($file['file_size']));
                $tpl->SetVariable('hints_count', $file['hints_count']);

                $tpl->SetVariable('file_download_link', $file['user_filename']);
                $file_url = $this->gadget->urlMap('Attachment',
                                                  array(
                                                      'uid' => $message['from'],
                                                      'mid' => $id,
                                                      'aid' => $file['id'],
                                                  ));
                $tpl->SetVariable('file_download_link', $file_url);

                $tpl->ParseBlock('message/attachment/file');
            }
            $tpl->ParseBlock('message/attachment');
        }

        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

        $tpl->SetVariable('icon_back',      STOCK_LEFT);
        $tpl->SetVariable('icon_forward',   STOCK_RIGHT);
        $tpl->SetVariable('icon_reply',     STOCK_JUMP_TO);
        $tpl->SetVariable('icon_delete',    STOCK_DELETE);

        $tpl->ParseBlock('message');
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

        $id = jaws()->request->get('id', 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->DeleteMessage($id, $user);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->GetMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

    /**
     * Display Reply message form
     *
     * @access  public
     * @return  void
     */
    function Reply()
    {
        $id = jaws()->request->get('id', 'post');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id);

        $tpl = $this->gadget->loadTemplate('Reply.html');
        $tpl->SetBlock('reply');

        $tpl->SetVariable('id', $id);

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));
        $tpl->SetVariable('lbl_reply', _t('PRIVATEMESSAGE_REPLY'));
        $tpl->SetVariable('lbl_send', _t('PRIVATEMESSAGE_SEND'));

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

        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

        $tpl->ParseBlock('reply');
        return $tpl->Get();
    }

    /**
     * Reply a message
     *
     * @access  public
     * @return  void
     */
    function ReplyMessage()
    {
        $this->gadget->CheckPermission('ReplyMessage');

        $post = jaws()->request->get(array('id', 'reply'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->ReplyMessage($post['id'], $user, $post['reply']);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->GetMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('ViewMessage', array('id' => $post['id'])));
    }

}