<?php
/**
 * Shoutbox - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Shoutbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url'   => $this->gadget->urlMap('Comments'),
                        'title' => $this->gadget->title);
        return $urls;
    }
}
