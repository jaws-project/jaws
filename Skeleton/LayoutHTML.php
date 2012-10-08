<?php
/**
 * Skeleton Gadget - An example gadget to be used by gadget developers (layout actions)
 *
 * @category   GadgetLayout
 * @package    Skeleton
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SkeletonLayoutHTML
{
    /**
     * Returns the server time
     *
     * @access  public
     * @return  string
     */
    function Display()
    {
        $model   = $GLOBALS['app']->LoadGadget('Skeleton', 'Model');
        $version = $model->GetJawsVersion();

        return _t('SKELETON_DISPLAY_MESSAGE', $version);
    }

}