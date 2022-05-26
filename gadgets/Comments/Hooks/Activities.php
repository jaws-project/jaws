<?php
/**
 * Comments - Activities hook
 *
 * @category    GadgetHook
 * @package     Users
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Hooks_Activities extends Jaws_Gadget_Hook
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
        $items['NewComment'] = $this::t('ACTIVITIES_ACTION_NEWCOMMENTS');

        return $items;
    }

}