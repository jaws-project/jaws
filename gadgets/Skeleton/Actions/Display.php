<?php
/**
 * Skeleton Gadget
 *
 * @category    Gadget
 * @package     Skeleton
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_Actions_Display extends Jaws_Gadget_Action
{
    /**
     * Displays version of Jaws
     *
     * @access  public
     * @return  string  Jaws version
     */
    function Display()
    {
        $model   = $this->gadget->model->load();
        $version = $model->GetJawsVersion();
        return _t('SKELETON_DISPLAY_MESSAGE', $version);
    }

}