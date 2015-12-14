<?php
/**
 * SiteActivity - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     SiteActivity
 */
class SiteActivity_Hooks_Menu extends Jaws_Gadget_Hook
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
            'url'   => $this->gadget->urlMap('SiteActivity'),
            'title' => _t('SITEACTIVITY_ACTIONS_SITEACTIVITY')
        );

        return $urls;
    }

}