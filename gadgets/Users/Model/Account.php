<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Account extends Jaws_Gadget_Model
{
    /**
     * Updates user profile
     *
     * @access  public
     * @param   int      $uid       User ID
     * @param   string   $username  Username
     * @param   string   $nickname  User's display name
     * @param   string   $email     User's email
     * @param   string   $new_email User's new_email
     * @param   string   $mobile    User's mobile number
     * @param   string   $password  Password
     * @return  mixed    True on success or Jaws_Error on failure
     */
    function UpdateAccount($uid, $username, $nickname, $email, $new_email, $mobile, $password)
    {
        $uData = array(
            'username' => $username,
            'nickname' => $nickname,
            'email'    => $email,
            'mobile'   => $mobile,
            'password' => $password,
        );
        if (!empty($new_email)) {
            $uData['new_email'] = $new_email;
        }

        $jUser  = new Jaws_User;
        if ($jUser->UserEmailExists($new_email)) {
            return Jaws_Error::raiseError(
                _t('USERS_EMAIL_ALREADY_EXISTS', $new_email),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if ($jUser->UserMobileExists($mobile, $uid)) {
            return Jaws_Error::raiseError(
                _t('USERS_MOBILE_ALREADY_EXISTS', $mobile),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        return $jUser->UpdateUser($uid, $uData);
    }

    /**
     * Changes a password from a given key
     *
     * @access  public
     * @param   string  $user   User name/email/mobile
     * @param   string  $key    Recovery key
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function ChangePassword($user, $key)
    {
        $jUser = new Jaws_User;
        $user = $jUser->VerifyPasswordRecoveryKey($user, $key);
        if (Jaws_Error::IsError($user)) {
            return $user;
        }

        if (empty($user)) {
            return Jaws_Error::raiseError(
                _t('USERS_FORGOT_KEY_NOT_VALID'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // generate new password
        $password = Jaws_Utils::RandomText(8);
        $res = $jUser->UpdateUser(
            $user['id'],
            array(
                'username' => $user['username'],
                'nickname' => $user['nickname'],
                'email'    => $user['email'],
                'mobile'   => $user['mobile'],
                'password' => $password,
            )
        );
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('NewPassword.txt');
        $tpl->SetBlock('NewPassword');
        $tpl->SetVariable('say_hello', _t('USERS_EMAIL_REPLACEMENT_HELLO', $user['nickname']));
        $tpl->SetVariable('username', $user['username']);
        $tpl->SetVariable('nickname', $user['nickname']);
        $tpl->SetVariable('password', $password);
        $tpl->SetVariable('message',  _t('USERS_FORGOT_PASSWORD_CHANGED_MESSAGE', $user['username']));
        $tpl->SetVariable('lbl_password', _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->ParseBlock('NewPassword');

        $message = $tpl->Get();            
        $subject = _t('USERS_FORGOT_PASSWORD_CHANGED_SUBJECT');

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom();
        $mail->AddRecipient($user['email']);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->plugin->parseAdmin($message));
        $mresult = $mail->send();
        if (Jaws_Error::IsError($mresult)) {
            return Jaws_Error::raiseError(
                _t('USERS_FORGOT_ERROR_SENDING_MAIL'),
                __FUNCTION__
            );
        }

        return true;
    }

}