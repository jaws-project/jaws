<?php
/**
 * StaticPage - Activities hook
 *
 * @category    GadgetHook
 * @package     StaticPage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class StaticPage_Hooks_Activities extends Jaws_Gadget_Hook
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
        $items['Page'] = $this::t('ACTIVITIES_ACTION_PAGE');

        return $items;
    }

}