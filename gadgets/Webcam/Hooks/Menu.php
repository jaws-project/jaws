<?php
/**
 * Webcam - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Webcam
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls[] = array('url'   => $this->gadget->urlMap('Display'),
                        'title' => $this->gadget->title);
        return $urls;
    }
}
