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
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_message_type', _t('NOTIFICATION_MESSAGE_TYPE'));
        $this->gadget->define('lbl_gadget', _t('GLOBAL_GADGET'));
        $this->gadget->define('lbl_insert_time', _t('GLOBAL_CREATETIME'));
        $this->gadget->define('lbl_status', _t('GLOBAL_STATUS'));

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
        $tpl->SetVariable('lbl_recipient', _t('NOTIFICATION_RECIPIENT'));
        $tpl->SetVariable('lbl_back', _t('GLOBAL_BACK'));

        $tpl->SetBlock('Messages/filter_from_date');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'filter_from_date'));
        $tpl->ParseBlock('Messages/filter_from_date');

        $tpl->SetBlock('Messages/filter_to_date');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'filter_to_date'));
        $tpl->ParseBlock('Messages/filter_to_date');

//        $policyTypes = array(
//            IICBase_Info::NOTIFICATION_PACKAGE_TYPE_FIRE_HAMI => _t('NOTIFICATION_PACKAGE_TYPE_FIRE_HAMI'),
//            IICBase_Info::NOTIFICATION_PACKAGE_TYPE_FIRE_SAFAR => _t('NOTIFICATION_PACKAGE_TYPE_FIRE_SAFAR'),
//            IICBase_Info::NOTIFICATION_PACKAGE_TYPE_CAR_THIRDPARTY => _t('NOTIFICATION_PACKAGE_TYPE_CAR_THIRDPARTY'),
//            IICBase_Info::NOTIFICATION_PACKAGE_TYPE_CAR_HULL => _t('NOTIFICATION_PACKAGE_TYPE_CAR_HULL'),
//        );
//        foreach ($policyTypes as $value => $title) {
//            $tpl->SetBlock('Messages/filter_policy_type');
//            $tpl->SetVariable('value', $value);
//            $tpl->SetVariable('title', $title);
//            $tpl->ParseBlock('Messages/filter_policy_type');
//        }

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

        $model = $this->gadget->model->load('Insurance');
        $requests = $model->GetMessages($post['filters'], $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($requests)) {
            return $this->gadget->session->response(
                $requests->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $objDate = Jaws_Date::getInstance();
        foreach ($requests as &$request) {
            $request['insurer_name'] = $request['insurer_first_name'] . ' ' . $request['insurer_last_name'];

//            $policyType = '';
//            switch ($request['policy_type']) {
//                case IICBase_Info::NOTIFICATION_PACKAGE_TYPE_FIRE_HAMI:
//                    $policyType = _t('NOTIFICATION_PACKAGE_TYPE_FIRE_HAMI');
//                    break;
//                case IICBase_Info::NOTIFICATION_PACKAGE_TYPE_FIRE_SAFAR:
//                    $policyType = _t('NOTIFICATION_PACKAGE_TYPE_FIRE_SAFAR');
//                    break;
//                case IICBase_Info::NOTIFICATION_PACKAGE_TYPE_CAR_THIRDPARTY:
//                    $policyType = _t('NOTIFICATION_PACKAGE_TYPE_CAR_THIRDPARTY');
//                    break;
//                case IICBase_Info::NOTIFICATION_PACKAGE_TYPE_CAR_HULL:
//                    $policyType = _t('NOTIFICATION_PACKAGE_TYPE_CAR_HULL');
//                    break;
//            }
//            $request['policy_type'] = $policyType;

            $request['insert_time'] = $objDate->Format($request['insert_time'], 'Y/m/d H:i:s');
        }
        $requestsCount = $model->GetMessagesCount($post['filters']);

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $requestsCount,
                'records' => $requests
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
        $messageInfo = $this->gadget->model->load('Insurance')->GetMessage($id);
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