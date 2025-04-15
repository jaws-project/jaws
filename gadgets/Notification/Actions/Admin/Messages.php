<?php
/**
 * Notification Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Notification
 */
class Notification_Actions_Admin_Messages extends Notification_Actions_Admin_Default
{
    /**
     * Builds Messages UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Messages()
    {
        $this->gadget->CheckPermission('Messages');
        $this->AjaxMe('script.js');
        $this->gadget->define('lbl_message_title', Jaws::t('TITLE'));
        $this->gadget->define('lbl_driver', $this::t('DRIVER'));
        $this->gadget->define('lbl_message_type', $this::t('MESSAGE_TYPE'));
        $this->gadget->define('lbl_shouter', Jaws::t('GADGET'));
        $this->gadget->define('lbl_insert_time', Jaws::t('CREATETIME'));
        $this->gadget->define('lbl_status', Jaws::t('STATUS'));
        $this->gadget->define('lbl_view', $this::t('VIEW'));
        $this->gadget->define('lbl_delete_message', $this::t('DELETE_MESSAGE'));
        $this->gadget->define('lbl_delete_similar_message', $this::t('DELETE_SIMILAR_MESSAGE'));
        $this->gadget->define('confirmDeleteMessage', Jaws::t('CONFIRM_DELETE', $this::t('MESSAGE')));
        $this->gadget->define('confirmDeleteSimilarMessage', Jaws::t('CONFIRM_DELETE', $this::t('MESSAGES_SIMILAR')));

        $tpl = $this->gadget->template->loadAdmin('Messages.html');
        $tpl->SetBlock('Messages');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Messages'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

        $tpl->SetVariable('lbl_message', $this::t('MESSAGE'));
        $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
        $tpl->SetVariable('lbl_shouter', Jaws::t('GADGET'));
        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_summary', $this::t('MESSAGE_SUMMARY'));
        $tpl->SetVariable('lbl_verbose', $this::t('MESSAGE_VERBOSE'));
        $tpl->SetVariable('lbl_message_type', $this::t('MESSAGE_TYPE'));
        $tpl->SetVariable('lbl_callback', $this::t('MESSAGE_CALLBACK'));
        $tpl->SetVariable('lbl_image', $this::t('IMAGE'));
        $tpl->SetVariable('lbl_insert_time', Jaws::t('TIME'));
        $tpl->SetVariable('lbl_attempts', $this::t('MESSAGE_ATTEMPTS'));
        $tpl->SetVariable('lbl_attempt_time', $this::t('MESSAGE_ATTEMPT_TIME'));
        $tpl->SetVariable('lbl_status_comment', $this::t('MESSAGE_STATUS_COMMENT'));
        $tpl->SetVariable('lbl_from_date', $this::t('FROM_DATE'));
        $tpl->SetVariable('lbl_to_date', $this::t('TO_DATE'));
        $tpl->SetVariable('lbl_contact', $this::t('CONTACT'));
        $tpl->SetVariable('lbl_message_details', $this::t('MESSAGE_DETAILS'));
        $tpl->SetVariable('lbl_back', Jaws::t('BACK'));

        $tpl->SetBlock('Messages/filter_from_date');
        $objDate = Jaws_Date::getInstance();
        $this->gadget->action->load('DatePicker')->calendar($tpl,
            array('name' => 'filter_from_date', 'value' => $objDate->Format(time() - (24 * 3600), 'yyyy/MM/dd'))
        );
        $tpl->ParseBlock('Messages/filter_from_date');

        $tpl->SetBlock('Messages/filter_to_date');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'filter_to_date'));
        $tpl->ParseBlock('Messages/filter_to_date');

        $gadgets = $this->gadget->model->load()->recommendedfor();
        if (!Jaws_Error::IsError($gadgets)) {
            foreach ($gadgets as $gadget) {
                $objGadget = Jaws_Gadget::getInstance($gadget);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }

                $tpl->SetBlock('Messages/filter_shouter');
                $tpl->SetVariable('value', $gadget);
                $tpl->SetVariable('title', $objGadget->title);
                $tpl->ParseBlock('Messages/filter_shouter');
            }
        }

        $drivers = $this->gadget->model->load('Drivers')->GetNotificationDrivers();
        if (Jaws_Error::IsError($drivers)) {
            $drivers = array();
        }
        foreach ($drivers as $driver) {
            $tpl->SetBlock('Messages/filter_driver');
            $tpl->SetVariable('value', $driver['id']);
            $tpl->SetVariable('title', $driver['title']);
            $tpl->ParseBlock('Messages/filter_driver');
        }

        $messageTypes = array(
            Notification_Info::MESSAGE_TYPE_EMAIL => $this::t('MESSAGE_TYPE_EMAIL'),
            Notification_Info::MESSAGE_TYPE_SMS => $this::t('MESSAGE_TYPE_SMS'),
            Notification_Info::MESSAGE_TYPE_WEB => $this::t('MESSAGE_TYPE_WEB'),
            Notification_Info::MESSAGE_TYPE_APP => $this::t('MESSAGE_TYPE_APP'),
        );
        foreach ($messageTypes as $value => $title) {
            $tpl->SetBlock('Messages/filter_message_type');
            $tpl->SetVariable('value', $value);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Messages/filter_message_type');
        }

        $sendStatuses = array(
            Notification_Info::MESSAGE_STATUS_PENDING => $this::t('MESSAGE_STATUS_PENDING'),
            Notification_Info::MESSAGE_STATUS_SENDING => $this::t('MESSAGE_STATUS_SENDING'),
            Notification_Info::MESSAGE_STATUS_SENT => $this::t('MESSAGE_STATUS_SENT'),
            Notification_Info::MESSAGE_STATUS_EXPIRED => $this::t('MESSAGE_STATUS_EXPIRED'),
            Notification_Info::MESSAGE_STATUS_REJECTED => $this::t('MESSAGE_STATUS_REJECTED'),
        );
        foreach ($sendStatuses as $value => $title) {
            $tpl->SetBlock('Messages/filter_status');
            $tpl->SetVariable('value', $value);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Messages/filter_status');
        }

        $tpl->ParseBlock('Messages');
        return $tpl->Get();
    }

    /**
     * Get Messages list
     *
     * @access  public
     * @return  JSON
     */
    function GetMessages()
    {
        $this->gadget->CheckPermission('Messages');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $filters = $post['filters'];
        $model = $this->gadget->model->load('Notification');
        $messages = $model->GetNotificationMessages($filters, $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($messages)) {
            return $this->gadget->session->response(
                $messages->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $objDate = Jaws_Date::getInstance();
        foreach ($messages as &$message) {
            $messageType = '';
            switch ($message['type']) {
                case Notification_Info::MESSAGE_TYPE_EMAIL:
                    $messageType = $this::t('MESSAGE_TYPE_EMAIL');
                    break;
                case Notification_Info::MESSAGE_TYPE_SMS:
                    $messageType = $this::t('MESSAGE_TYPE_SMS');
                    break;
                case Notification_Info::MESSAGE_TYPE_WEB:
                    $messageType = $this::t('MESSAGE_TYPE_WEB');
                    break;
                case Notification_Info::MESSAGE_TYPE_APP:
                    $messageType = $this::t('MESSAGE_TYPE_APP');
                    break;
            }
            $message['message_type'] = $messageType;

            $sendStatus = '';
            switch ($message['status']) {
                case Notification_Info::MESSAGE_STATUS_PENDING:
                    $sendStatus = $this::t('MESSAGE_STATUS_PENDING');
                    break;
                case Notification_Info::MESSAGE_STATUS_SENDING:
                    $sendStatus = $this::t('MESSAGE_STATUS_SENDING');
                    break;
                case Notification_Info::MESSAGE_STATUS_SENT:
                    $sendStatus = $this::t('MESSAGE_STATUS_SENT');
                    break;
                case Notification_Info::MESSAGE_STATUS_EXPIRED:
                    $sendStatus = $this::t('MESSAGE_STATUS_EXPIRED');
                    break;
                case Notification_Info::MESSAGE_STATUS_REJECTED:
                    $sendStatus = $this::t('MESSAGE_STATUS_REJECTED');
                    break;
            }
            $message['status'] = $sendStatus;

            $message['time'] = $objDate->Format($message['time'], 'yyyy/MM/dd HH:mm:ss');
        }
        $messagesCount = $model->GetMessagesCount($post['filters']);

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $messagesCount,
                'records' => $messages
            )
        );
    }

    /**
     * Get a message info
     *
     * @access  public
     * @return  JSON
     */
    function GetMessage()
    {
        $this->gadget->CheckPermission('Messages');
        $recipientId = (int)$this->gadget->request->fetch('recipient_id', 'post');
        $messageInfo = $this->gadget->model->load('Notification')->GetNotificationMessageDetails($recipientId);
        if (Jaws_Error::IsError($messageInfo)) {
            return $this->gadget->session->response(
                $messageInfo->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $objDate = Jaws_Date::getInstance();
        $messageType = '';
        switch ($messageInfo['driver']) {
            case Notification_Info::MESSAGE_TYPE_EMAIL:
                $messageType = $this::t('MESSAGE_TYPE_EMAIL');
                break;
            case Notification_Info::MESSAGE_TYPE_SMS:
                $messageType = $this::t('MESSAGE_TYPE_SMS');
                break;
            case Notification_Info::MESSAGE_TYPE_WEB:
                $messageType = $this::t('MESSAGE_TYPE_WEB');
                break;
        }
        $sendStatus = '';
        switch ($messageInfo['status']) {
            case Notification_Info::MESSAGE_STATUS_PENDING:
                $sendStatus = $this::t('MESSAGE_STATUS_PENDING');
                break;
            case Notification_Info::MESSAGE_STATUS_SENDING:
                $sendStatus = $this::t('MESSAGE_STATUS_SENDING');
                break;
            case Notification_Info::MESSAGE_STATUS_SENT:
                $sendStatus = $this::t('MESSAGE_STATUS_SENT');
                break;
        }

        $messageInfo['message_type'] = $messageType;
        $messageInfo['status'] = $sendStatus;
        $messageInfo['time'] = $objDate->Format($messageInfo['time'], 'yyyy/MM/dd HH:mm:ss');
        $messageInfo['attempt_time'] = $objDate->Format($messageInfo['attempt_time'], 'yyyy/MM/dd HH:mm:ss');

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $messageInfo
        );
    }

    /**
     * Delete a message
     *
     * @access  public
     * @return  JSON
     */
    function DeleteMessage()
    {
        $this->gadget->CheckPermission('DeleteMessage');
        $post = $this->gadget->request->fetch(array('recipient_id', 'delete_similar'), 'post');
        $res = $this->gadget->model->load('Notification')->DeleteMessageRecipient(
            $post['recipient_id'],
            $post['delete_similar']
        );
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                $res->GetMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            $this::t('MESSAGE_DELETED'),
            RESPONSE_NOTICE
        );
    }

}