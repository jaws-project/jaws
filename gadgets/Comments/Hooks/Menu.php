<?php
/**
 * Comments - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Guestbook'),
            'title' => _t('COMMENTS_GUESTBOOK')
        );

        return $urls;
    }

}