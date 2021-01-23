<?php
/**
 * Skeleton Gadget
 *
 * @category    Gadget
 * @package     Skeleton
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_Actions_Admin_Display extends Jaws_Gadget_Action
{
    /**
     * Displays no admin action
     *
     * @access  public
     * @return  string  Jaws version
     */
    function Display()
    {
        return _t('SKELETON_ADMIN_MESSAGE');
    }

}