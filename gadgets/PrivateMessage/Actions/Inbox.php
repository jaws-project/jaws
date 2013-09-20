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

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Inbox.html');
        $tpl->SetBlock('inbox');

        $post = jaws()->request->fetch(array('view', 'page', 'read', 'replied', 'term'), 'post');
        if (!empty($post['read']) || !empty($post['replied']) || !empty($post['term'])) {
            $tpl->SetVariable('opt_replied_' . $post['replied'], 'selected="selected"');
            $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
            $tpl->SetVariable('txt_term', $post['term']);
            $page = $post['page'];
            $view = $post['view'];
        } else {
            $post = null;
            $get = jaws()->request->fetch(array('view', 'page'), 'get');
            $page = $get['page'];
            $view = $get['view'];
        }

        $post['archived'] = false;
        if ($view == 'archived') {
            $post['archived'] = true;
        }

        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('inbox_limit');

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_INBOX'));
        $tpl->SetVariable('page', $page);
        $tpl->SetVariable('view', $view);
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('filter', _t('PRIVATEMESSAGE_FILTER'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
        $tpl->SetVariable('lbl_mark_as_read', _t('PRIVATEMESSAGE_MARK_AS_READ'));
        $tpl->SetVariable('lbl_mark_as_unread', _t('PRIVATEMESSAGE_MARK_AS_UNREAD'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);

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
                $tpl->SetVariable('id',  $message['message_recipient_id']);
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
                    'InboxMessage',
                    array('id' => $message['message_recipient_id'])));

                if ($message['attachments'] > 0) {
                    $tpl->SetBlock('inbox/message/have_attachment');
                    $tpl->SetVariable('attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
                    $tpl->SetVariable('icon_attachment', STOCK_ATTACH);
                    $tpl->ParseBlock('inbox/message/have_attachment');
                } else {
                    $tpl->SetBlock('inbox/message/no_attachment');
                    $tpl->ParseBlock('inbox/message/no_attachment');
                }

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