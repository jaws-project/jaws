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
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));

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
        $tpl->SetVariable('lbl_gadget', _t('GLOBAL_GADGET'));
        $tpl->SetVariable('lbl_message_type', _t('NOTIFICATION_MESSAGE_TYPE'));
        $tpl->SetVariable('lbl_from_date', _t('NOTIFICATION_FROM_DATE'));
        $tpl->SetVariable('lbl_to_date', _t('NOTIFICATION_TO_DATE'));
        $tpl->SetVariable('lbl_contact', _t('NOTIFICATION_CONTACT'));
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
        if (!empty($filters['from_date']) || !empty($filters['to_date'])) {
            $filters['insert_date'] = array($filters['from_date'], $filters['to_date']);
        }
        unset($filters['from_date'], $filters['to_date']);

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
     * Get a Sold Policy info
     *
     * @access  public
     * @return  JSON
     */
    function GetMessage()
    {
        $this->gadget->CheckPermission('Messages');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $messageInfo = $this->gadget->model->load('Messages')->GetMessage($id);
        if (Jaws_Error::IsError($messageInfo)) {
            return $messageInfo;
        }

        $objDate = Jaws_Date::getInstance();
        $messageInfo['policy_total_premium'] = number_format($messageInfo['policy_total_premium']);
        $messageInfo['payment_time'] = $objDate->Format($messageInfo['payment_time'], 'Y/m/d');
        $messageInfo['insert_time'] = $objDate->Format($messageInfo['insert_time'], 'Y/m/d H:i:s');
        
        return $messageInfo;
    }

}