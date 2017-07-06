<?php
/**
 * Subscription Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Subscription
 */
class Subscription_Actions_Admin_Subscription extends Subscription_Actions_Admin_Default
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Subscription()
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmSubscriptionDelete', _t('GLOBAL_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->loadAdmin('Subscription.html');
        $tpl->SetBlock('Subscription');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Subscription'));

        // Users Filter
        $usersCombo =& Piwi::CreateWidget('Combo', 'filter_user');
        $usersCombo->AddOption(_t('GLOBAL_ALL_USERS'), "", false);
        $userModel = new Jaws_User();
        $users = $userModel->GetUsers();
        if (!Jaws_Error::IsError($users)) {
            foreach ($users as $user) {
                $usersCombo->AddOption($user['username'] . ' - ' . $user['nickname'], $user['id']);
            }
        }
        $usersCombo->AddEvent(ON_CHANGE, "javascript:searchSubscription();");
        $usersCombo->SetDefault(-1);
        $tpl->SetVariable('filter_user', $usersCombo->Get());
        $tpl->SetVariable('lbl_filter_user', _t('LOGS_USERS'));

        // Email
        $email =& Piwi::CreateWidget('Entry', 'filter_email');
        $email->AddEvent(ON_CHANGE, "javascript:searchSubscription();");
        $tpl->SetVariable('filter_email', $email->Get());
        $tpl->SetVariable('lbl_filter_email', _t('GLOBAL_EMAIL'));

        // Gadgets Filter
        $gadgets = $this->gadget->model->load('Subscription')->GetSubscriptionGadgets();
        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
        $gadgetsCombo->AddOption(_t('GLOBAL_ALL'), "", false);
        foreach ($gadgets as $name=>$title) {
            $gadgetsCombo->AddOption($title, $name);
        }
        $gadgetsCombo->AddEvent(ON_CHANGE, "javascript:searchSubscription();");
        $gadgetsCombo->SetDefault(-1);
        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
        $tpl->SetVariable('lbl_filter_gadget', _t('GLOBAL_GADGETS'));

        // Order
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('GLOBAL_DATE'). ' &darr;', 'insert_time');
        $orderType->AddOption(_t('GLOBAL_DATE'). ' &uarr;', 'insert_time desc');
        $orderType->AddEvent(ON_CHANGE, "javascript:searchSubscription();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', _t('SUBSCRIPTION_ORDER_TYPE'));

        //DataGrid
        $tpl->SetVariable('datagrid', $this->SubscriptionDataGrid());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'subscriptions_actions');
        $actions->SetID('subscriptions_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        if ($this->gadget->GetPermission('DeleteSubscription')) {
            $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        }
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeSubscriptionAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:subscriptionDGAction($('#subscriptions_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());


        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->ParseBlock('Subscription');
        return $tpl->Get();
    }

    /**
     * Builds Subscription datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function SubscriptionDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('subscription_datagrid');
        $grid->useMultipleSelection();
        $grid->pageBy(15);

        $column1 = Piwi::CreateWidget('Column', _t('SUBSCRIPTION_USER'), null, false);
        $column1->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_EMAIL'), null, false);
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('SUBSCRIPTION_MOBILE_NUMBER'), null, false);
        $column3->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('SUBSCRIPTION_GADGET'), null, false);
        $column4->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('SUBSCRIPTION_ACTION'), null, false);
        $column5->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column5);

        $column6 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column6->SetStyle('width:128px; white-space:nowrap;');
        $grid->AddColumn($column6);

        return $grid->Get();
    }

    /**
     * Return list of subscription data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetSubscriptions()
    {
        $post = $this->gadget->request->fetch(array('offset', 'order', 'filters:array'), 'post');
        $filters = $post['filters'];

        $model = $this->gadget->model->loadAdmin('Subscription');
        $subscriptions = $model->GetSubscriptions($filters, 15, $post['offset'], $post['order']);
        if (Jaws_Error::IsError($subscriptions)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $gridData = array();
        foreach ($subscriptions as $subscription) {
            $subscriptionData = array();
            $subscriptionData['__KEY__'] = $subscription['id'];

            // User
            $subscriptionData['username'] = $subscription['username'];
            // Email
            $subscriptionData['email'] = $subscription['email'];
            // Mobile number
            $subscriptionData['mobile_number'] = $subscription['mobile_number'];
            // Mobile number
            $subscriptionData['mobile_number'] = $subscription['mobile_number'];
            // Gadget
            if (!empty($subscription['gadget'])) {
                $subscriptionData['gadget'] = _t(strtoupper($subscription['gadget'] . '_TITLE'));
            } else {
                $subscriptionData['gadget'] = '';
            }
            // Action
            $subscriptionData['action'] = $subscription['action'];
            // Date
            $subscriptionData['date'] = $date->Format($subscription['insert_time']);
            $gridData[] = $subscriptionData;
        }
        return $gridData;
    }

    /**
     * Get subscriptions count
     *
     * @access  public
     * @return  int     Total of subscriptions
     */
    function GetSubscriptionsCount()
    {
        $filters = $this->gadget->request->fetch('filters:array', 'post');
        $model = $this->gadget->model->loadAdmin('Subscription');
        return $model->GetSubscriptionsCount($filters);
    }

    /**
     * Delete subscriptions
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteSubscriptions()
    {
        $this->gadget->CheckPermission('DeleteSubscription');
        $subscriptionsID = $this->gadget->request->fetchAll();
        $model = $this->gadget->model->loadAdmin('Subscription');
        $res = $model->DeleteSubscriptions($subscriptionsID);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('SUBSCRIPTION_ERROR_CANT_DELETE_SUBSCRIPTIONS'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('SUBSCRIPTION_SUBSCRIPTION_DELETED'), RESPONSE_NOTICE);
        }
    }
}