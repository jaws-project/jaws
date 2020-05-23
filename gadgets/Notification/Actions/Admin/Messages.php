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
        $this->gadget->define('lbl_message_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_message_type', _t('NOTIFICATION_MESSAGE_TYPE'));
        $this->gadget->define('lbl_shouter', _t('GLOBAL_GADGET'));
        $this->gadget->define('lbl_insert_time', _t('GLOBAL_CREATETIME'));
        $this->gadget->define('lbl_status', _t('GLOBAL_STATUS'));
        $this->gadget->define('lbl_view', _t('NOTIFICATION_VIEW'));
        $this->gadget->define('lbl_delete_message', _t('NOTIFICATION_DELETE_MESSAGE'));
        $this->gadget->define('lbl_delete_similar_message', _t('NOTIFICATION_DELETE_SIMILAR_MESSAGE'));
        $this->gadget->define('confirmDeleteMessage', _t('GLOBAL_CONFIRM_DELETE', _t('NOTIFICATION_MESSAGE')));
        $this->gadget->define('confirmDeleteSimilarMessage', _t('GLOBAL_CONFIRM_DELETE', _t('NOTIFICATION_MESSAGES_SIMILAR')));

        $tpl = $this->gadget->template->loadAdmin('Messages.html');
        $tpl->SetBlock('Messages');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Messages'));

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));

        $tpl->SetVariable('lbl_message', _t('NOTIFICATION_MESSAGE'));
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('lbl_shouter', _t('GLOBAL_GADGET'));
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_summary', _t('NOTIFICATION_MESSAGE_SUMMARY'));
        $tpl->SetVariable('lbl_verbose', _t('NOTIFICATION_MESSAGE_VERBOSE'));
        $tpl->SetVariable('lbl_message_type', _t('NOTIFICATION_MESSAGE_TYPE'));
        $tpl->SetVariable('lbl_callback', _t('NOTIFICATION_MESSAGE_CALLBACK'));
        $tpl->SetVariable('lbl_image', _t('NOTIFICATION_IMAGE'));
        $tpl->SetVariable('lbl_insert_time', _t('GLOBAL_TIME'));
        $tpl->SetVariable('lbl_attempts', _t('NOTIFICATION_MESSAGE_ATTEMPTS'));
        $tpl->SetVariable('lbl_attempt_time', _t('NOTIFICATION_MESSAGE_ATTEMPT_TIME'));
        $tpl->SetVariable('lbl_from_date', _t('NOTIFICATION_FROM_DATE'));
        $tpl->SetVariable('lbl_to_date', _t('NOTIFICATION_TO_DATE'));
        $tpl->SetVariable('lbl_contact', _t('NOTIFICATION_CONTACT'));
        $tpl->SetVariable('lbl_message_details', _t('NOTIFICATION_MESSAGE_DETAILS'));
        $tpl->SetVariable('lbl_back', _t('GLOBAL_BACK'));

        $tpl->SetBlock('Messages/filter_from_date');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'filter_from_date'));
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

        $messageTypes = array(
            Notification_Info::NOTIFICATION_MESSAGE_TYPE_EMAIL => _t('NOTIFICATION_MESSAGE_TYPE_EMAIL'),
            Notification_Info::NOTIFICATION_MESSAGE_TYPE_SMS => _t('NOTIFICATION_MESSAGE_TYPE_SMS'),
            Notification_Info::NOTIFICATION_MESSAGE_TYPE_WEB => _t('NOTIFICATION_MESSAGE_TYPE_WEB'),
        );
        foreach ($messageTypes as $value => $title) {
            $tpl->SetBlock('Messages/filter_message_type');
            $tpl->SetVariable('value', $value);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('Messages/filter_message_type');
        }

        $sendStatuses = array(
            Notification_Info::NOTIFICATION_MESSAGE_STATUS_NOT_SEND => _t('NOTIFICATION_MESSAGE_STATUS_NOT_SEND'),
            Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENDING => _t('NOTIFICATION_MESSAGE_STATUS_SENDING'),
            Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENT => _t('NOTIFICATION_MESSAGE_STATUS_SENT'),
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
            switch ($message['driver']) {
                case Notification_Info::NOTIFICATION_MESSAGE_TYPE_EMAIL:
                    $messageType = _t('NOTIFICATION_MESSAGE_TYPE_EMAIL');
                    break;
                case Notification_Info::NOTIFICATION_MESSAGE_TYPE_SMS:
                    $messageType = _t('NOTIFICATION_MESSAGE_TYPE_SMS');
                    break;
                case Notification_Info::NOTIFICATION_MESSAGE_TYPE_WEB:
                    $messageType = _t('NOTIFICATION_MESSAGE_TYPE_WEB');
                    break;
            }
            $message['message_type'] = $messageType;

            $sendStatus = '';
            switch ($message['status']) {
                case Notification_Info::NOTIFICATION_MESSAGE_STATUS_NOT_SEND:
                    $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_NOT_SEND');
                    break;
                case Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENDING:
                    $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_SENDING');
                    break;
                case Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENT:
                    $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_SENT');
                    break;
            }
            $message['status'] = $sendStatus;

            $message['time'] = $objDate->Format($message['time'], 'Y/m/d H:i:s');
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
            case Notification_Info::NOTIFICATION_MESSAGE_TYPE_EMAIL:
                $messageType = _t('NOTIFICATION_MESSAGE_TYPE_EMAIL');
                break;
            case Notification_Info::NOTIFICATION_MESSAGE_TYPE_SMS:
                $messageType = _t('NOTIFICATION_MESSAGE_TYPE_SMS');
                break;
            case Notification_Info::NOTIFICATION_MESSAGE_TYPE_WEB:
                $messageType = _t('NOTIFICATION_MESSAGE_TYPE_WEB');
                break;
        }
        $sendStatus = '';
        switch ($messageInfo['status']) {
            case Notification_Info::NOTIFICATION_MESSAGE_STATUS_NOT_SEND:
                $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_NOT_SEND');
                break;
            case Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENDING:
                $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_SENDING');
                break;
            case Notification_Info::NOTIFICATION_MESSAGE_STATUS_SENT:
                $sendStatus = _t('NOTIFICATION_MESSAGE_STATUS_SENT');
                break;
        }

        $messageInfo['message_type'] = $messageType;
        $messageInfo['status'] = $sendStatus;
        $messageInfo['time'] = $objDate->Format($messageInfo['time'], 'Y/m/d H:i:s');
        $messageInfo['attempt_time'] = $objDate->Format($messageInfo['attempt_time'], 'Y/m/d H:i:s');

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
            _t('NOTIFICATION_MESSAGE_DELETED'),
            RESPONSE_NOTICE
        );
    }

}