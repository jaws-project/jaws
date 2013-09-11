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
class PrivateMessage_Actions_Reply extends Jaws_Gadget_HTML
{
    /**
     * Display Reply message form
     *
     * @access  public
     * @return  void
     */
    function Reply()
    {
        $id = jaws()->request->fetch('id', 'get');
        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $usrModel = new Jaws_User;
        $message = $model->GetMessage($id);

        $tpl = $this->gadget->loadTemplate('Reply.html');
        $tpl->SetBlock('reply');
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_REPLY'));

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

        $post = jaws()->request->fetch(array('id', 'body'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $res = $model->ReplyMessage($post['id'], $user, $post['body']);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('ViewMessage', array('id' => $post['id'])));
    }
}