<?php
/**
 * Phoo - Activities hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2015-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Activities extends Jaws_Gadget_Hook
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
        $items['Photo'] = $this::t('ACTIVITIES_ACTION_PHOTO');

        return $items;
    }

}