<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_PrivateMessage extends Jaws_Gadget_Action
{
    /**
     * Display Private Message
     *
     * @access  public
     * @return  void
     */
    function PrivateMessage()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $tpl = $this->gadget->template->load('PrivateMessage.html');
        $tpl->SetBlock('PrivateMessage');
        $tpl->SetVariable('title', $this->gadget->title);

        $model = $this->gadget->model->load('Message');
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $unreadNotifyCount = $model->GetMessagesStatistics(
                                                $user_id,
                                                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS,
                                                array('read' => 'no'));
        $unreadInboxCount = $model->GetMessagesStatistics(
                                                $user_id,
                                                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX,
                                                array('read' => 'no'));
        $draftMessageCount = $model->GetMessagesStatistics(
                                                 $user_id,
                                                 PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT);
        if ($unreadNotifyCount > 0) {
            $tpl->SetVariable('notifications', _t('PRIVATEMESSAGE_NOTIFICATIONS', '(' . $unreadNotifyCount . ')'));
        } else {
            $tpl->SetVariable('notifications', _t('PRIVATEMESSAGE_NOTIFICATIONS'));
        }
        if ($unreadInboxCount > 0) {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX', '(' . $unreadInboxCount . ')'));
        } else {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX'));
        }
        if ($draftMessageCount > 0) {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT', '(' . $draftMessageCount . ')'));
        } else {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT'));
        }

        $tpl->SetVariable('archived', _t('PRIVATEMESSAGE_ARCHIVED'));
        $tpl->SetVariable('outbox', _t('PRIVATEMESSAGE_OUTBOX'));
        $tpl->SetVariable('trash', _t('PRIVATEMESSAGE_TRASH'));
        $tpl->SetVariable('compose_message', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
        $tpl->SetVariable('all_messages', _t('PRIVATEMESSAGE_ALL_MESSAGES'));

        $tpl->SetVariable('compose_message_url', $this->gadget->urlMap('Compose'));
        $tpl->SetVariable('all_messages_url', $this->gadget->urlMap('Messages'));
        $tpl->SetVariable('notifications_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS)));
        $tpl->SetVariable('inbox_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX)));
        $tpl->SetVariable('archived_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED)));
        $tpl->SetVariable('draft_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT)));
        $tpl->SetVariable('outbox_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX)));
        $tpl->SetVariable('trash_url', $this->gadget->urlMap(
                                            'Messages',
                                            array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH)));

        $tpl->ParseBlock('PrivateMessage');
        return $tpl->Get();
    }
}