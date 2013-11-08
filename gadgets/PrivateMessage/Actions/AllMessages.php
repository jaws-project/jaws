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
class PrivateMessage_Actions_AllMessages extends PrivateMessage_Actions_Default
{
    /**
     * Display AllMessages
     *
     * @access  public
     * @return  void
     */
    function AllMessages()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->loadTemplate('AllMessages.html');
        $tpl->SetBlock('all');

        $post = jaws()->request->fetch(array('view', 'page', 'read', 'replied', 'term', 'page_item'));
        $page = $post['page'];
        $view = $post['view'];

        $tpl->SetVariable('txt_term', $post['term']);

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('AllMessages'));
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_ALL_MESSAGES'));

        $page = empty($page) ? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = $this->gadget->registry->fetch('paging_limit');
            if (empty($limit)) {
                $limit = 10;
            }
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $tpl->SetVariable('page', $page);
        $tpl->SetVariable('view', $view);
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('lbl_in_out', _t('PRIVATEMESSAGE_IN_OUT'));

        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $date = $GLOBALS['app']->loadDate();
        $model = $this->gadget->model->load('AllMessages');
        $mModel = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $messages = $model->GetAllMessages($user, $post, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('all/message');
                $tpl->SetVariable('rownum', $i);

                $subject = $message['subject'];
                // check inbox or outbox
                if ($message['recipient'] == $user) {
                    $tpl->SetVariable('in_out', _t('PRIVATEMESSAGE_IN'));
                    $tpl->SetVariable('id',  $message['message_recipient_id']);

                    $tpl->SetVariable('message_url', $this->gadget->urlMap(
                        'InboxMessage',
                        array('id' => $message['message_recipient_id'])));

                    if($message['read']) {
                        $tpl->SetVariable('status', 'read');
                    } else {
                        $subject = '<strong>' . $message['subject'] . '</strong>';
                        $tpl->SetVariable('status', 'unread');
                    }
                } else {
                    $tpl->SetVariable('in_out', _t('PRIVATEMESSAGE_OUT'));
                    $tpl->SetVariable('id',  $message['id']);

                    $tpl->SetVariable('message_url', $this->gadget->urlMap(
                        'OutboxMessage',
                        array('id' => $message['id'])));
                }

                $recipients = $mModel->GetMessageRecipientsInfo($message['id']);
                $recipients_str = _t('PRIVATEMESSAGE_MESSAGE_RECIPIENT_ALL_USERS');
                if (count($recipients) > 0) {
                    // user's profile
                    $user_url = $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $recipients[0]['username']));
                    $recipients_str = '<a href=' . $user_url . '>' . $recipients[0]['nickname'] . '<a/>';
                    if (count($recipients) > 1) {
                        $recipients_str .= ' , ...';
                    }
                }
                $tpl->SetVariable('recipients', $recipients_str);


                $tpl->SetVariable('from', $message['from_nickname']);

                $tpl->SetVariable('subject', $subject);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));

                if ($message['attachments'] > 0) {
                    $tpl->SetBlock('all/message/have_attachment');
                    $tpl->SetVariable('attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
                    $tpl->SetVariable('icon_attachment', STOCK_ATTACH);
                    $tpl->ParseBlock('all/message/have_attachment');
                } else {
                    $tpl->SetBlock('all/message/no_attachment');
                    $tpl->ParseBlock('all/message/no_attachment');
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

                $tpl->ParseBlock('all/message');
            }
        }

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_recipients', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));
        $total = $model->GetAllMessagesStatistics($user, $post);

        $params = array();
        if(!empty($post['read'])) {
            $params['read'] = $post['read'];
        }
        if(!empty($post['replied'])) {
            $params['replied'] = $post['replied'];
        }
        if(!empty($post['term'])) {
            $params['term'] = $post['term'];
        }
        if (!empty($post['page_item'])) {
            $params['page_item'] = $post['page_item'];
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'all',
            $page,
            $limit,
            $total,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $total),
            'AllMessages',
            $params
        );

        $tpl->ParseBlock('all');
        return $tpl->Get();
    }
}