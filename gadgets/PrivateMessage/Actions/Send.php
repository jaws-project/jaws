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
class PrivateMessage_Actions_Send extends Jaws_Gadget_HTML
{
    /**
     * Display Send page
     *
     * @access  public
     * @return  void
     */
    function Send()
    {
        $this->gadget->CheckPermission('SendMessage');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/mootools/core.js');

//        $date = $GLOBALS['app']->loadDate();
//        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Send');
//        $usrModel = new Jaws_User;

        $tpl = $this->gadget->loadTemplate('Send.html');
        $tpl->SetBlock('send');

        $tpl->SetVariable('lbl_recipient_users', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_USERS'));
        $tpl->SetVariable('lbl_recipient_groups', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_GROUPS'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_body', _t('PRIVATEMESSAGE_MESSAGE_BODY'));
        $tpl->SetVariable('lbl_attachments', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENTS'));
        $tpl->SetVariable('lbl_send', _t('PRIVATEMESSAGE_SEND'));
        $tpl->SetVariable('lbl_back', _t('PRIVATEMESSAGE_BACK'));
        $tpl->SetVariable('lbl_file', _t('PRIVATEMESSAGE_FILE'));
        $tpl->SetVariable('lbl_add_file', _t('PRIVATEMESSAGE_ADD_ANOTHER_FILE'));
        $tpl->SetVariable('back_url', $this->gadget->urlMap('Inbox'));

        $tpl->SetVariable('icon_add', STOCK_ADD);

        $tpl->ParseBlock('send');
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
        $this->gadget->CheckPermission('SendMessage');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('recipient_users', 'recipient_groups', 'subject', 'body'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = Jaws_Utils::UploadFiles($_FILES, JAWS_DATA . 'pm/' . $user . '/',
            'jpg,gif,swf,png,jpeg,bmp,svg,zip,rar,doc,docx,xls,xlsx');

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PRIVATEMESSAGE_ERROR_NO_FILE_UPLOADED'), RESPONSE_ERROR);
        } else {
//            $post['image'] = $res['image'][0]['host_filename'];
            $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
            $res = $model->SendMessage($user, $post);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushResponse(
                    $res->GetMessage(),
                    'PrivateMessage.Message',
                    RESPONSE_ERROR
                );
            }
        }

        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}