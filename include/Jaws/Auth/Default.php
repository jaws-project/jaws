<?php
/**
 * Default auth class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_Default
{
    /**
     * Authenticate user/password
     *
     * @access  public
     */
    function Auth($user, $password)
    {
        $userModel = $GLOBALS['app']->loadObject('Jaws_User');
        $result = $userModel->GetUser($user, true, true, true, true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (empty($result)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // bad_password_count & lockedout time
        $max_password_bad_count = $GLOBALS['app']->Registry->fetch('password_bad_count', 'Policy');
        $password_lockedout_time = $GLOBALS['app']->Registry->fetch('password_lockedout_time', 'Policy');
        if ($result['bad_password_count'] >= $max_password_bad_count &&
           ((time() - $result['last_access']) <= $password_lockedout_time))
        {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING, null, 403, $result['id']));
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_LOCKED_OUT'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // check password
        if ($result['password'] !== Jaws_User::GetHashedPassword($password, $result['password'])) {
            $userModel->updateLastAccess($result['id'], false);
            // password incorrect event logging
            $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING, null, 401, $result['id']));
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_WRONG'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        unset($result['password']);

        // status
        if ($result['status'] !== 1) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING, null, 403, $result['id']));
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_STATUS_'. $result['status']),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // expiry date
        if (!empty($result['expiry_date']) && $result['expiry_date'] <= time()) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING, null, 403, $result['id']));
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_EXPIRED'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // logon hours
        $wdhour = explode(',', $GLOBALS['app']->UTC2UserTime(time(), 'w,G', true));
        $lhByte = hexdec($result['logon_hours']{$wdhour[0]*6 + intval($wdhour[1]/4)});
        if ((pow(2, fmod($wdhour[1], 4)) & $lhByte) == 0) {
            // forbidden access event logging
            $GLOBALS['app']->Listener->Shout('Log', array('Users', 'Login', JAWS_WARNING, null, 403, $result['id']));
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_LOGIN_LOGON_HOURS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
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

        // update last access
        $userModel->updateLastAccess($result['id'], true);
        return $result;
    }

}