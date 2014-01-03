<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
     * @param   string   $password  Password
     * @return  mixed    True on success or Jaws_Error on failure
     */
    function UpdateAccount($uid, $username, $nickname, $email, $password)
    {
        $jUser  = new Jaws_User;
        $result = $jUser->UpdateUser(
            $uid,
            array(
                'username' => $username,
                'nickname' => $nickname,
                'email'    => $email,
                'password' => $password,
            )
        );
        return $result;
    }

    /**
     * Changes a password from a given key
     *
     * @access  public
     * @param   string   $key   Recovery key
     * @return  mixed    True on success or Jaws_Error on failure
     */
    function ChangePassword($key)
    {
        $jUser = new Jaws_User;
        $user = $jUser->GetUserByPasswordVerifyKey($key);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        // generate new password
        $password = Jaws_Utils::RandomText(8);
        $res = $jUser->UpdateUser(
            $user['id'],
            array(
                'username' => $user['username'],
                'nickname' => $user['nickname'],
                'email'    => $user['email'],
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

        $mail = new Jaws_Mail;
        $mail->SetFrom();
        $mail->AddRecipient($user['email']);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->ParseText($message));
        $mresult = $mail->send();
        if (Jaws_Error::IsError($mresult)) {
            return new Jaws_Error(_t('USERS_FORGOT_ERROR_SENDING_MAIL'));
        }

        return true;
    }

}