<?php
/**
 * Users - Preferences hook
 *
 * @category    GadgetHook
 * @package     Users
 */
class Users_Hooks_Preferences extends Jaws_Gadget_Hook
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
        $result['two_step_verification'] = array(
            'type'  => 'checkbox',
            'title' => $this::t('SETTINGS_TWO_STEP_VERIFICATION'),
        );

        return $result;
    }

}