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
     * Get then MostRatted action params
     *
     * @access  public
     * @return  array list of the MostRatted action params
     */
    function MostRattedLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Rating');
        $gadgets = $model->GetRateableGadgets();
        array_unshift($gadgets, _t('GLOBAL_ALL'));
        $result[] = array(
            'title' => _t('GLOBAL_GADGET'),
            'value' => $gadgets
        );

        return $result;
    }


    /**
     * Display most rated references
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  string  XHTML template content
     */
    function MostRatted($gadget)
    {
        $model = $this->gadget->model->load('Rating');
        $references = $model->GetMostRatted($gadget);
        if (Jaws_Error::IsError($references) || empty($references)) {
            return false;
        }

        $rateableGadgets = $model->GetRateableGadgets();
        $gadgetReferences = array();
        // grouping references by gadget/action for one time call hook per gadget
        foreach ($references as $reference) {
            if (!array_key_exists($reference['gadget'], $rateableGadgets)) {
                continue;
            }
            $gadgetReferences[$reference['gadget']][$reference['action']][] = $reference['reference'];
        }

        // call gadget hook
        foreach ($gadgetReferences as $gadget => $actions) {
            // load gadget
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }

            // load hook
            $objHook = $objGadget->hook->load('Rating');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            // communicate with gadget Rating hook 
            foreach ($actions as $action => $action_references) {
                // call execute method
                $result = $objHook->Execute($action, $action_references);
                if (!Jaws_Error::IsError($result) && !empty($result)) {
                    $gadgetReferences[$gadget][$action] = $result;
                } else {
                    $gadgetReferences[$gadget][$action] = array();
                }
            }
        }

        $objDate = Jaws_Date::getInstance();
        $max_result_len = (int)$this->gadget->registry->fetch('max_result_len');
        if (empty($max_result_len)) {
            $max_result_len = 500;
        }

        $tpl = $this->gadget->template->load('MostRatted.html');
        $tpl->SetBlock('rating');
        $tpl->SetVariable('title', _t('RATING_MOSTRATTED', $gadget));

        // provide return result
        foreach ($references as $reference) {
            if (!@array_key_exists(
                $reference['reference'],
                $gadgetReferences[$reference['gadget']][$reference['action']]
                )
            ) {
                continue;
            }

            $reference = $gadgetReferences[$reference['gadget']][$reference['action']][$reference['reference']];
            $tpl->SetBlock('rating/reference');
            $tpl->SetVariable('title',  $reference['title']);
            $tpl->SetVariable('url',    $reference['url']);
            $tpl->SetVariable('target', (@$reference['outer'])? '_blank' : '_self');
            $tpl->SetVariable('image',  $reference['image']);
            if (!isset($reference['parse_text']) || $reference['parse_text']) {
                $reference['snippet'] = $this->gadget->plugin->parse(
                    $reference['snippet'],
                    Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
                    0,
                    '',
                    $gadget
                );
            }
            if (!isset($reference['strip_tags']) || $reference['strip_tags']) {
                $reference['snippet'] = strip_tags($reference['snippet']);
            }
            $reference['snippet'] = Jaws_UTF8::substr($reference['snippet'], 0, $max_result_len);

            $tpl->SetVariable('snippet', $reference['snippet']);
            $tpl->SetVariable('date', $objDate->Format($reference['date']));
            $tpl->ParseBlock('rating/reference');
        }

        $tpl->ParseBlock('rating');
        return $tpl->Get();
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

        if (!in_array((int)$post['rate'], range(0, 5))) {
            return Jaws_Error::raiseError('Out of range!', __FUNCTION__);
        }

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
                        'rates_count' => 0,
                        'rates_sum'   => 0,
                        'rates_avg'   => 0,
                    );
                }
            }
        }

        return $result;
    }

}