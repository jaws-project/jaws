<?php
/**
 * Contact - Activities hook
 *
 * @category    GadgetHook
 * @package     Contact
 */
class Contact_Hooks_Activities extends Jaws_Gadget_Hook
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
        $items['Contact'] = $this::t('ACTIVITIES_ACTION_CONTACT');

        return $items;
    }

}