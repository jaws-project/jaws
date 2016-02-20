<?php
/**
 * Activities - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Activities
 */
class Activities_Hooks_Menu extends Jaws_Gadget_Hook
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
            'url'   => $this->gadget->urlMap('Activities'),
            'title' => _t('ACTIVITIES_ACTIONS_ACTIVITIES')
        );

        return $urls;
    }

}