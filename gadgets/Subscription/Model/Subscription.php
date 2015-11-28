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
            if (is_file(JAWS_PATH . "gadgets/$gadget/Hooks/Subscription.php")) {
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
     * @param   int     $uid            User ID
     * @param   string  $email          User Email
     * @param   array   $selectedItems  Selected item's for subscription
     * @return  bool    True or  error
     */
    function UpdateSubscription($uid, $email, $selectedItems)
    {
        if (empty($selectedItems)) {
            return false;
        }

        $gadgetActions = array();
        foreach ($selectedItems as $item) {
            // explode string like this Forums_forum_1 (gadget_action_reference)
            $itemData = explode('_', $item);
            if (count($itemData) != 3) {
                continue;
            }
            $gadget = $itemData['0'];
            $action = $itemData['1'];
            $reference = (int)$itemData['2'];
            $references[] = array($uid, $email, $gadget, $action, $reference, time());
            $gadgetActions[$gadget][$action] = $action;
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        // delete old user subscription data
        if (count($gadgetActions) > 0) {
            foreach ($gadgetActions as $gadget => $actions) {
                foreach ($actions as $action) {
                    $table = $objORM->table('subscription');
                    $res = $table->delete()->where('gadget', $gadget)->and()->where('action', $action)->exec();
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                }
            }
        }

        // insert user subscription data to DB
        $table = $objORM->table('subscription');
        $result = $table->insertAll(
            array('uid', 'email', 'gadget', 'action', 'reference', 'insert_time'),
            $references
        )->exec();

        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //commit transaction
        $objORM->commit();
        return true;
    }


    /**
     * Update user's gadget subscription
     *
     * @access  public
     * @param   int         $uid            User ID
     * @param   string      $email          User Email
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @param   bool        $isSubscribe    Subscribe to this?
     * @return  bool        True or error
     */
    function UpdateGadgetSubscription($uid, $email, $gadget, $action, $reference, $isSubscribe)
    {
        if(empty($uid) && empty($email)) {
            return false;
        }

        // delete old user gadget-action subscription
        $res = $this->RemoveGadgetSubscription($uid, $email, $gadget, $action, $reference);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        if (!$isSubscribe) {
            return true;
        }

        return Jaws_ORM::getInstance()->table('subscription')->insert(
            array(
                'uid' => $uid,
                'email' => $email,
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
     * @param   int         $uid            User ID
     * @param   string      $email          User Email
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return bool True or error
     */
    function RemoveGadgetSubscription($uid, $email, $gadget, $action, $reference)
    {
        if(empty($uid) && empty($email)) {
            return false;
        }

        $table = Jaws_ORM::getInstance()->table('subscription')
            ->delete()->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->and()->where('reference', $reference);

        if (!empty($uid)) {
            $table->and()->where('uid', $uid);
        } else if (!empty($email)) {
            $table->and()->where('email', $email);
        }

        return $table->exec();
    }


    /**
     * Get user subscribe to the item?
     *
     * @access  public
     * @param   int         $uid            User ID
     * @param   string      $email          User Email
     * @param   string      $gadget         Gadget name
     * @param   string      $action         Action name
     * @param   int         $reference      Reference Id
     * @return  bool        True or error
     */
    function GetUserSubscription($uid, $email, $gadget, $action, $reference)
    {
        $table = Jaws_ORM::getInstance()->table('subscription');
        $table->select('count(id):integer');
        if (!empty($uid)) {
            $table->where('uid', (int)$uid);
        } else {
            $table->where('email', $email);
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
     * @param   int         $uid        User ID
     * @param   string      $email      User Email
     * @return  bool        True or error
     */
    function GetUserSubscriptions($uid, $email)
    {
        $table = Jaws_ORM::getInstance()->table('subscription');
        $table->select('gadget', 'action', 'reference');
        if (!empty($uid)) {
            $table->where('uid', (int)$uid);
        } else {
            $table->where('email', $email);
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
        return $sTable->select('uid:integer', 'email')
            ->where('gadget', $gadget)->and()
            ->where('action', $action)->and()
            ->where('reference', $reference)
            ->fetchAll();
    }
}