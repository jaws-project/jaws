<?php
/**
 * Subscription Gadget
 *
 * @category    Gadget
 * @package     Subscription
 */
class Subscription_Actions_Subscription extends Jaws_Gadget_Action
{

    /**
     * Show all gadget's subscription items
     *
     * @access  public
     * @return  string  XHTML
     */
    function Subscription()
    {
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('Subscription.html');
        $tpl->SetBlock('subscription');

        $sModel = $this->gadget->model->load('Subscription');
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $response = $GLOBALS['app']->Session->PopResponse('Subscription.Subscription');
        $email = '';
        $mobile = '';
        $selectedItems = array();
        if (isset($response['data'])) {
            $email = $response['data']['email'];
            $mobile = $response['data']['mobile'];
            $selectedItems = $response['data']['subscriptionItems'];
        } else {
            if (!empty($currentUser)) {
                $userSubscriptions = $sModel->GetUserSubscriptions($currentUser);
                if (count($userSubscriptions) > 0) {
                    foreach ($userSubscriptions as $item) {
                        $selectedItems[] = $item['gadget'] . '_' . $item['action'] . '_' . $item['reference'];
                    }
                }
            }
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->SetVariable('title', _t('SUBSCRIPTION_SUBSCRIPTION'));
        $this->SetTitle(_t('SUBSCRIPTION_SUBSCRIPTION'));

        if (empty($currentUser)) {
            $tpl->SetBlock('subscription/email');
            $tpl->SetVariable('email', $email);
            $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
            $tpl->ParseBlock('subscription/email');

            $tpl->SetBlock('subscription/mobile');
            $tpl->SetVariable('mobile', $mobile);
            $tpl->SetVariable('lbl_mobile', _t('SUBSCRIPTION_MOBILE_NUMBER'));
            $tpl->ParseBlock('subscription/mobile');
        }

        // get subscription gadgets list
        $gadgets = $sModel->GetSubscriptionGadgets();
        if (Jaws_Error::IsError($gadgets)) {
            return $gadgets;
        }

        $tpl->SetVariable('update', _t('SUBSCRIPTION_UPDATE'));

        // call gadget hook
        foreach ($gadgets as $gadget => $title) {
            $tpl->SetBlock('subscription/gadget');

            // load gadget
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }

            // load hook
            $objHook = $objGadget->hook->load('Subscription');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $tpl->SetVariable('gadget_title',  $title);

            // fetch subscription items
            $items = $objHook->Execute();

            foreach ($items as $item) {
                $tpl->SetBlock('subscription/gadget/item');
                $tpl->SetVariable('title', $item['title']);
                $tpl->SetVariable('url', $item['url']);

                // display checkbox?
                if (!isset($item['selectable']) || (isset($item['selectable']) && $item['selectable'] == true)) {
                    $tpl->SetBlock('subscription/gadget/item/checkbox');

                    $checkboxName = $gadget . '_' . $item['action'] . '_' . $item['reference'];
                    $tpl->SetVariable('id', $checkboxName);

                    // check selected item
                    if (in_array($checkboxName, $selectedItems)) {
                        $tpl->SetVariable('checked', 'checked');
                    } else {
                        $tpl->SetVariable('checked', '');
                    }
                    $tpl->ParseBlock('subscription/gadget/item/checkbox');
                }

                if (!empty($item['sub_items'])) {
                    $tpl->SetVariable('collapse', 'collapse');
                    foreach ($item['sub_items'] as $subItem) {
                        $tpl->SetBlock('subscription/gadget/item/subItem');
                        $checkboxName = $gadget . '_' . $subItem['action'] . '_' . $subItem['reference'];
                        $tpl->SetVariable('id', $checkboxName);
                        $tpl->SetVariable('title', $subItem['title']);
                        $tpl->SetVariable('url', $subItem['url']);

                        // check selected item
                        if(in_array($checkboxName, $selectedItems)) {
                            $tpl->SetVariable('checked', 'checked');
                        } else {
                            $tpl->SetVariable('checked', '');
                        }

                        $tpl->ParseBlock('subscription/gadget/item/subItem');
                    }
                }

                $tpl->ParseBlock('subscription/gadget/item');
            }

            $tpl->ParseBlock('subscription/gadget');
        }

        $tpl->ParseBlock('subscription');
        return $tpl->Get();
    }


    /**
     * Update user subscription
     *
     * @access  public
     * @return  void
     */
    function UpdateSubscription()
    {
        $post = jaws()->request->fetch(array('email', 'mobile', 'subscriptionItems:array'), 'post');
        $selectedItems = $post['subscriptionItems'];
        if(empty($selectedItems)) {
            $subscriptionItems = jaws()->request->fetch('subscriptionItems', 'post');
            $selectedItems = empty($subscriptionItems) ? null : array($subscriptionItems) ;

            if(empty($selectedItems)) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('SUBSCRIPTION_ERROR_NOT_ITEM_SELECTED'),
                    'Subscription.Subscription',
                    RESPONSE_ERROR,
                    $post
                );
                return Jaws_Header::Location($this->gadget->urlMap('Subscription'), 'Subscription.Subscription');
            }
        }

       $sModel = $this->gadget->model->load('Subscription');
        $result = $sModel->UpdateSubscription(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['email'],
            $post['mobile'],
            $selectedItems
        );
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Subscription.Subscription',
                RESPONSE_ERROR,
                $post
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('SUBSCRIPTION_SUBSCRIPTION_UPDATED'),
                'Subscription.Subscription'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Subscription'), 'Subscription.Subscription');
    }


    /**
     * Show subscription UI for display in other gadgets
     *
     * @access  public
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return string XHTML
     */
    function ShowSubscription($gadget, $action, $reference)
    {
        $tpl = $this->gadget->template->load('Subscription.html');
        $tpl->SetBlock('inline');

        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('lbl_subscription', _t('SUBSCRIPTION_SUBSCRIPTION'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        // check user current subscription
        $sModel = $this->gadget->model->load('Subscription');
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $isSubscribed = $sModel->GetUserSubscription($currentUser, null, null, $gadget, $action, $reference);

        if ($isSubscribed) {
            $tpl->SetVariable('is_subscribe', '0');
            $tpl->SetVariable('lbl_action_type', _t('SUBSCRIPTION_ACTION_UNSUBSCRIBE'));
        } else {
            $tpl->SetVariable('is_subscribe', '1');
            $tpl->SetVariable('lbl_action_type', _t('SUBSCRIPTION_ACTION_SUBSCRIBE'));
        }

        if (empty($currentUser)) {
            $tpl->SetBlock('subscription/email');
            $tpl->ParseBlock('subscription/email');

            $tpl->SetBlock('subscription/mobile');
            $tpl->ParseBlock('subscription/mobile');
        }

        $tpl->ParseBlock('inline');
        return $tpl->Get();
    }


    /**
     * Update user one subscription item
     *
     * @access  public
     * @return  void
     */
    function UpdateGadgetSubscription()
    {
        $post  = jaws()->request->fetch(
            array(
                'email', 'mobile', 'subscription_gadget', 'subscription_action',
                'subscription_reference', 'is_subscribe'
            ),
            'post'
        );

        $sModel = $this->gadget->model->load('Subscription');
        $result = $sModel->UpdateGadgetSubscription(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['email'],
            $post['mobile'],
            $post['subscription_gadget'],
            $post['subscription_action'],
            $post['subscription_reference'],
            $post['is_subscribe']
        );

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Subscription.Subscription',
                RESPONSE_ERROR,
                $post
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('SUBSCRIPTION_SUBSCRIPTION_UPDATED'),
                'Subscription.Subscription'
            );
        }

        Jaws_Header::Referrer();
    }
}