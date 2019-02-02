<?php
/**
 * Layout - Preferences hook
 *
 * @category    GadgetHook
 * @package     Layout
 */
class Layout_Hooks_Preferences extends Jaws_Gadget_Hook
{
    /**
     * Get user's preferences of this gadget
     *
     * @access  public
     * @return  array   Formatted array for using in Users Preferences action
     */
    function Execute()
    {
        $result = array();
        $result['default_layout_type'] = array(
            'type' => 'select',
            'title' => _t('LAYOUT_TYPE_DEFAULT'),
            'values' => array(
                0 => _t('LAYOUT_TYPE_0'),
                1 => _t('LAYOUT_TYPE_1'),
                2 => _t('LAYOUT_TYPE_2'),
            ),
        );

        return $result;
    }

}