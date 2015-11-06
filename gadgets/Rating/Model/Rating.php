<?php
/**
 * Rating Model
 *
 * @category    GadgetModel
 * @package     Rating
 */
class Rating_Model_Rating extends Jaws_Gadget_Model
{
    /**
     * Get rating of a reference item
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference
     * @param   int     $item       rating item
     * @return  mixed   Array of rating information or Jaws_Error on failure
     */
    function GetRating($gadget, $action, $reference, $item = 0)
    {
        $ratingTable = Jaws_ORM::getInstance()->table('rating');
        $ratingTable->select(
            'gadget', 'action', 'reference:integer', 'item:integer', 'rates_total:integer',
            'rates_sum:integer', 'restricted:boolean', 'allowed:boolean',
            'insert_time:integer', 'update_time:integer'
        );
        $ratingTable->where('gadget', $gadget)
            ->and()
            ->where('action', $action)
            ->and()
            ->where('reference', $reference)
            ->and()
            ->where('item', $item);
        return $ratingTable->fetchRow();
    }

    /**
     * Get user rate of a reference item
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference
     * @param   int     $item       rating item
     * @return  mixed   Rate value or Jaws_Error on failure
     */
    function GetUserRating($gadget, $action, $reference, $item = 0)
    {
        $uip = inet_pton($_SERVER['REMOTE_ADDR']);
        $ratingTable = Jaws_ORM::getInstance()->table('rating_details');
        $ratingTable->select('rating_details.rate')
            ->join('rating', 'rating.id', 'rating_details.rid')
            ->where('rating.gadget', $gadget)
            ->and()
            ->where('rating.action', $action)
            ->and()
            ->where('rating.reference', $reference)
            ->and()
            ->where('rating.item', $item)
            ->and()
            ->where('rating_details.uip', $uip);
        return $ratingTable->fetchOne();
    }

    /**
     * Update user rate of a reference item
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference
     * @param   int     $item       Rating item
     * @param   int     $rate       User rate(if null user old rate will be removed)
     * @return  mixed   Rate value or Jaws_Error on failure
     */
    function UpdateUserRating($gadget, $action, $reference, $item = 0, $rate = null)
    {
        $objORM = Jaws_ORM::getInstance();
        // fetch reference item from parent table(rating)
        $rid = $objORM->table('rating')
            ->upsert(array('gadget'=> $gadget, 'action'=> $action, 'reference'=> $reference, 'item'=> $item))
            ->where('gadget', $gadget)
            ->and()
            ->where('action', $action)
            ->and()
            ->where('reference', $reference)
            ->and()
            ->where('item', $item)
            ->exec();
        if (Jaws_Error::IsError($rid)) {
            return $rid;
        }

        // insert/update user rate
        $uip = inet_pton($_SERVER['REMOTE_ADDR']);
        $objORM->beginTransaction();
        if (is_null($rate)) {
            // delete user rate
            $result = $objORM->table('rating_details')
                ->delete()
                ->where('rid', $rid)->and()->where('uip', $uip)
                ->exec();
        } else {
            // update/insert user rate
            $result = $objORM->table('rating_details')
                ->upsert(array('rid' => $rid, 'uip' => $uip, 'rate' => (int)$rate))
                ->where('rid', $rid)
                ->and()
                ->where('uip', $uip)
                ->exec();
        }
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // update rating statistics
        $result = $objORM->table('rating')->update(
            array(
                'rates_total' => Jaws_ORM::getInstance()
                    ->table('rating_details')->select('count(id)')->where('rid', $rid),
                'rates_sum'   => Jaws_ORM::getInstance()
                    ->table('rating_details')->select('sum(rate)')->where('rid', $rid),
            )
        )->where('id', $rid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //commit transaction
        $objORM->commit();
        return true;
    }

}