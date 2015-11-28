<?php
/**
 * Subscription - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Subscription
 */
class Subscription_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();

        $urls[] = array(
            'url'   => $this->gadget->urlMap('Subscription'),
            'title' => _t('SUBSCRIPTION_SUBSCRIPTION')
        );

        return $urls;
    }

}