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
class PrivateMessage_Actions_Inbox extends Jaws_Gadget_HTML
{
    /**
     * Display Navigation Area
     *
     * @access  public
     * @return  void
     */
    function Inbox()
    {
        $tpl = $this->gadget->loadTemplate('Inbox.html');
        $tpl->SetBlock('inbox');

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $messages = $model->GetInbox($user_id);

        $i = 0;
        foreach ($messages as $message) {
            $i++;
            $tpl->SetBlock('inbox/message');
            $tpl->SetVariable('rownum', $i);
            $tpl->SetVariable('from', $message['from_nickname']);
            $tpl->SetVariable('title', $message['title']);
            $tpl->SetVariable('send_time', $date->Format($message['insert_time']));

            $tpl->SetVariable('message_url', $this->gadget->urlMap('ViewMessage', array('id' => $message['id'])));
            $tpl->ParseBlock('inbox/message');
        }

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_title', _t('PRIVATEMESSAGE_MESSAGE_TITLE'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));

        $tpl->ParseBlock('inbox');
        return $tpl->Get();
    }
}