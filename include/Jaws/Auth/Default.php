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
     * @param   array   $loginData  Login data(username, password, ...)
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($loginData)
    {
        if ($loginData['usecrypt']) {
            $JCrypt = Jaws_Crypt::getInstance();
            if (!Jaws_Error::IsError($JCrypt)) {
                $loginData['password'] = $JCrypt->decrypt($loginData['password']);
            }
        } else {
            $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
        }

        $userModel = $GLOBALS['app']->loadObject('Jaws_User');
        $result = $userModel->VerifyUser($loginData['username'], $loginData['password']);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // fetch user groups
        $groups = $userModel->GetGroupsOfUser($result['id']);
        if (Jaws_Error::IsError($groups)) {
            $groups = array();
        }

        // FIXME: we must find better way for use password in extra protocols ex. IMAP
        $result['password'] = $loginData['password'];
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