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
class PrivateMessage_Actions_Outbox extends PrivateMessage_Actions_Default
{
    /**
     * Display Outbox
     *
     * @access  public
     * @return  void
     */
    function Outbox()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $date_format = $this->gadget->registry->fetch('date_format');
        $tpl = $this->gadget->loadTemplate('Outbox.html');
        $tpl->SetBlock('outbox');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Outbox'));

        $tpl->SetVariable('action', 'Outbox');

        $post = jaws()->request->fetch(array('page', 'replied', 'attachment', 'term', 'page_item'));
        $page = $post['page'];

        $tpl->SetVariable('txt_term', $post['term']);

        $page = empty($page)? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = $this->gadget->registry->fetch('paging_limit');
            if(empty($limit)) {
                $limit = 10;
            }
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_OUTBOX'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));

        $tpl->SetBlock('outbox/replied_filter');
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('opt_replied_' . $post['replied'], 'selected="selected"');
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->ParseBlock('outbox/replied_filter');

        $tpl->SetBlock('outbox/table_number');
        $tpl->ParseBlock('outbox/table_number');

        $date = $GLOBALS['app']->loadDate();
        $oModel = $this->gadget->model->load('Outbox');
        $mModel = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('outbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('outbox/response');
        }

        $post['published'] = true;
        $messages = $oModel->GetOutbox($user, $post, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('outbox/message');
                $tpl->SetVariable('rownum', $i);

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

                $tpl->SetVariable('subject', $message['subject']);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time'], $date_format));

                $tpl->SetVariable('message_url', $this->gadget->urlMap(
                    'OutboxMessage', array('id' => $message['id'])));

                if ($message['attachments'] > 0) {
                    $tpl->SetBlock('outbox/message/have_attachment');
                    $tpl->SetVariable('attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
                    $tpl->SetVariable('icon_attachment', STOCK_ATTACH);
                    $tpl->ParseBlock('outbox/message/have_attachment');
                } else {
                    $tpl->SetBlock('outbox/message/no_attachment');
                    $tpl->ParseBlock('outbox/message/no_attachment');
                }

                $tpl->ParseBlock('outbox/message');
            }
        }

        $tpl->SetVariable('lbl_recipients', _t('PRIVATEMESSAGE_MESSAGE_RECIPIENTS'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));

        $post['published'] = true;
        $outboxTotal = $oModel->GetOutboxStatistics($user, $post);

        $params = array();
        if (!empty($post['replied'])) {
            $params['replied'] = $post['replied'];
        }
        if (!empty($post['term'])) {
            $params['term'] = $post['term'];
        }
        if (!empty($post['page_item'])) {
            $params['page_item'] = $post['page_item'];
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'outbox',
            $page,
            $limit,
            $outboxTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $outboxTotal),
            'Outbox',
            $params
        );

        $tpl->ParseBlock('outbox');
        return $tpl->Get();
    }
}