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
     * @param   int     $domain         Domain ID
     * @param   string  $username       Username
     * @param   string  $user_email     User's email
     * @param   string  $user_mobile    User's mobile
     * @param   string  $nickname       User's display name
     * @param   string  $fname          First name
     * @param   string  $lname          Last name
     * @param   string  $gender         User gender
     * @param   string  $ssn            Social Security number
     * @param   string  $dob            Birth date
     * @param   string  $url            User's URL
     * @param   string  $password       Password
     * @param   string  $group          Default user group
     * @return  mixed   User ID on success or Jaws_Error on failure
     */
    function CreateUser($domain, $username, $user_email, $user_mobile, $nickname, $fname, $lname,
        $gender, $ssn, $dob, $url, $password, $group = null
    ) {
        $username = trim($username);
        $user_email = trim($user_email);
        $user_mobile = trim($user_mobile);
        if (empty($username) || empty($nickname) || (empty($user_email) && empty($user_mobile)))
        {
            return Jaws_Error::raiseError(
                _t('USERS_USERS_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if (trim($password) == '') {
            $password = Jaws_Utils::RandomText(8);
        }

        $jUser = new Jaws_User;
        // this username already exists in the DB?
        if ($jUser->UsernameExists($username)) {
            return Jaws_Error::raiseError(
                _t('USERS_USERS_ALREADY_EXISTS', $username),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        // this email address already exists in the DB?
        if ($jUser->UserEmailExists($user_email)) {
            return Jaws_Error::raiseError(
                _t('USERS_EMAIL_ALREADY_EXISTS', $user_email),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        // this mobile number already exists in the DB?
        if ($jUser->UserMobileExists($user_mobile)) {
            return Jaws_Error::raiseError(
                _t('USERS_MOBILE_ALREADY_EXISTS', $user_mobile),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $verifyKey = Jaws_Utils::RandomText(5, false, false, true);
        $user_enabled = ($this->gadget->registry->fetch('anon_activation') == 'auto')? 1 : 2;
        $user_id = $jUser->AddUser(
            array(
                'domain' =>   $domain,
                'username' => $username,
                'nickname' => $nickname,
                'email'    => $user_email,
                'mobile'   => $user_mobile,
                'password' => $password,
                'verify_key' => $verifyKey,
                'status'   => $user_enabled,
            )
        );
        if (Jaws_Error::IsError($user_id)) {
            return $user_id;
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

        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $settings   = $GLOBALS['app']->Registry->fetchAll('Settings');
        $activation = $this->gadget->registry->fetch('anon_activation');
        $message    = '';

        //Send notification to the user
        $tpl = $this->gadget->template->load('RegistrationNotification.html');
        $tpl->SetBlock('UserNotification');
        $tpl->SetVariable('say_hello', _t('USERS_REGISTRATION_HELLO', $nickname));

        switch ($activation) {
            case 'admin':
                $tpl->SetVariable('message', _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_ADMIN'));
                break;

            case 'user':
                $tpl->SetVariable('message', _t('USERS_REGISTRATION_ACTIVATION_REQUIRED_BY_USER'));
                // verify key
                $tpl->SetBlock('UserNotification/Activation');
                $tpl->SetVariable('lbl_key', _t('USERS_REGISTRATION_KEY'));
                $tpl->SetVariable('key', $verifyKey);
                $tpl->ParseBlock('UserNotification/Activation');
                break;

            default:
                $tpl->SetVariable(
                    'message',
                    _t('USERS_REGISTRATION_ACTIVATED_BY_AUTO', $this->gadget->urlMap(
                        'Login',
                        array(),
                        array('absolute'=>true)
                    ))
                );
        }

        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $username);
        $tpl->SetVariable('lbl_password', _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('password', $password);
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $user_email);
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile',      $user_mobile);
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
        $tpl->SetVariable('thanks',    _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name', $settings['site_name']);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->ParseBlock('UserNotification');
        $message = $tpl->Get();
        $subject = _t('USERS_REGISTRATION_USER_SUBJECT', $settings['site_name']);

        // Notify
        $params = array();
        $params['key']     = crc32('Users.Registration.User' . $user_id);
        $params['title']   = $subject;
        $params['summary'] = _t(
            'USERS_REGISTRATION_USER_SUMMARY',
            $nickname,
            $site_url,
            $username,
            $password,
            $user_email,
            $user_mobile,
            $verifyKey
        );
        $params['description'] = $this->gadget->plugin->parse($message);
        $params['emails']      = array($user_email);
        $params['mobiles']     = array($user_mobile);
        $this->gadget->event->shout('Notify', $params);

        //Send an email to website owner
        if ($this->gadget->registry->fetch('register_notification') == 'true') {
            $tpl = $this->gadget->template->load('RegistrationNotification.html');
            $tpl->SetBlock('OwnerNotification');
            $tpl->SetVariable('say_hello', _t('USERS_REGISTRATION_HELLO', $settings['site_author']));
            $tpl->SetVariable('message', _t('USERS_REGISTRATION_ADMIN_MAIL_MSG'));
            $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
            $tpl->SetVariable('username', $username);
            $tpl->SetVariable('lbl_nickname', _t('USERS_USERS_NICKNAME'));
            $tpl->SetVariable('nickname', $nickname);
            $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
            $tpl->SetVariable('email', $user_email);
            $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
            $tpl->SetVariable('mobile',      $user_mobile);
            $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
            $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);

            $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
            $tpl->SetVariable('site-name', $settings['site_name']);
            $tpl->SetVariable('site-url', $site_url);
            $tpl->ParseBlock('OwnerNotification');
            $message = $tpl->Get();
            $subject = _t('USERS_REGISTRATION_OWNER_SUBJECT', $settings['site_name']);

            // Notify
            $params = array();
            $params['key']     = crc32('Users.Registration.Owner' . $user_id);
            $params['title']   = $subject;
            $params['summary'] = _t(
                'USERS_REGISTRATION_OWNER_SUMMARY',
                $site_url,
                $username,
                $nickname,
                $user_email,
                $user_mobile
            );
            $params['description'] = $this->gadget->plugin->parse($message);
            $params['emails']      = array($settings['site_email']);
            $params['mobiles']     = array($settings['site_mobile']);
            $this->gadget->event->shout('Notify', $params);
        }

        return $user_id;
    }

    /**
     * Checks the user verification key
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   string  $key    Verification key
     * @return  bool    True on success or False on failure
     */
    function verifyKey($user, $key)
    {
        $result = Jaws_ORM::getInstance()
            ->table('users')
            ->update(array('status' => 1))
            ->where('id', (int)$user)
            ->and()
            ->where('verify_key', $key)
            ->exec();
        return Jaws_Error::IsError($result)? false : !empty($result);
    }

    /**
     * Checks if user/email are valid, if they are then generates a recovery
     * secret key and sends it to the user
     *
     * @access  public
     * @param   string  $user_email User email
     * @return  bool    True on success or Jaws_Error on failure
     */
    function SendLoginRecoveryKey($term)
    {
        if (empty($term)) {
            return Jaws_Error::raiseError(
                _t('USERS_USER_NOT_EXIST'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $userModel = new Jaws_User;
        $user = $userModel->FindUserByTerm($term);
        if (Jaws_Error::IsError($user)) {
            return $uInfos;
        }

        if (empty($user)) {
            return Jaws_Error::raiseError(
                _t('USERS_USER_NOT_EXIST'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $recoveryKey = $userModel->UpdatePasswordRecoveryKey($user['id']);
        if (Jaws_Error::IsError($RecoveryKey)) {
            return $RecoveryKey;
        }

        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('LoginRecoveryNotification.html');
        $tpl->SetBlock('NotificationRecovery');
        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $user['username']);
        $tpl->SetVariable('nickname', $user['nickname']);
        $tpl->SetVariable('say_hello', _t('USERS_EMAIL_REPLACEMENT_HELLO', $user['nickname']));
        $tpl->SetVariable('message', _t('USERS_FORGOT_MAIL_MESSAGE'));
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile',     $user['mobile']);
        $tpl->SetVariable('lbl_key', _t('USERS_FORGOT_RECOVERY_KEY'));
        $tpl->SetVariable('key',      $recoveryKey);
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
        $tpl->SetVariable('thanks', _t('GLOBAL_THANKS'));
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url', $site_url);
        $tpl->ParseBlock('NotificationRecovery');

        $message = $tpl->Get();
        $subject = _t('USERS_FORGOT_REMEMBER', $site_name);

        // Notify
        $params = array();
        $params['key']     = crc32('Users.SendLoginRecoveryKey.User' . $user['id']);
        $params['title']   = $subject;
        $params['summary'] = _t(
            'USERS_FORGOT_LOGIN_SUMMARY',
            $user['nickname'],
            $site_url,
            $user['username'],
            $user['email'],
            $user['mobile'],
            $recoveryKey
        );
        $params['description'] = $message;
        $params['emails']      = array($user['email']);
        $params['mobiles']     = array($user['mobile']);
        $this->gadget->event->shout('Notify', $params);
        return true;
    }

}