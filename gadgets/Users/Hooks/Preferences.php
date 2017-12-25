<?php
/**
 * Users - Preferences hook
 *
 * @category    GadgetHook
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
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
            'title' => _t('USERS_SETTINGS_TWO_STEP_VERIFICATION'),
        );

        return $result;
    }

}