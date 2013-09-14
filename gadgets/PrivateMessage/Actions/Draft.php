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

        $page = jaws()->request->fetch('page', 'get');
        $page = empty($page)? 1 : (int)$page;
        $limit = (int)$this->gadget->registry->fetch('draft_limit');
        $tpl = $this->gadget->loadTemplate('Outbox.html');
        $tpl->SetBlock('outbox');
        $tpl->SetVariable('title', _t('PRIVATEMESSAGE_NAVIGATION_AREA_DRAFT'));

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
}