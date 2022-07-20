<?php
/**
 * Quotes - Categories hook
 *
 * @category    GadgetHook
 * @package     Contact
 */
class Quotes_Hooks_Categories extends Jaws_Gadget_Hook
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
        $items['Quotes'] = $this::t('QUOTES');
        return $items;
    }

}