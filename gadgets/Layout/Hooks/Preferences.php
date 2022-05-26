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
            'title' => $this::t('TYPE_DEFAULT'),
            'values' => array(
                0 => $this::t('TYPE_0'),
                1 => $this::t('TYPE_1'),
            ),
        );

        return $result;
    }

}