<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Actions_Draft extends PrivateMessage_Actions_Default
{
    /**
     * Display draft
     *
     * @access  public
     * @return  void
     */
    function Draft()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $date_format = $this->gadget->registry->fetch('date_format');

        $page = jaws()->request->fetch('page');
        $page_item = jaws()->request->fetch('page_item', 'post');
        $page = empty($page)? 1 : (int)$page;
        if (empty($page_item)) {
            $limit = $this->gadget->registry->fetch('paging_limit');
            if(empty($limit)) {
                $limit = 10;
            }
        } else {
            $limit = $page_item;
        }

        $tpl = $this->gadget->template->load('Outbox.html');
        $tpl->SetBlock('outbox');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Draft'));

        $tpl->SetVariable('action', 'Draft');
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_DRAFT'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('lbl_page_item', _t('PRIVATEMESSAGE_ITEMS_PER_PAGE'));

        $tpl->SetBlock('outbox/table_checkbox');
        $tpl->ParseBlock('outbox/table_checkbox');

        $tpl->SetBlock('outbox/actions');
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $tpl->ParseBlock('outbox/actions');

        $date = Jaws_Date::getInstance();
        $oModel = $this->gadget->model->load('Outbox');
        $mModel = $this->gadget->model->load('Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $messages = $oModel->GetOutbox($user, array('published' => false), $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('outbox/message');
                $tpl->SetBlock('outbox/message/checkbox');
                $tpl->SetVariable('id', $message['id']);
                $tpl->ParseBlock('outbox/message/checkbox');

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
                    'OutboxMessage',
                    array('id' => $message['id'])));

                $tpl->SetVariable('message_url', $this->gadget->urlMap('Compose', array('id' => $message['id'])));

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

        $draftTotal = $oModel->GetOutboxStatistics($user, array('published' => false));

        $params = array();
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
            $draftTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $draftTotal),
            'Draft',
            $params
        );

        $tpl->ParseBlock('outbox');
        return $tpl->Get();
    }

    /**
     * Draft a message
     *
     * @access  public
     * @return  void
     */
    function DraftMessage()
    {
        $this->gadget->CheckPermission('ComposeMessage');

        $id = jaws()->request->fetch('id', 'get');

        $model = $this->gadget->model->load('Message');
        $res = $model->MarkMessagesPublishStatus($id, false);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res === true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_DRAFTED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_DRAFTED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Draft'));
    }
}