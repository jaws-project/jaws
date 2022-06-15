<?php
/**
 * Blog - Activities hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Activities extends Jaws_Gadget_Hook
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
        $items['Post'] = $this::t('ACTIVITIES_ACTION_POST');

        return $items;
    }

}