<?php
/**
 * Subscription Model
 *
 * @category    GadgetModel
 * @package     Subscription
 */
class Subscription_Model_Subscription extends Jaws_Gadget_Model
{
    /**
     * Gets list of subscription support gadgets
     *
     * @access  public
     * @return  array   List of subscription supportgadgets
     */
    function GetSubscriptionGadgets()
    {
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $info) {
            if (is_file(ROOT_JAWS_PATH . "gadgets/$gadget/Hooks/Subscription.php")) {
                $gadgets[$gadget] = $info['title'];
                continue;
            }
            unset($gadgets[$gadget]);
        }

        return $gadgets;
    }


    /**
     * Update user subscription
     *
     * @access  public
     * @param   int     $user           User ID
     * @param   string  $email          User Email
     * @param   string  $mobile         User Mobile number
     * @param   array   $webPush        User Web push api params
     * @param   array   $selectedItems  Selected item's for subscription
     * @return  bool    True or  error
     */
    function UpdateSubscription($user, $email, $mobile, $webPush, $selectedItems)
    {
        if (empty($user) && empty($email) && empty($mobile) && empty($webPush)) {
            return false;
        }

        $references = array();
        $webPush = empty($webPush)? '' : serialize($webPush);
        foreach ($selectedItems as $item) {
            // explode string like this Forums_forum_1 (gadget_action_reference)
            $itemData = explode('_', $item);
            if (count($itemData) != 3) {
                continue;
            }
            $gadget = $itemData['0'];
            $action = $itemData['1'];
            $reference = (int)$itemData['2'];
            $references[] = array($user, $email, $mobile, $webPush, $gadget, $action, $reference, time());
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        // delete old user's subscription data
        $result = $objORM->table('subscription')->delete()
            ->where('user', $user, '=', empty($user))
            ->or()
            ->where('email', $email, '=', empty($email))
            ->or()
            ->where('mobile_number', $mobile, '=', empty($mobile))
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // insert user subscription data to DB
        if (!empty($references)) {
            $result = $objORM->table('subscription')->insertAll(
                array('user', 'email', 'mobile_number', 'web_push', 'gadget', 'action', 'reference', 'insert_time'),
                $references
            )->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        //commit transaction
        $objORM->commit();
        return true;
    }


    /**
     * Update user's gadget subscription
     *
     * @access  public
     * @param   int         $user           User ID
     * @param   string      $email          User Email
     * @param   string      $mobile         User Mobile number
     * @param   string      $webPush        User Web Push parameters
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @param   bool        $isSubscribe    Subscribe to this?
     * @return  bool        True or error
     */
    function UpdateGadgetSubscription($user, $email, $mobile, $webPush, $gadget, $action, $reference, $isSubscribe)
    {
        if (empty($user) && empty($email) && empty($mobile)) {
            return false;
        }

        // delete old user gadget-action subscription
        $res = $this->RemoveGadgetSubscription($user, $email, $mobile, $gadget, $action, $reference);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        if (!$isSubscribe) {
            return true;
        }

        return Jaws_ORM::getInstance()->table('subscription')->insert(
            array(
                'user' => $user,
                'email' => $email,
                'mobile_number' => $mobile,
                'web_push' => $webPush,
                'gadget' => $gadget,
                'action' => $action,
                'reference' => $reference,
                'insert_time' => time()
            ))->exec();
    }


    /**
     * Remove user gadget subscription
     *
     * @access  public
     * @param   int         $user           User ID
     * @param   string      $email          User Email
     * @param   string      $mobile         User Mobile number
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return bool True or error
     */
    function RemoveGadgetSubscription($user, $email, $mobile, $gadget, $action, $reference)
    {
        if (empty($user) && empty($email) && empty($mobile)) {
            return false;
        }

        $table = Jaws_ORM::getInstance()->table('subscription')
            ->delete()->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->and()->where('reference', $reference);

        if (!empty($user)) {
            $table->and()->where('user', $user);
        } else if (!empty($email)) {
            $table->and()->where('email', $email);
        } else if (!empty($email)) {
            $table->and()->where('mobile_number', $mobile);
        }

        return $table->exec();
    }


    /**
     * Get user subscribe to the item?
     *
     * @access  public
     * @param   int         $user           User ID
     * @param   string      $email          User Email
     * @param   string      $mobile         User Mobile number
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return  bool        True or error
     */
    function GetUserSubscription($user, $email, $mobile, $gadget, $action, $reference)
    {
        $table = Jaws_ORM::getInstance()->table('subscription');
        $table->select('count(id):integer');
        if (!empty($user)) {
            $table->where('user', (int)$user);
        } else if (!empty($email)){
            $table->where('email', $email);
        } else {
            $table->where('mobile_number', $mobile);
        }
        $items = $table->and()->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->and()->where('reference', $reference)
            ->fetchOne();

        if(Jaws_Error::IsError($items)) {
            return $items;
        }

        return ($items > 0) ? true : false;
    }


    /**
     * Get user all subscriptions
     *
     * @access  public
     * @param   int         $user       User ID
     * @param   string      $email      User Email
     * @param   string      $mobile     User Mobile number
     * @return  bool        True or error
     */
    function GetUserSubscriptions($user, $email = null, $mobile = null)
    {
        $table = Jaws_ORM::getInstance()->table('subscription');
        $table->select('gadget', 'action', 'reference');
        if (!empty($user)) {
            $table->where('user', (int)$user);
        } else if (!empty($email)) {
            $table->where('email', $email);
        } else {
            $table->where('mobile_number', $mobile);
        }
        return $table->fetchAll();
    }


    /**
     * Update users subscriptions
     *
     * @access  public
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return  bool        True or error
     */
    function GetUsersSubscriptions($gadget, $action, $reference)
    {
        $sTable = Jaws_ORM::getInstance()->table('subscription');
        return $sTable->select('user:integer', 'email', 'mobile_number', 'web_push')
            ->where('gadget', $gadget)->and()
            ->where('action', $action)->and()
            ->where('reference', $reference)
            ->fetchAll();
    }
}