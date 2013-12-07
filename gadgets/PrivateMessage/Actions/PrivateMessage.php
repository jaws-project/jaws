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

        $iModel = $this->gadget->model->load('Inbox');
        $oModel = $this->gadget->model->load('Outbox');
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $unreadAnnouncementCount = $iModel->GetInboxStatistics(
                                                $user_id,
                                                array('read' => 'no',
                                                      'archived' => false,
                                                      'type' => PrivateMessage_Info::PRIVATEMESSAGE_TYPE_ANNOUNCEMENT));
        $unreadInboxCount = $iModel->GetInboxStatistics(
                                                $user_id,
                                                array('read' => 'no',
                                                      'archived' => false,
                                                      'type' => PrivateMessage_Info::PRIVATEMESSAGE_TYPE_MESSAGE));
        $draftMessageCount = $oModel->GetOutboxStatistics($user_id, array('published' => false));
        if ($unreadInboxCount > 0) {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX', '(' . $unreadInboxCount . ')'));
        } else {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX'));
        }

        $tpl->SetVariable('archived', _t('PRIVATEMESSAGE_ARCHIVED'));

        if ($draftMessageCount > 0) {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT', '(' . $draftMessageCount . ')'));
        } else {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT'));
        }

        $tpl->SetVariable('all_messages', _t('PRIVATEMESSAGE_ALL_MESSAGES'));
        $tpl->SetVariable('all_messages_url', $this->gadget->urlMap('AllMessages'));

        if ($unreadAnnouncementCount > 0) {
            $tpl->SetVariable('announcement', _t('PRIVATEMESSAGE_ANNOUNCEMENT', '(' . $unreadAnnouncementCount . ')'));
        } else {
            $tpl->SetVariable('announcement', _t('PRIVATEMESSAGE_ANNOUNCEMENT'));
        }
        $tpl->SetVariable('announcement_url', $this->gadget->urlMap('Announcement'));

        $tpl->SetVariable('inbox_url', $this->gadget->urlMap('Inbox'));
        $tpl->SetVariable('archived_url', $this->gadget->urlMap('Inbox', array('view' => 'archived')));
        $tpl->SetVariable('draft_url', $this->gadget->urlMap('Draft'));

        $tpl->SetVariable('outbox', _t('PRIVATEMESSAGE_OUTBOX'));
        $tpl->SetVariable('outbox_url', $this->gadget->urlMap('Outbox'));


        $tpl->SetVariable('compose_message', _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'));
        $tpl->SetVariable('compose_message_url', $this->gadget->urlMap('Compose'));

        $tpl->ParseBlock('PrivateMessage');
        return $tpl->Get();
    }
}