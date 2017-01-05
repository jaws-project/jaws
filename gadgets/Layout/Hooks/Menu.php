<?php
/**
 * Layout - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Layout
 */
class Layout_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls[] = array(
            'url'        => $this->gadget->urlMap('Layout'),
            'title'      => _t('LAYOUT_TITLE'),
            'acl_key'    => 'ManageLayout',
            'acl_subkey' => ''
        );

        return $urls;
    }

}