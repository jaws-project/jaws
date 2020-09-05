<?php
/**
 * Users - Activities hook
 *
 * @category    GadgetHook
 * @package     Users
 */
class Users_Hooks_Activities extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of Site activity
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $items = array();
        $items['AddUser'] = $this::t('ACTIVITIES_ACTION_ADDUSER');

        return $items;
    }

}