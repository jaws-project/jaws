<?php
/**
 * Phoo - SiteActivity hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_SiteActivity extends Jaws_Gadget_Hook
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
        $items['Photo'] = _t('PHOO_SITEACTIVITY_ACTION_PHOTO');

        return $items;
    }

}