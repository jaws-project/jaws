<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2024 Jaws Development Group
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(401);
        }

        $tpl = $this->gadget->template->load('PrivateMessage.html');
        $tpl->SetBlock('PrivateMessage');
        $tpl->SetVariable('title', $this->gadget->title);

        $model = $this->gadget->model->load('Message');
        $user_id = $this->app->session->user->id;
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
            $tpl->SetVariable('notifications', $this::t('NOTIFICATIONS', '(' . $unreadNotifyCount . ')'));
        } else {
            $tpl->SetVariable('notifications', $this::t('NOTIFICATIONS'));
        }
        if ($unreadInboxCount > 0) {
            $tpl->SetVariable('inbox', $this::t('INBOX', '(' . $unreadInboxCount . ')'));
        } else {
            $tpl->SetVariable('inbox', $this::t('INBOX'));
        }
        if ($draftMessageCount > 0) {
            $tpl->SetVariable('draft', $this::t('DRAFT', '(' . $draftMessageCount . ')'));
        } else {
            $tpl->SetVariable('draft', $this::t('DRAFT'));
        }

        $tpl->SetVariable('archived', $this::t('ARCHIVED'));
        $tpl->SetVariable('outbox', $this::t('OUTBOX'));
        $tpl->SetVariable('trash', $this::t('TRASH'));
        $tpl->SetVariable('compose_message', $this::t('COMPOSE_MESSAGE'));
        $tpl->SetVariable('all_messages', $this::t('ALL_MESSAGES'));

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