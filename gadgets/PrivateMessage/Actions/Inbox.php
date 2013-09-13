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
class PrivateMessage_Actions_Inbox extends PrivateMessage_HTML
{
    /**
     * Display Inbox
     *
     * @access  public
     * @return  void
     */
    function Inbox()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $page = jaws()->request->fetch('page', 'get');
        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('inbox_limit');
        $tpl = $this->gadget->loadTemplate('Inbox.html');
        $tpl->SetBlock('inbox');
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_NAVIGATION_AREA_INBOX'));

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('inbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('inbox/response');
        }

        $messages = $model->GetInbox($user, null, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('inbox/message');
                $tpl->SetVariable('rownum', $i);
                $tpl->SetVariable('from', $message['from_nickname']);
                if($message['read']) {
                    $subject = $message['subject'];
                    $tpl->SetVariable('status', 'read');
                } else {
                    $subject = '<strong>' . $message['subject'] . '</strong>';
                    $tpl->SetVariable('status', 'unread');
                }
                $tpl->SetVariable('subject', $subject);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time']));

                $tpl->SetVariable('message_url', $this->gadget->urlMap(
                    'ViewMessage',
                    array('id' => $message['message_recipient_id'])));

                // user's profile
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $message['from_username'])
                    )
                );

                $tpl->ParseBlock('inbox/message');
            }
        }

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));

        $iModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $inboxTotal = $iModel->GetInboxStatistics($user);

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'inbox',
            $page,
            $limit,
            $inboxTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $inboxTotal),
            'Inbox'
        );

        $tpl->ParseBlock('inbox');
        return $tpl->Get();
    }
}