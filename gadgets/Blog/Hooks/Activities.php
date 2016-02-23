<?php
/**
 * Blog - Activities hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
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
        $items['Post'] = _t('BLOG_ACTIVITIES_ACTION_POST');

        return $items;
    }

}