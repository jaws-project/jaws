<?php
/**
 * Users - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls[] = array('url'   => $this->gadget->urlMap('LoginBox'),
                        'title' => _t('USERS_LOGIN_TITLE'));
        return $urls;
    }

}