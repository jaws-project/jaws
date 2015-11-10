<?php
/**
 * Rating Gadget
 *
 * @category    Gadget
 * @package     Rating
 */
class Rating_Actions_Rating extends Jaws_Gadget_Action
{
    /**
     * Get reference rating
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $action         Action name
     * @param   int     $reference      Reference ID
     * @param   int     $item           Item number
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @return  void
     */
    function loadReferenceRating($gadget, $action, $reference, $item, &$tpl, $tpl_base_block)
    {
        $rModel = $this->gadget->model->load('Rating');
        // get rating statistics
        $rating = $rModel->GetRating($gadget, $action, $reference, $item);
        if (Jaws_Error::IsError($rating)) {
            return $rating;
        } elseif (empty($rating)) {
            $rating = array(
                'rates_total' => 0,
                'rates_sum'   => 0,
            );
        }

        // get user last rating
        $usrRating = $rModel->GetUserRating($gadget, $action, $reference, $item);
        if (Jaws_Error::IsError($rating)) {
            return $usrRating;
        }

        $tpl->SetBlock("$tpl_base_block/rating");
        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('item', $item);
        $tpl->SetVariable('rates_total', $rating['rates_total']);
        $tpl->SetVariable('rates_sum', $rating['rates_sum']);
        $tpl->SetVariable('rates_mean', $rating['rates_total'] == 0? 0 : ($rating['rates_sum']/$rating['rates_total']));
        for ($i = 1; $i <= 5; $i++) {
            $tpl->SetBlock("$tpl_base_block/rating/item");
            $tpl->SetVariable('value', $i);
            if ($i == $usrRating) {
                $tpl->SetBlock("$tpl_base_block/rating/item/checked");
                $tpl->ParseBlock("$tpl_base_block/rating/item/checked");
            }
            $tpl->SetVariable('checked', '');
            $tpl->ParseBlock("$tpl_base_block/rating/item");
        }
        $tpl->ParseBlock("$tpl_base_block/rating");
    }


    /**
     * Updates rating
     *
     * @access  public
     * @return  void
     */
    function PostRating()
    {
        $post = jaws()->request->fetch(
            array('requested_gadget', 'requested_action', 'reference', 'item', 'rate'),
            'post'
        );

        $rModel = Jaws_Gadget::getInstance('Rating')->model->load('Rating');
        // update user rate
        $result = $rModel->UpdateUserRating(
            $post['requested_gadget'],
            $post['requested_action'],
            $post['reference'],
            $post['item'],
            $post['rate']
        );
        if (!Jaws_Error::IsError($result)) {
            // get rating statistics
            $result = $rModel->GetRating(
                $post['requested_gadget'],
                $post['requested_action'],
                $post['reference'],
                $post['item']
            );
            if (!Jaws_Error::IsError($result)) {
                if (empty($result)) {
                    $result = array(
                        'rates_total' => 0,
                        'rates_sum'   => 0,
                    );
                }
            }
        }
        
        return $result;
    }

}