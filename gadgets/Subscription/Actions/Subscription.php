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
        $selectedItems = array();
        if (isset($response['data'])) {
            $email = $response['data']['email'];
            $selectedItems = $response['data']['subscriptionItems'];
        } else {
            if (!empty($currentUser)) {
                $userSubscriptions = $sModel->GetUserSubscriptions($currentUser, null);
                if (count($userSubscriptions) > 0) {
                    foreach ($userSubscriptions as $item) {
                        $selectedItems[] = $item['gadget'] . '_' . $item['action'] . '_' . $item['reference'];
                    }
                }
            }
        }

        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->SetVariable('title', _t('SUBSCRIPTION_SUBSCRIPTION'));
        $this->SetTitle(_t('SUBSCRIPTION_SUBSCRIPTION'));

        if (empty($currentUser)) {
            $tpl->SetBlock('subscription/email');
            $tpl->SetVariable('email', $email);
            $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
            $tpl->ParseBlock('subscription/email');
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

            // fetch level 0 (root) items
            $rootItems = $objHook->Execute(0);

            // fetch level 1 items
            $levelOneItems = $objHook->Execute(1);

            foreach ($rootItems as $item) {
                $tpl->SetBlock('subscription/gadget/item');
                $tpl->SetVariable('title', $item['title']);

                // display checkbox?
                if(!isset($item['selectable']) || (isset($item['selectable']) && $item['selectable']==true ) ) {
                    $checkboxName = $gadget . '_' . $item['action'] . '_' . $item['id'];
                    $tpl->SetVariable('id', $checkboxName);

                    // check selected item
                    if (in_array($checkboxName, $selectedItems)) {
                        $tpl->SetVariable('checked', 'checked');
                    } else {
                        $tpl->SetVariable('checked', '');
                    }
                }

                if (!empty($levelOneItems) &&
                    isset($levelOneItems[$item['id']]) &&
                    count($levelOneItems[$item['id']]) > 0) {
                    foreach ($levelOneItems[$item['id']] as $subItem) {
                        $tpl->SetBlock('subscription/gadget/item/subItem');
                        $checkboxName = $gadget . '_' . $subItem['action'] . '_' . $subItem['id'];
                        $tpl->SetVariable('id', $checkboxName);
                        $tpl->SetVariable('title', $subItem['title']);

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
        $post = jaws()->request->fetch(array('email', 'subscriptionItems:array'), 'post');
        $email = $post['email'];
        $selectedItems = empty($post['subscriptionItems']) ?
            array(jaws()->request->fetch('subscriptionItems', 'post')) : $post['subscriptionItems'];

       $sModel = $this->gadget->model->load('Subscription');
        $result = $sModel->UpdateSubscription(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $email,
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

        Jaws_Header::Location($this->gadget->urlMap('Subscription'), 'Subscription.Subscription');
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
        $isSubscribed = $sModel->GetUserSubscription($currentUser, null, $gadget, $action, $reference);

        if ($isSubscribed) {
            $tpl->SetVariable('checked', 'checked');
        } else {
            $tpl->SetVariable('checked', '');
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
                'email', 'subscription_gadget', 'subscription_action', 'reference', 'subscribe'
            ),
            'post'
        );
        $email = $post['email'];

        $sModel = $this->gadget->model->load('Subscription');
        $result = $sModel->UpdateGadgetSubscription(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $email,
            $post['subscription_gadget'],
            $post['subscription_action'],
            $post['reference'],
            $post['subscribe']
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