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
class PrivateMessage_Actions_PrivateMessage extends Jaws_Gadget_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $tpl = $this->gadget->loadTemplate('PrivateMessage.html');
        $tpl->SetBlock('PrivateMessage');
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_NAME'));

        $iModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $oModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Outbox');
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $unreadMessageCount = $iModel->GetInboxStatistics($user_id, array('read'=>'no', 'archived'=> false));
        $draftMessageCount = $oModel->GetOutboxStatistics($user_id, array('published' => false));
        if ($unreadMessageCount > 0) {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX', '(' . $unreadMessageCount . ')'));
        } else {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_INBOX'));
        }

        $tpl->SetVariable('archived', _t('PRIVATEMESSAGE_ARCHIVED'));

        if ($draftMessageCount > 0) {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT', '(' . $draftMessageCount . ')'));
        } else {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_DRAFT'));
        }

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