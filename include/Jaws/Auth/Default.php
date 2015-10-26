<?php
/**
 * Default auth class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_Default
{
    /**
     * Authenticate user|email/password
     *
     * @access  public
     * @param   string  $user       User's name or email
     * @param   string  $password   User's password
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($user, $password)
    {
        $userModel = $GLOBALS['app']->loadObject('Jaws_User');
        $result = $userModel->VerifyUser($user, $password);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // fetch user groups
        $groups = $userModel->GetGroupsOfUser($result['id']);
        if (Jaws_Error::IsError($groups)) {
            $groups = array();
        }

        $result['groups'] = $groups;
        $result['avatar'] = $userModel->GetAvatar(
            $result['avatar'],
            $result['email'],
            48,
            $result['last_update']
        );

        $result['internal'] = true;
        return $result;
    }

}