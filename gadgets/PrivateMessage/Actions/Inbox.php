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

        $tpl = $this->gadget->loadTemplate('Inbox.html');
        $tpl->SetBlock('inbox');

        $post = jaws()->request->fetch(array('page', 'read', 'attachment', 'filter'), 'post');
        if (!empty($post['read']) || !empty($post['attachment']) || !empty($post['filter'])) {
            $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
            $tpl->SetVariable('opt_attachment_' . $post['attachment'], 'selected="selected"');
            $tpl->SetVariable('txt_filter', $post['filter']);
            $page = $post['page'];
        } else {
            $post = null;
            $page = jaws()->request->fetch('page', 'get');
        }

        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('inbox_limit');

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_NAVIGATION_AREA_INBOX'));
        $tpl->SetVariable('page', $page);
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status_read', _t('PRIVATEMESSAGE_STATUS_READ'));
        $tpl->SetVariable('status_unread', _t('PRIVATEMESSAGE_STATUS_UNREAD'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('attachment_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('attachment_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('filter', _t('PRIVATEMESSAGE_FILTER'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Inbox');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('inbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('inbox/response');
        }

        $messages = $model->GetInbox($user, $post, $limit, ($page - 1) * $limit);
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
        $inboxTotal = $iModel->GetInboxStatistics($user, $post);

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