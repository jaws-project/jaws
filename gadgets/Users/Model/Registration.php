<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Registration extends Jaws_Gadget_Model
{
    /**
     * Creates a valid(registered) n user for an anonymous user
     *
     * @access  public
     * @param   string  $username   Username
     * @param   string  $user_email User's email
     * @param   string  $nickname   User's display name
     * @param   string  $fname      First name
     * @param   string  $lname      Last name
     * @param   string  $gender     User gender
     * @param   string  $ssn        Social Security number
     * @param   string  $dob        Birth date
     * @param   string  $url        User's URL
     * @param   string  $password   Password
     * @param   string  $group      Default user group
     * @return  mixed   True on success or message string
     */
    function CreateUser($username, $user_email, $nickname, $fname, $lname, $gender, $ssn,
                        $dob, $url, $password, $group = null)
    {
        if (empty($username) || empty($nickname) || empty($user_email))
        {
            return _t('USERS_USERS_INCOMPLETE_FIELDS');
        }

        $random = false;
        if (trim($password) == '') {
            $random = true;
            $password = Jaws_Utils::RandomText(8);
        }

        $jUser = new Jaws_User;
        //We already have a $username in the DB?
        $info = $jUser->GetUser($username);
        if (Jaws_Error::IsError($info) || isset($info['username'])) {
            return _t('USERS_USERS_ALREADY_EXISTS', $username);
        }

        if ($jUser->UserEmailExists($user_email)) {
            return _t('USERS_EMAIL_ALREADY_EXISTS', $user_email);
        }

        $user_enabled = ($this->gadget->registry->fetch('anon_activation') == 'auto')? 1 : 2;
        $user_id = $jUser->AddUser(
            array(
                'username' => $username,
                'nickname' => $nickname,
                'email'    => $user_email,
                'password' => $password,
                'status'   => $user_enabled,
            )
        );
        if (Jaws_Error::IsError($user_id)) {
            return $user_id->getMessage();
        }

        $result = $jUser->UpdatePersonal(
            $user_id,
            array(
                'fname'  => $fname,
                'lname'  => $lname,
                'gender' => $gender,
                'ssn'    => $ssn,
                'dob'    => $dob,
                'url'    => $url
            )
        );
        if ($result !== true) {
            //do nothing
        }

        if (!is_null($group) && is_numeric($group)) {
            $jUser->AddUserToGroup($user_id, $group);
        }

        $mail = Jaws_Mail::getInstance();
        $site_url     = $GLOBALS['app']->getSiteURL('/');
        $site_name    = $this->gadget->registry->fetch('site_name', 'Settings');
        $site_author  = $this->gadget->registry->fetch('site_author', 'Settings');
        $activation   = $this->gadget->registry->fetch('anon_activation');
        $notification = $this->gadget->registry->fetch('register_notification');
        $delete_user  = false;
        $message      = '';

        if ($random === true || $activation != 'admin') {
            $tpl = $this->gadget->template->load('UserNotification.txt');
            $tpl->SetBlock('Notification');
            $tpl->SetVariable('say_hello', _t('USERS_REGISTER_HELLO', $nickname));

            if ($random === true) {
                switch ($activation) {
                    case 'admin':
                        $tpl->SetVariable('message', _t('USERS_REGISTER_BY_ADMIN_RANDOM_MAIL_MSG'));
                        break;

                    case 'user':
                        $tpl->SetVariable('message', _t('USERS_REGISTER_BY_USER_RANDOM_MAIL_MSG'));
                        break;

                    default:
                        $tpl->SetVariable('message', _t('USERS_REGISTER_RANDOM_MAIL_MSG'));

                }

                $tpl->SetBlock('Notification/Password');
                $tpl->SetVariable('lbl_password', _t('USERS_USERS_PASSWORD'));
                $tpl->SetVariable('password', $password);
                $tpl->ParseBlock('Notification/Password');
            } elseif ($activation == 'user') {
                $tpl->SetVariable('message', _t('USERS_REGISTER_ACTIVATION_MAIL_MSG'));
            } else {
                $tpl->SetVariable('message', _t('USERS_REGISTER_MAIL_MSG'));
            }

            $tpl->SetBlock('Notification/IP');
            $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
            $tpl->ParseBlock('Notification/IP');

            $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
            $tpl->SetVariable('username', $username);

            if ($activation == 'user') {
                $verifyKey = $jUser->UpdateEmailVerifyKey($user_id);
                if (Jaws_Error::IsError($verifyKey)) {
                    $delete_user = true;
                    $message = _t('GLOBAL_ERROR_QUERY_FAILED');
                } else {
                    $tpl->SetBlock('Notification/Activation');
                    $tpl->SetVariable('lbl_activation_link', _t('USERS_ACTIVATE_ACTIVATION_LINK'));
                    $tpl->SetVariable(
                        'activation_link',
                        $this->gadget->urlMap(
                            'ActivateUser',
                            array('key' => $verifyKey),
                            array('absolute' => true)
                        )
                    );
                    $tpl->ParseBlock('Notification/Activation');
                }
            }

            $tpl->SetVariable('thanks',    _t('GLOBAL_THANKS'));
            $tpl->SetVariable('site-name', $site_name);
            $tpl->SetVariable('site-url',  $site_url);

            $tpl->ParseBlock('Notification');
            $body = $tpl->Get();

            if (!$delete_user) {
                $subject = _t('USERS_REGISTER_SUBJECT', $site_name);
                $mail->SetFrom();
                $mail->AddRecipient($user_email);
                $mail->SetSubject($subject);
                $mail->SetBody($this->gadget->plugin->parseAdmin($body));
                $mresult = $mail->send();
                if (Jaws_Error::IsError($mresult)) {
                    if ($activation == 'user') {
                        $delete_user = true;
                        $message = _t('USERS_REGISTER_ACTIVATION_SENDMAIL_FAILED', $user_email);
                    } elseif ($random === true) {
                        $delete_user = true;
                        $message = _t('USERS_REGISTER_RANDOM_SENDMAIL_FAILED', $user_email);
                    }
                }
            }
        }

        //Send an email to website owner
        $mail->reset();
        if (!$delete_user && ($notification == 'true' || $activation == 'admin')) {
            $tpl = $this->gadget->template->load('AdminNotification.txt');
            $tpl->SetBlock('Notification');
            $tpl->SetVariable('say_hello', _t('USERS_REGISTER_HELLO', $site_author));
            $tpl->SetVariable('message', _t('USERS_REGISTER_ADMIN_MAIL_MSG'));
            $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
            $tpl->SetVariable('username', $username);
            $tpl->SetVariable('lbl_nickname', _t('USERS_USERS_NICKNAME'));
            $tpl->SetVariable('nickname', $nickname);
            $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
            $tpl->SetVariable('email', $user_email);
            $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
            if ($activation == 'admin') {
                $verifyKey = $jUser->UpdateEmailVerifyKey($user_id);
                if (!Jaws_Error::IsError($verifyKey)) {
                    $tpl->SetBlock('Notification/Activation');
                    $tpl->SetVariable('lbl_activation_link', _t('USERS_ACTIVATE_ACTIVATION_LINK'));
                    $tpl->SetVariable(
                        'activation_link',
                        $this->gadget->urlMap(
                            'ActivateUser',
                            array('key' => $verifyKey),
                            array('absolute' => true)
                        )
                    );
                    $tpl->ParseBlock('Notification/Activation');
                }
            }
            $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
            $tpl->SetVariable('site-name', $site_name);
            $tpl->SetVariable('site-url', $site_url);
            $tpl->ParseBlock('Notification');
            $body = $tpl->Get();

            if (!$delete_user) {
                $subject = _t('USERS_REGISTER_SUBJECT', $site_name);
                $mail->SetFrom();
                $mail->AddRecipient();
                $mail->SetSubject($subject);
                $mail->SetBody($this->gadget->plugin->parseAdmin($body));
                $mresult = $mail->send();
                if (Jaws_Error::IsError($mresult) && $activation == 'admin') {
                    // do nothing
                    //$delete_user = true;
                    //$message = _t('USERS_ACTIVATE_NOT_ACTIVATED_SENDMAIL', $user_email);
                }
            }
        }

        if ($delete_user) {
            $jUser->DeleteUser($user_id);
            return $message;
        }

        return true;
    }

    /**
     * Checks if user/email are valid, if they are then generates a recovery
     * secret key and sends it to the user
     *
     * @access  public
     * @param   string  $user_email User email
     * @return  bool    True on success or Jaws_Error on failure
     */
    function SendRecoveryKey($user_email)
    {
        if (empty($user_email)) {
            return new Jaws_Error(_t('USERS_REGISTER_EMAIL_NOT_VALID'), $user_email);
        }

        $userModel = new Jaws_User;
        $uInfos = $userModel->GetUserInfoByEmail($user_email);
        if (Jaws_Error::IsError($uInfos)) {
            return $uInfos;
        }

        if (empty($uInfos)) {
            return new Jaws_Error(_t('USERS_USER_NOT_EXIST'));
        }

        foreach($uInfos as $info) {
            $verifyKey = $userModel->UpdatePasswordVerifyKey($info['id']);
            if (Jaws_Error::IsError($verifyKey)) {
                $verifyKey->SetMessage(_t('GLOBAL_ERROR_QUERY_FAILED'));
                return $verifyKey;
            }

            $site_url  = $GLOBALS['app']->getSiteURL('/');
            $site_name = $this->gadget->registry->fetch('site_name', 'Settings');

            $tpl = $this->gadget->template->load('RecoverPassword.txt');
            $tpl->SetBlock('RecoverPassword');
            $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
            $tpl->SetVariable('username', $info['username']);
            $tpl->SetVariable('nickname', $info['nickname']);
            $tpl->SetVariable('say_hello', _t('USERS_EMAIL_REPLACEMENT_HELLO', $info['nickname']));
            $tpl->SetVariable('message', _t('USERS_FORGOT_MAIL_MESSAGE'));
            $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
            $tpl->SetVariable(
                'url',
                $this->gadget->urlMap(
                    'ChangePassword',
                    array('key' => $verifyKey),
                    array('absolute' => true)
                )
            );
            $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
            $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
            $tpl->SetVariable('site-name', $site_name);
            $tpl->SetVariable('site-url', $site_url);
            $tpl->ParseBlock('RecoverPassword');

            $message = $tpl->Get();
            $subject = _t('USERS_FORGOT_REMEMBER', $site_name);

            $mail = Jaws_Mail::getInstance();
            $mail->SetFrom();
            $mail->AddRecipient($user_email);
            $mail->SetSubject($subject);
            $mail->SetBody($this->gadget->plugin->parseAdmin($message));
            $mresult = $mail->send();
            if (Jaws_Error::IsError($mresult)) {
                $mresult->SetMessage(_t('USERS_FORGOT_ERROR_SENDING_MAIL'));
                return $mresult;
            }
        }
    }

}