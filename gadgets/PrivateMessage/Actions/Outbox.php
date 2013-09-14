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

        $tpl = $this->gadget->loadTemplate('Outbox.html');
        $tpl->SetBlock('outbox');

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

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_NAVIGATION_AREA_OUTBOX'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('filter', _t('PRIVATEMESSAGE_FILTER'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Outbox');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('inbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('inbox/response');
        }

        $post['published'] = true;
        $messages = $model->GetOutbox($user, $post, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('outbox/message');
                $tpl->SetVariable('rownum', $i);
                $tpl->SetVariable('from', $message['from_nickname']);
                $tpl->SetVariable('subject', $message['subject']);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time']));

                $tpl->SetVariable('message_url', $this->gadget->urlMap(
                    'Message',
                    array('id' => $message['id'], 'view' => 'reference')));
                $tpl->ParseBlock('outbox/message');
            }
        }

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));

        $post['published'] = true;
        $outboxTotal = $model->GetOutboxStatistics($user, $post);

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