<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Authenticate extends Users_Account_Default
{
    /**
     * Authenticate
     *
     * @access  public
     * @return  void
     */
    function Authenticate()
    {
        $loginData = $this->gadget->request->fetch(
            array(
                'domain', 'username', 'password', 'chkpassword',
                'usecrypt', 'resend', 'loginkey', 'loginstep', 'remember', 'defaults:array'
            ),
            'post'
        );
        $loginData['loginstep'] = (int)$loginData['loginstep'];

        // set default domain if not set
        if (is_null($loginData['domain'])) {
            $loginData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
        }

        try {
            if ($loginData['loginstep'] == 3) { // user password expired try to change it
                if ($loginData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $loginData['password'] = $JCrypt->decrypt($loginData['password']);
                        $loginData['chkpassword'] = $JCrypt->decrypt($loginData['chkpassword']);
                    }
                } else {
                    $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
                    $loginData['chkpassword'] = Jaws_XSS::defilter($loginData['chkpassword']);
                }

                // changing expired password
                if ($loginData['password'] !== $loginData['chkpassword']) {
                    throw new Exception(_t('USERS_USERS_PASSWORDS_DONT_MATCH'), 206);
                }

                // fetch user data from session
                $user = $this->gadget->session->fetch('temp.login.user');
                if (empty($user)) {
                    $loginData['loginstep'] = 1;
                    throw new Exception(_t('USERS_USER_NOT_EXIST'), 404);
                }

                // trying change password
                $userModel = $GLOBALS['app']->loadObject('Jaws_User');
                $result = $userModel->UpdateUser(
                    $user['id'],
                    array(
                        'password' => $loginData['password'],
                    )
                );
                if (Jaws_Error::IsError($result)) {
                    throw new Exception($result->getMessage(), 500);
                }

                // set password update time
                $user['last_password_update'] = time();

            } elseif ($loginData['loginstep'] == 2) { // two step/factor verification step
                // check captcha
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha('login');
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage(), 401);
                }

                // fetch user data from session
                $user = $this->gadget->session->fetch('temp.login.user');
                if (empty($user)) {
                    $loginData['loginstep'] = 1;
                    throw new Exception(_t('USERS_USER_NOT_EXIST'), 404);
                }

                $loginkey = $this->gadget->session->fetch('loginkey');
                if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300)) ||
                   (!empty($loginData['resend']) && ($loginkey['time'] < (time() - 90)))
                ) {
                    // send notification to user
                    $this->gadget->action->load('Login')->NotifyLoginKey($user);
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }

                // check verification key
                if ($loginkey['text'] != $loginData['loginkey']) {
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }

                // remove login key
                $this->gadget->session->delete('loginkey');
            } else { // user login step
                // get bad logins count
                $bad_logins = $this->gadget->action->load('Login')->BadLogins($loginData['username'], 0);
                $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
                if ($bad_logins >= $max_captcha_login_bad_count) {
                    // check captcha
                    $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                    $resCheck = $htmlPolicy->checkCaptcha('login');
                    if (Jaws_Error::IsError($resCheck)) {
                        throw new Exception($resCheck->getMessage(), 401);
                    }
                }

                $max_lockedout_login_bad_count = $this->app->registry->fetch('password_bad_count', 'Policy');
                if ($bad_logins >= $max_lockedout_login_bad_count) {
                    // forbidden access event logging
                    $this->gadget->event->shout(
                        'Log',
                        array(
                            'action'   => 'Login',
                            'domain'   => $loginData['domain'],
                            'username' => strtolower($loginData['username']),
                            'priority' => JAWS_WARNING,
                            'status'   => 403,
                        )
                    );
                    throw new Exception(_t('GLOBAL_ERROR_LOGIN_LOCKED_OUT'), 403);
                }

                $this->gadget->session->update('temp.login.user', '');
                if ($loginData['username'] === '') {
                    throw new Exception(_t('GLOBAL_ERROR_LOGIN_WRONG'), 401);
                }

                if ($loginData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $loginData['password'] = $JCrypt->decrypt($loginData['password']);
                    }
                } else {
                    $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
                }

                // fetch user information from database
                $userModel = $GLOBALS['app']->loadObject('Jaws_User');
                $user = $userModel->VerifyUser($loginData['domain'], $loginData['username'], $loginData['password']);
                if (Jaws_Error::isError($user)) {
                    // increase bad logins count
                    $this->gadget->action->load('Login')->BadLogins($loginData['username'], 1);
                    throw new Exception($user->getMessage(), $user->getCode());
                }

                // fetch user groups
                $groups = $userModel->GetGroupsOfUser($user['id']);
                if (Jaws_Error::IsError($groups)) {
                    $groups = array();
                }

                $user['groups'] = $groups;
                $user['avatar'] = $userModel->GetAvatar(
                    $user['avatar'],
                    $user['email'],
                    48,
                    $user['last_update']
                );
                $user['internal'] = true;
                $user['remember'] = (bool)$loginData['remember'];
                // user define default data for pass to user login listener gadgets
                $user['defaults'] = $loginData['defaults'];

                // store user data in registry for using next steps
                $this->gadget->session->update('temp.login.user', $user);

                // two step verification?
                if ((bool)$this->gadget->registry->fetchByUser('two_step_verification', '', $user['id']))
                {
                    $loginData['loginstep'] = 2;
                    // send notification to user
                    $this->gadget->action->load('Login')->NotifyLoginKey($user);

                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }
            } // end of login step

            // check password was expired
            $password_max_age = (int)$this->gadget->registry->fetch('password_max_age', 'Policy');
            if (($password_max_age > 0) &&
               (($user['last_password_update'] + $password_max_age) < time())
            ) {
                $loginData['loginstep'] = 3;
                throw new Exception(_t('GLOBAL_ERROR_PASSWORD_EXPIRED'), 206);
            }

            // check user concurrents logins
            $existSessions = 0;
            if (!empty($user['concurrents'])) {
                $existSessions = $GLOBALS['app']->Session->GetUserSessions($user['id'], true);
            }
            if (!empty($existSessions) && $existSessions >= $user['concurrents']) {
                // login conflict event logging
                $this->gadget->event->shout(
                    'Log',
                    array(
                        'action'   => 'Login',
                        'domain'   => $user['domain'],
                        'username' => strtolower($user['username']),
                        'priority' => JAWS_WARNING,
                        'status'   => 403,
                    )
                );

                throw new Exception(_t('GLOBAL_ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // remove temp user data
            $this->gadget->session->delete('temp.login.user');
            // unset bad login entry
            $this->gadget->action->load('Login')->BadLogins($user['username'], -1);

            return $user;
        } catch (Exception $error) {
            unset($loginData['password'], $loginData['chkpassword']);
            $this->gadget->session->push(
                $error->getMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }

    }

    /**
     * Authenticate Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AuthenticateError($error, $authtype, $referrer)
    {
        $urlParams = array();
        if (!empty($authtype)) {
            $urlParams['authtype'] = strtolower($authtype);
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = $referrer;
        }

        http_response_code($error->getCode());
        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location($this->gadget->urlMap('Login', $urlParams));
        } else {
            return Jaws_Header::Location($this->gadget->url('Login', $urlParams));
        }

    }

}