<?php
/**
 * Skeleton Model 
 *
 * @category   GadgetModel
 * @package    Skeleton
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_Model extends Jaws_Gadget_Model
{
    /**
     * Returns version of Jaws
     *
     * @access  public
     * @return  string  Jaws version
     */
    function GetJawsVersion()
    {
        return $GLOBALS['app']->Registry->fetch('version');
    }

}