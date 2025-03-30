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
                'domain?null', 'username', 'password?text', 'old_password?text',
                'usecrypt|boolean?null', 'resend|boolean?null', 'loginkey?text', 'loginstep|text', 'remember|boolean?null', 'referrer?null',
                'defaults:array'
            ),
            'post'
        );

        // decrypt password if required
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

        try {
            $user = $this->gadget->session->temp_login_user;
            $user_login_steps = $this->gadget->session->temp_login_steps?? [];
            if (empty($user)) {
                $loginData['loginstep'] = 'user';
                $user_login_steps = [];
            }

            // checks if all previous steps is passed, if not, then goto to user/first step
            $login_internal_step = 'user';
            foreach ($user_login_steps as $step => $passed) {
                if (in_array($loginData['loginstep'], explode('|', $step))) {
                    $login_internal_step = $step;
                    break;
                }
                if (!$passed) {
                    $loginData['domain'] = 0;
                    $loginData['loginstep'] = 'user';
                    break;
                }
            }

            switch ($loginData['loginstep']) {
                case 'password':
                    if ($loginData['password'] === '') {
                        throw new Exception('', 403);
                    }

                    // get bad logins count
                    $bad_password_cache_key = Jaws_Cache::key('loginstep.password.'.$user['username']);
                    $bad_password_count = $this->app->cache->get($bad_password_cache_key)?? 0;
                    $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
                    if ($bad_password_count >= $max_captcha_login_bad_count) {
                        // check captcha
                        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                        $resCheck = $htmlPolicy->checkCaptcha('login');
                        if (Jaws_Error::IsError($resCheck)) {
                            throw new Exception($resCheck->getMessage(), 401);
                        }
                    }

                    $max_lockedout_login_bad_count = $this->app->registry->fetch('login_bad_count', 'Policy');
                    if ($bad_password_count >= $max_lockedout_login_bad_count) {
                        // forbidden access event logging
                        $this->gadget->event->shout(
                            'Log',
                            array(
                                'action'   => 'Login',
                                'domain'   => $user['domain'],
                                'username' => $user['username'],
                                'priority' => JAWS_WARNING,
                                'result'   => 403,
                                'status'   => false,
                            )
                        );
                        throw new Exception(Jaws::t('ERROR_LOGIN_LOCKED_OUT'), 403);
                    }

                    // fetch user information from database
                    $result = $this->gadget->model->load('User')->verify(
                        $user['domain'], $user['username'], $loginData['password']
                    );
                    if (Jaws_Error::isError($result)) {
                        // increase bad logins count
                        $lockedout_time = (int)$this->app->registry->fetch('login_lockedout_time', 'Policy');
                        $this->app->cache->set($bad_password_cache_key, $bad_password_count + 1, false, $lockedout_time);
                        throw new Exception(Jaws::t('ERROR_LOGIN_PASSWORD_WRONG'), $result->getCode());
                    }

                    // delete bad password cache entry, because we got correct password
                    $this->app->cache->delete($bad_password_cache_key);

                    // two step verification?
                    if (!array_key_exists('key', $user_login_steps) &&
                        (bool)$this->gadget->registry->fetchByUser('two_step_verification', '', $user['id'])
                    ) {
                        array_splice_assoc($user_login_steps, 'password', 1, ['key' => false]);
                        $this->gadget->session->temp_login_steps = $user_login_steps;
                    }

                    // check password was expired
                    // if last_password_update = 0 then password must be change even password expiry is disabled
                    $password_max_age = (int)$this->gadget->registry->fetch('password_max_age', 'Policy') * 3600;
                    if ($user['last_password_update'] == 0 ||
                        ($password_max_age > 0 && ($user['last_password_update'] + $password_max_age < time()))
                    ) {
                        // add expiry step to login-steps
                        $user_login_steps['expiry'] = false;
                        $this->gadget->session->temp_login_steps = $user_login_steps;
                    }

                    // set password step passed
                    $user_login_steps[$login_internal_step] = true;
                    $this->gadget->session->temp_login_steps = $user_login_steps;
                    break;

                case 'key':
                    // get bad logins count
                    $bad_key_cache_key = Jaws_Cache::key('loginstep.key.'.$user['username']);
                    $bad_key_count = $this->app->cache->get($bad_key_cache_key)?? 0;
                    $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
                    if ($bad_key_count >= $max_captcha_login_bad_count) {
                        // check captcha
                        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                        $resCheck = $htmlPolicy->checkCaptcha('login');
                        if (Jaws_Error::IsError($resCheck)) {
                            throw new Exception($resCheck->getMessage(), 401);
                        }
                    }

                    $max_lockedout_login_bad_count = $this->app->registry->fetch('login_bad_count', 'Policy');
                    if ($bad_key_count >= $max_lockedout_login_bad_count) {
                        // forbidden access event logging
                        $this->gadget->event->shout(
                            'Log',
                            array(
                                'action'   => 'Login',
                                'domain'   => $user['domain'],
                                'username' => $user['username'],
                                'priority' => JAWS_WARNING,
                                'result'   => 403,
                                'status'   => false,
                            )
                        );
                        throw new Exception(Jaws::t('ERROR_LOGIN_LOCKED_OUT'), 403);
                    }

                    $loginkey = $this->gadget->session->loginkey;
                    if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300)) ||
                       (isset($loginData['resend']) && ($loginkey['time'] < (time() - 90)))
                    ) {
                        // send notification to user
                        $this->gadget->session->loginkey = $this->gadget->action->load('Login')->NotifyLoginKey($user);
                        if (!empty($loginData['loginkey'])) {
                            throw new Exception(Jaws::t('ERROR_LOGIN_KEY_WRONG'), 403);
                        }

                        throw new Exception('', 201);
                    }

                    // check verification key
                    if ($loginkey['text'] != $loginData['loginkey']) {
                        $lockedout_time = (int)$this->app->registry->fetch('login_lockedout_time', 'Policy');
                        $this->app->cache->set($bad_key_cache_key, $bad_key_count + 1, false, $lockedout_time);
                        throw new Exception(
                            empty($loginData['loginkey'])? '' : Jaws::t('ERROR_LOGIN_KEY_WRONG'),
                            403
                        );
                    }

                    // remove login key
                    $this->gadget->session->delete('loginkey');
                    // delete bad key cache entry, because we got correct key
                    $this->app->cache->delete($bad_key_cache_key);

                    // set key step passed
                    $user_login_steps[$login_internal_step] = true;
                    $this->gadget->session->temp_login_steps = $user_login_steps;
                    break;

                case 'expiry':
                    // changing expired password
                    if ($loginData['password'] === $loginData['old_password']) {
                        throw new Exception($this::t('USERS_PASSWORDS_OLD_EQUAL'), 206);
                    }

                    // trying change password
                    $result = $this->gadget->model->load('User')->updatePassword(
                        $user['id'],
                        $loginData['password'],
                        $loginData['old_password']
                    );
                    if (Jaws_Error::IsError($result)) {
                        throw new Exception($result->getMessage(), 206);
                    }

                    // set password update time
                    $user['last_password_update'] = time();

                    // set expiry step passed
                    $user_login_steps[$login_internal_step] = true;
                    $this->gadget->session->temp_login_steps = $user_login_steps;
                    break;

                default:
                    // user step
                    $loginData['loginstep'] = 'user';
                    $this->gadget->session->temp_login_user = null;
                    // get default steps from registry
                    $login_step_keys = explode(',', $this->gadget->registry->fetch('login_steps'));
                    $user_login_steps = array_combine(
                        $login_step_keys,
                        array_fill(0, count($login_step_keys), false)
                    );
                    $this->gadget->session->temp_login_steps = $user_login_steps;

                    // set default domain if not set
                    $loginData['domain'] = $loginData['domain']?? (int)$this->gadget->registry->fetch('default_domain');

                    if (empty($loginData['username'])) {
                        throw new Exception(Jaws::t('ERROR_LOGIN_WRONG'), 401);
                    }

                    // get bad logins count
                    $addr = Jaws_Utils::GetRemoteAddress();
                    $ipAddr = $addr['public']? $addr['client'] : $addr['proxy'];
                    $bad_ip_cache_key = Jaws_Cache::key('loginstep.user.'. $ipAddr);
                    $bad_ip_count = (int)$this->app->cache->get($bad_ip_cache_key);
                    $max_captcha_login_bad_count = (int)$this->gadget->registry->fetch('login_captcha_status', 'Policy');
                    if ($bad_ip_count >= $max_captcha_login_bad_count) {
                        // check captcha
                        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                        $resCheck = $htmlPolicy->checkCaptcha('login');
                        if (Jaws_Error::IsError($resCheck)) {
                            throw new Exception($resCheck->getMessage(), 401);
                        }
                    }

                    $max_lockedout_login_bad_count = $this->app->registry->fetch('login_bad_count', 'Policy');
                    if ($bad_ip_count >= $max_lockedout_login_bad_count) {
                        // forbidden access event logging
                        $this->gadget->event->shout(
                            'Log',
                            array(
                                'action'   => 'Login',
                                'domain'   => 0,
                                'username' => '',
                                'priority' => JAWS_WARNING,
                                'result'   => 403,
                                'status'   => false,
                            )
                        );
                        throw new Exception(Jaws::t('ERROR_LOGIN_LOCKED_OUT'), 403);
                    }

                    // fetch user information from database
                    $user = $this->gadget->model->load('User')->getByTerm(
                        $loginData['domain'], $loginData['username']
                    );
                    if (Jaws_Error::isError($user)) {
                        throw new Exception($user->getMessage(), $user->getCode());
                    }

                    // increment user trying count, regardless of result
                    $lockedout_time = (int)$this->app->registry->fetch('login_lockedout_time', 'Policy');
                    $this->app->cache->set($bad_ip_cache_key, $bad_ip_count + 1, false, $lockedout_time);

                    if (empty($user)) {
                        throw new Exception($this::t('USER_NOT_EXIST'), 404);
                    }
                    // store user data in session
                    $this->gadget->session->temp_login_user = $user;

                    // set user step passed
                    $user_login_steps[$login_internal_step] = true;
                    $this->gadget->session->temp_login_steps = $user_login_steps;
            }

            // set next step
            $allSteps = array_keys($user_login_steps);
            foreach ($allSteps as $order => $step) {
                if (in_array($loginData['loginstep'], explode('|', $step))) {
                    $nextStepOrder = $order + 1;
                    if (isset($allSteps[$nextStepOrder])) {
                        $nextStep = explode('|', $allSteps[$nextStepOrder])[0];
                        if ($nextStep == 'key') {
                            // send notification to user
                            $this->gadget->session->loginkey = $this->gadget->action->load('Login')->NotifyLoginKey($user);
                        }

                        $loginData['loginstep'] = $nextStep;
                        throw new Exception('', 201);
                    }

                    break;
                }
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
                        'username' => $user['username'],
                        'priority' => JAWS_WARNING,
                        'result'   => 403,
                        'status'   => false,
                    )
                );

                $loginData['loginstep'] = 'user';
                throw new Exception(Jaws::t('ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // fetch user groups
            $groups = $this->gadget->model->load('Group')->list(0, 0, $user['id']);
            if (Jaws_Error::IsError($groups)) {
                $groups = array();
            } else {
                $groups = array_column($groups, 'name', 'id');
            }

            $user['groups'] = $groups;
            $user['avatar'] = $this->gadget->urlMap('Avatar', array('user'  => $user['username']));
            $user['internal'] = true;
            $user['remember'] = $loginData['remember']?? false;
            // user define default data for pass to user login listener gadgets
            $user['defaults'] = $loginData['defaults'];

            // remove temp user data
            $this->gadget->session->delete('temp_login_user');

            $user['referrer'] = $loginData['referrer'];
            return $user;
        } catch (Exception $error) {
            unset($loginData['password'], $loginData['chkpassword']);
            $this->gadget->session->push(
                $error->getMessage(),
                ($error->getCode() == 201)? RESPONSE_NOTICE : (($error->getCode() == 206)? RESPONSE_WARNING : RESPONSE_ERROR),
                'Login.Response',
                $loginData
            );

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode(), JAWS_ERROR_NOTICE);
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

        // if stored authtype in session not found, push error message
        if (is_null($authtype)) {
            $this->gadget->session->push(
                Jaws::t('ERROR_SESSION_NOTFOUND'),
                RESPONSE_WARNING,
                'Login.Response'
            );
        }

        if (Jaws_Error::IsError($result)) {
            http_response_code($result->getCode());
            if (JAWS_SCRIPT == 'index') {
                return Jaws_Header::Location(
                    $this->gadget->urlMap('Login', $urlParams),
                    'Login.Response'
                );
            } else {
                $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
                return Jaws_Header::Location($admin_script?: 'admin.php');
                /*
                return Jaws_Header::Location(
                    $this->gadget->urlMap('Login', $urlParams),
                    'Login.Response'
                );
                */
            }
        }

        // 201 http code for success login
        http_response_code(201);
        return Jaws_Header::Location(
            $referrer,
            'Login.Response'
        );
    }

}