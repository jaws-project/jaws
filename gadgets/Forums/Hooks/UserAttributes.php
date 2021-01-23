<?php
/**
 * Forums - Preferences hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Hooks_UserAttributes extends Jaws_Gadget_Hook
{
    /**
     * Gets user's attributes extension
     *
     * @access  public
     * @return  array   Formatted array for using in Users attributes extension action
     */
    function Execute()
    {
        $attrs = array();
        $attrs['level'] = array(
            'type'  => 'number',
            'title' => _t('FORUMS_USERS_ATTRIBUTES_LEVEL'),
            'value' => 1,
            'ltr' => true,
        );

        return $attrs;
    }

}