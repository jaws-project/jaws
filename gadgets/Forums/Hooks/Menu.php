<?php
/**
 * Forums - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items for Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Forums'),
            'title' => _t('FORUMS_FORUMS')
        );

        return $urls;
    }

}