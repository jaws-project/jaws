<?php
/**
 * Rating Gadget
 *
 * @category    Gadget
 * @package     Rating
 */
class Rating_Actions_RatingTypes extends Jaws_Gadget_Action
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
                'rates_count' => 0,
                'rates_sum'   => 0,
                'rates_avg'   => 0,
            );
        }

        // get user last rating
        $usrRating = $rModel->GetUserRating($gadget, $action, $reference, $item);
        if (Jaws_Error::IsError($rating)) {
            return $usrRating;
        }

        $this->app->layout->addScript('gadgets/Rating/Resources/index.js');
        $tpl->SetBlock("$tpl_base_block/rating");
        $tpl->SetVariable('lbl_rating', $this::t('RATING'));
        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('item', $item);
        $tpl->SetVariable('rates_count', $rating['rates_count']);
        $tpl->SetVariable('rates_sum', $rating['rates_sum']);
        $tpl->SetVariable('rates_avg', $rating['rates_avg']);
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
     * Get reference rating
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(reference, pagination_data, user, per_page, order_by, ...)
     * @return  array   Tag's template variables
     */
    function xloadReferenceRating($interface = array(), $options = array())
    {
        $defaultOptions = array(
            'item'       => 0,
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);

        $rModel = $this->gadget->model->load('Rating');

        // get rating statistics
        $rating = $rModel->GetRating(
            $interface['gadget'],
            $interface['action'],
            $interface['reference'],
            $options['item']
        );
        if (Jaws_Error::IsError($rating) || empty($rating)) {
            $rating = array(
                'rates_count' => 0,
                'rates_sum'   => 0,
                'rates_avg'   => 0,
            );
        }

        // get user last rating
        $usrRating = $rModel->GetUserRating(
            $interface['gadget'],
            $interface['action'],
            $interface['reference'],
            $options['item']
        );
        if (Jaws_Error::IsError($usrRating) || empty($usrRating)) {
            $usrRating = 0;
        }

        $this->app->layout->addScript('gadgets/Rating/Resources/index.js');

        // initiate assign with option array
        $assigns = array();
        $assigns['gadget'] = $interface['gadget'];
        $assigns['action'] = $interface['action'];
        $assigns['reference'] = $interface['reference'];
        $assigns['rates_count'] = $rating['rates_count'];
        $assigns['rates_sum'] = $rating['rates_sum'];
        $assigns['rates_avg'] = $rating['rates_avg'];
        $assigns['user_rating'] = $usrRating;
        return $assigns;
    }


    /**
     * Get reference like rating
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
    function loadReferenceLike($gadget, $action, $reference, $item, &$tpl, $tpl_base_block)
    {
        $rModel = $this->gadget->model->load('Rating');
        // get rating statistics
        $rating = $rModel->GetRating($gadget, $action, $reference, $item);
        if (Jaws_Error::IsError($rating)) {
            return $rating;
        } elseif (empty($rating)) {
            $rating = array(
                'rates_count' => 0,
            );
        }

        // get user last rating
        $usrRating = $rModel->GetUserRating($gadget, $action, $reference, $item);
        if (Jaws_Error::IsError($rating)) {
            return $usrRating;
        }

        $this->app->layout->addScript('gadgets/Rating/Resources/index.js');
        $tpl->SetBlock("$tpl_base_block/like");
        $tpl->SetVariable('lbl_like', $this::t('LIKE'));
        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('item', $item);
        $tpl->SetVariable('rates_count', (int)$rating['rates_count']);
        if ($usrRating) {
            $tpl->SetBlock("$tpl_base_block/like/checked");
            $tpl->ParseBlock("$tpl_base_block/like/checked");
        }

        $tpl->ParseBlock("$tpl_base_block/like");
    }

}