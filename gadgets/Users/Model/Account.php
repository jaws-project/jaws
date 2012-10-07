<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Account extends Jaws_Model
{
    /**
     * Updates the profile of an user
     *
     * @access  public
     * @param   int      $uid       User's ID
     * @param   string   $username  Username
     * @param   string   $email     User's email
     * @param   string   $nickname     User's display name
     * @param   string   $password  Password
     * @return  mixed    True (Success) or Jaws_Error (failure)
     */
    function UpdateAccount($uid, $username, $email, $nickname, $password)
    {
        if (trim($nickname) == '' || trim($email) == '')
        {
            return new Jaws_Error(_t('USERS_USERS_INCOMPLETE_FIELDS'), _t('USERS_NAME'));
        }

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser  = new Jaws_User;
        $result = $jUser->UpdateUser($uid,
                                     $username,
                                     $nickname,
                                     $email,
                                     $password);
        return $result;
    }

    /**
     * Changes a password from a given key
     *
     * @access  public
     * @param   string   $key   Recovery key
     * @return  boolean
     */
    function ChangePassword($key)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';

        $jUser = new Jaws_User;
        if ($id = $jUser->GetIDByVerificationKey($key)) {
            $info = $jUser->GetUser((int)$id);

            include_once 'Text/Password.php';
            $password = Text_Password::create(8, 'pronounceable', 'alphanumeric');

            $res = $jUser->UpdateVerificationKey($id);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }

            $res = $jUser->UpdateUser($id,
                                      $info['username'],
                                      $info['nickname'],
                                      $info['email'],
                                      $password);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }

            $site_url  = $GLOBALS['app']->getSiteURL('/');
            $site_name = $GLOBALS['app']->Registry->Get('/config/site_name');

            $tpl = new Jaws_Template('gadgets/Users/templates/');
            $tpl->Load('NewPassword.txt');
            $tpl->SetBlock('NewPassword');
            $tpl->SetVariable('username', $info['username']);
            $tpl->SetVariable('nickname', $info['nickname']);
            $tpl->SetVariable('password', $password);
            $tpl->SetVariable('message',  _t('USERS_FORGOT_PASSWORD_CHANGED_MESSAGE', $info['username']));
            $tpl->SetVariable('lbl_password', _t('USERS_USERS_PASSWORD'));
            $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
            $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
            $tpl->SetVariable('site-name', $site_name);
            $tpl->SetVariable('site-url',  $site_url);
            $tpl->ParseBlock('NewPassword');

            $message = $tpl->Get();            
            $subject = _t('USERS_FORGOT_PASSWORD_CHANGED_SUBJECT');

            require_once JAWS_PATH . 'include/Jaws/Mail.php';
            $mail = new Jaws_Mail;
            $mail->SetFrom();
            $mail->AddRecipient($info['email']);
            $mail->SetSubject($subject);
            $mail->SetBody(Jaws_Gadget::ParseText($message, 'Users'));
            $mresult = $mail->send();
            if (Jaws_Error::IsError($mresult)) {
                return new Jaws_Error(_t('USERS_FORGOT_ERROR_SENDING_MAIL'));
            } else {
                return true;
            }
        } else {
            return new Jaws_Error(_t('USERS_FORGOT_KEY_NOT_VALID'));
        }
    }

}