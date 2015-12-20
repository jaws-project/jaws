<?php
/**
 * Subscription Model
 *
 * @category    GadgetModel
 * @package     Subscription
 */
class Subscription_Model_Admin_Subscription extends Jaws_Gadget_Model
{
    /**
     * Get subscriptions
     *
     * @access  public
     * @param   array   $filters
     * @param   bool    $limit
     * @param   int     $offset
     * @param   string  $order
     * @return  bool    True or error
     */
    function GetSubscriptions($filters = null, $limit = false, $offset = null, $order = 'insert_time')
    {
        $sTable = Jaws_ORM::getInstance()->table('subscription')
            ->select('subscription.id:integer', 'subscription.user:integer', 'subscription.email',
                'subscription.mobile_number', 'subscription.gadget', 'subscription.action',
                'subscription.reference', 'subscription.insert_time', 'users.username');

        $sTable->join('users', 'subscription.user', 'users.id', 'left');
        if (!empty($filters) && count($filters) > 0) {
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $sTable->and()->where('subscription.user', $filters['user']);
            }
            // email
            if (isset($filters['email']) && !empty($filters['email'])) {
                $sTable->and()->where('subscription.email', '%' . $filters['email'] . '%', 'like');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $sTable->and()->where('subscription.gadget', $filters['gadget']);
            }
        }
        return $sTable->limit((int)$limit, $offset)->orderBy($order)->fetchAll();
    }

    /**
     * Get subscriptions count
     *
     * @access  public
     * @param   array   $filters
     * @return  bool    True or error
     */
    function GetSubscriptionsCount($filters = null)
    {
        $sTable = Jaws_ORM::getInstance()->table('subscription')->select('count(*)');
        if (!empty($filters) && count($filters) > 0) {
            // user
            if (isset($filters['user']) && !empty($filters['user'])) {
                $sTable->and()->where('subscription.user', $filters['user']);
            }
            // email
            if (isset($filters['email']) && !empty($filters['email'])) {
                $sTable->and()->where('subscription.email', '%' . $filters['email'] . '%', 'like');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $sTable->and()->where('subscription.gadget', $filters['gadget']);
            }
        }
        return $sTable->fetchOne();
    }

    /**
     * Delete subscriptions
     *
     * @access  public
     * @param   array   $ids   Subscriptions Ids
     * @return bool True or error
     */
    function DeleteSubscriptions($ids)
    {
        return Jaws_ORM::getInstance()->table('subscription')->delete()->where('id', $ids, 'in')->exec();
    }
}