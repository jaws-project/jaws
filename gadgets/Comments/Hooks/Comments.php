<?php
/**
 * Comments - Comments gadget hook
 *
 * @category    GadgetHook
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns an array about guestbook action
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   int     $reference  Reference id
     * @return  array   entry info
     */
    function Execute($action, $reference)
    {
        $result = array(
            'reference_title' => _t('COMMENTS_GUESTBOOK'),
            'reference_link'  => $this->gadget->urlMap('Guestbook'),
            'author_name'     => '',
            'author_nickname' => '',
            'author_email'    => '',
        );

        return $result;
    }

}