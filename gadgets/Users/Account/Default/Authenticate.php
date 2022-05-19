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
                'domain', 'username', 'password', 'old_password',
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
                // fetch user data from session
                $user = $this->gadget->session->temp_login_user;
                if (empty($user)) {
                    $loginData['loginstep'] = 1;
                    throw new Exception($this::t('USER_NOT_EXIST'), 404);
                }

                if ($loginData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $loginData['password'] = $JCrypt->decrypt($loginData['password']);
                        $loginData['old_password'] = $JCrypt->decrypt($loginData['old_password']);
                    }
                } else {
                    $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
                    $loginData['old_password'] = Jaws_XSS::defilter($loginData['old_password']);
                }

                // changing expired password
                if ($loginData['password'] === $loginData['old_password']) {
                    throw new Exception($this::t('USERS_PASSWORDS_OLD_EQUAL'), 206);
                }

                // trying change password
                $result = $this->gadget->model->load('User')->updatePassword(
                    (int)$user['id'],
                    $loginData['password'],
                    $loginData['old_password']
                );
                if (Jaws_Error::IsError($result)) {
                    throw new Exception($result->getMessage(), 206);
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
                $user = $this->gadget->session->temp_login_user;
                if (empty($user)) {
                    $loginData['loginstep'] = 1;
                    throw new Exception($this::t('USER_NOT_EXIST'), 404);
                }

                $loginkey = $this->gadget->session->loginkey;
                if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300)) ||
                   (!empty($loginData['resend']) && ($loginkey['time'] < (time() - 90)))
                ) {
                    // send notification to user
                    $this->gadget->action->load('Login')->NotifyLoginKey($user);
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }

                // check verification key
                if ($loginkey['text'] != $loginData['loginkey']) {
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
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
                            'result'   => 403,
                            'status'   => false,
                        )
                    );
                    throw new Exception(Jaws::t('ERROR_LOGIN_LOCKED_OUT'), 403);
                }

                $this->gadget->session->temp_login_user = '';
                if ($loginData['username'] === '') {
                    throw new Exception(Jaws::t('ERROR_LOGIN_WRONG'), 401);
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
                $user = $this->gadget->model->load('User')->verify(
                    $loginData['domain'], $loginData['username'], $loginData['password']
                );
                if (Jaws_Error::isError($user)) {
                    // increase bad logins count
                    $this->gadget->action->load('Login')->BadLogins($loginData['username'], 1);
                    throw new Exception($user->getMessage(), $user->getCode());
                }

                // fetch user groups
                $groups = $this->gadget->model->load('Groups')->getGroups(0, 0, $user['id']);
                if (Jaws_Error::IsError($groups)) {
                    $groups = array();
                } else {
                    $groups = array_column($groups, 'name', 'id');
                }

                $user['groups'] = $groups;
                $user['avatar'] = $this->gadget->urlMap('Avatar', array('user'  => $user['username']));
                $user['internal'] = true;
                $user['remember'] = (bool)$loginData['remember'];
                // user define default data for pass to user login listener gadgets
                $user['defaults'] = $loginData['defaults'];

                // store user data in registry for using next steps
                $this->gadget->session->temp_login_user = $user;

                // two step verification?
                if ((bool)$this->gadget->registry->fetchByUser('two_step_verification', '', $user['id']))
                {
                    $loginData['loginstep'] = 2;
                    // send notification to user
                    $this->gadget->action->load('Login')->NotifyLoginKey($user);

                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }
            } // end of login step

            // check password was expired
            // if last_password_update = 0 then password must be change even password expiry is disabled
            $password_max_age = (int)$this->gadget->registry->fetch('password_max_age', 'Policy') * 3600;
            if ($user['last_password_update'] == 0 ||
                ($password_max_age > 0 && ($user['last_password_update'] + $password_max_age < time()))
            ) {
                $loginData['loginstep'] = 3;
                throw new Exception(Jaws::t('ERROR_PASSWORD_EXPIRED'), 206);
            }

            // check user concurrents logins
            $existSessions = 0;
            if (!empty($user['concurrents'])) {
                $existSessions = $this->app->session->getUserSessionsCount($user['id'], true);
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
                        'result'   => 403,
                        'status'   => false,
                    )
                );

                throw new Exception(Jaws::t('ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // remove temp user data
            $this->gadget->session->delete('temp_login_user');
            // unset bad login entry
            $this->gadget->action->load('Login')->BadLogins($user['username'], -1);

            return $user;
        } catch (Exception $error) {
            unset($loginData['password'], $loginData['chkpassword']);
            $this->gadget->session->push(
                $error->getMessage(),
                ($error->getCode() == 201)? RESPONSE_NOTICE : RESPONSE_ERROR,
                'Login.Response',
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
    function AuthenticateError($result, $authtype, $referrer)
    {
        $urlParams = array();
        if (!empty($authtype)) {
            $urlParams['authtype'] = strtolower($authtype);
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = bin2hex($referrer);
        }

        if (Jaws_Error::IsError($result)) {
            http_response_code($result->getCode());
            return Jaws_Header::Location(
                $this->gadget->urlMap('Login', $urlParams),
                'Login.Response'
            );
        }

        // 201 http code for success login
        http_response_code(201);
        return Jaws_Header::Location(
            $referrer,
            'Login.Response'
        );
    }

}