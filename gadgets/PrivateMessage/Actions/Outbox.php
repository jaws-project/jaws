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
class PrivateMessage_Actions_Outbox extends PrivateMessage_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Outbox.html');
        $tpl->SetBlock('outbox');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Outbox'));

        $post = jaws()->request->fetch(array('page', 'replied', 'attachment', 'filter'), 'post');
        if (!empty($post['replied']) || !empty($post['attachment']) || !empty($post['filter'])) {
            $tpl->SetVariable('opt_replied_' . $post['replied'], 'selected="selected"');
            $tpl->SetVariable('opt_attachment_' . $post['attachment'], 'selected="selected"');
            $tpl->SetVariable('txt_filter', $post['filter']);
            $page = $post['page'];
        } else {
            $post = null;
            $page = jaws()->request->fetch('page', 'get');
        }
        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('outbox_limit');

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_OUTBOX'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('filter', _t('PRIVATEMESSAGE_FILTER'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $date = $GLOBALS['app']->loadDate();
        $oModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Outbox');
        $mModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
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
                $tpl->SetVariable('send_time', $date->Format($message['insert_time']));

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

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'outbox',
            $page,
            $limit,
            $outboxTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $outboxTotal),
            'Outbox'
        );

        $tpl->ParseBlock('outbox');
        return $tpl->Get();
    }
}