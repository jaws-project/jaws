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
class PrivateMessage_Actions_NavigationArea extends Jaws_Gadget_HTML
{
    /**
     * Display Navigation Area
     *
     * @access  public
     * @return  void
     */
    function NavigationArea()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $tpl = $this->gadget->loadTemplate('NavigationArea.html');
        $tpl->SetBlock('NavigationArea');

        $iModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $oModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Outbox');
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $unreadMessageCount = $iModel->GetInboxStatistics($user_id, array('read'=>'no'));
        $draftMessageCount = $oModel->GetOutboxStatistics($user_id, false);
        if ($unreadMessageCount > 0) {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_NAVIGATION_AREA_INBOX', '(' . $unreadMessageCount . ')'));
        } else {
            $tpl->SetVariable('inbox', _t('PRIVATEMESSAGE_NAVIGATION_AREA_INBOX'));
        }

        if ($draftMessageCount > 0) {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_NAVIGATION_AREA_DRAFT', '(' . $draftMessageCount . ')'));
        } else {
            $tpl->SetVariable('draft', _t('PRIVATEMESSAGE_NAVIGATION_AREA_DRAFT'));
        }

        $tpl->SetVariable('inbox_url', $this->gadget->urlMap('Inbox'));
        $tpl->SetVariable('draft_url', $this->gadget->urlMap('Draft'));

        $tpl->SetVariable('outbox', _t('PRIVATEMESSAGE_NAVIGATION_AREA_OUTBOX'));
        $tpl->SetVariable('outbox_url', $this->gadget->urlMap('Outbox'));


        $tpl->SetVariable('send_message', _t('PRIVATEMESSAGE_NAVIGATION_AREA_SEND_MESSAGE'));
        $tpl->SetVariable('send_message_url', $this->gadget->urlMap('Send'));

        $tpl->ParseBlock('NavigationArea');
        return $tpl->Get();
    }
}