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
class PrivateMessage_Actions_Announcement extends PrivateMessage_Actions_Default
{
    /**
     * Display AllMessages
     *
     * @access  public
     * @return  void
     */
    function Announcement()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->loadTemplate('Inbox.html');
        $tpl->SetBlock('inbox');

        $post = jaws()->request->fetch(array('page', 'read', 'replied', 'term', 'page_item'));
        $page = $post['page'];

        $tpl->SetVariable('opt_replied_' . $post['replied'], 'selected="selected"');
        $tpl->SetVariable('opt_read_' . $post['read'], 'selected="selected"');
        $tpl->SetVariable('txt_term', $post['term']);

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Announcement'));
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_ANNOUNCEMENT'));

        $page = empty($page) ? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = $this->gadget->registry->fetch('paging_limit');
            if(empty($limit)) {
                $limit = 10;
            }
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $tpl->SetVariable('page', $page);
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_read', _t('PRIVATEMESSAGE_STATUS_READ'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));

        $tpl->SetBlock('inbox/inbox_action');
        $tpl->SetVariable('lbl_archive', _t('PRIVATEMESSAGE_ARCHIVE'));
        $tpl->SetVariable('lbl_mark_as_read', _t('PRIVATEMESSAGE_MARK_AS_READ'));
        $tpl->SetVariable('lbl_mark_as_unread', _t('PRIVATEMESSAGE_MARK_AS_UNREAD'));
        $tpl->ParseBlock('inbox/inbox_action');

        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);

        $date = $GLOBALS['app']->loadDate();
        $model = $this->gadget->model->load('Inbox');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('inbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('inbox/response');
        }

        $post['archived'] = false;
        $post['type'] = PrivateMessage_Info::PRIVATEMESSAGE_TYPE_ANNOUNCEMENT; // Just show announcement
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
                $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));

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

        $iModel = $this->gadget->model->load('Inbox');
        $inboxTotal = $iModel->GetInboxStatistics($user, $post);

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
            'inbox',
            $page,
            $limit,
            $inboxTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $inboxTotal),
            'Announcement',
            $params
        );

        $tpl->ParseBlock('inbox');
        return $tpl->Get();
    }
}