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
class PrivateMessage_Actions_Draft extends PrivateMessage_HTML
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $page = jaws()->request->fetch('page', 'get');
        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('draft_limit');
        $tpl = $this->gadget->loadTemplate('Outbox.html');
        $tpl->SetBlock('outbox');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Draft'));

        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_DRAFT'));
        $tpl->SetVariable('lbl_replied', _t('PRIVATEMESSAGE_MESSAGE_REPLIED'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_attachment', _t('PRIVATEMESSAGE_MESSAGE_ATTACHMENT'));
        $tpl->SetVariable('filter', _t('PRIVATEMESSAGE_FILTER'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $tpl->SetBlock('outbox/actions');
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $tpl->ParseBlock('outbox/actions');

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Outbox');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        if ($response = $GLOBALS['app']->Session->PopResponse('PrivateMessage.Message')) {
            $tpl->SetBlock('outbox/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('outbox/response');
        }

        $messages = $model->GetOutbox($user, array('published' => false), $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($messages) && !empty($messages)) {
            $i = 0;
            foreach ($messages as $message) {
                $i++;
                $tpl->SetBlock('outbox/message');
                $tpl->SetBlock('outbox/message/checkbox');
                $tpl->SetVariable('id', $message['id']);
                $tpl->ParseBlock('outbox/message/checkbox');

                $tpl->SetVariable('from', $message['from_nickname']);
                $tpl->SetVariable('subject', $message['subject']);
                $tpl->SetVariable('send_time', $date->Format($message['insert_time']));

                $tpl->SetVariable('message_url', $this->gadget->urlMap(
                    'OutboxMessage',
                    array('id' => $message['id'])));

                $tpl->SetVariable('message_url', $this->gadget->urlMap('Compose', array('id' => $message['id'])));
                $tpl->ParseBlock('outbox/message');
            }
        }

        $tpl->SetVariable('lbl_from', _t('PRIVATEMESSAGE_MESSAGE_FROM'));
        $tpl->SetVariable('lbl_subject', _t('PRIVATEMESSAGE_MESSAGE_SUBJECT'));
        $tpl->SetVariable('lbl_send_time', _t('PRIVATEMESSAGE_MESSAGE_SEND_TIME'));

        $draftTotal = $model->GetOutboxStatistics($user, array('published' => false));

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'outbox',
            $page,
            $limit,
            $draftTotal,
            _t('PRIVATEMESSAGE_MESSAGE_COUNT', $draftTotal),
            'Draft'
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

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
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