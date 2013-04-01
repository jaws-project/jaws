<?php
/**
 * Users - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UsersURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                        'title' => _t('USERS_LOGIN_TITLE'));
        return $urls;
    }
}
