<?php
/**
 * Blog - Categories hook
 *
 * @category    GadgetHook
 * @package     Contact
 */
class Blog_Hooks_Categories extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of Categories
     *
     * @access  public
     * @return  array
     */
    function Execute()
    {
        $items = array();
        $items['Types'] = $this::t('TYPES');
        return $items;
    }

}