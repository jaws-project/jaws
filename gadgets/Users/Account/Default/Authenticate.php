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
        $httpAuthEnabled = false;
        if (isset($_SERVER['PHP_AUTH_USER']) &&
            (jaws()->request->method() != 'post') &&
            $GLOBALS['app']->Registry->fetch('http_auth', 'Settings') == 'true'
        ) {
            $httpAuthEnabled = true;
            $httpAuth = new Jaws_HTTPAuth();
            $httpAuth->AssignData();
            jaws()->request->update('username', $httpAuth->getUsername(), 'post');
            jaws()->request->update('password', $httpAuth->getPassword(), 'post');
            jaws()->request->update('usecrypt', 0, 'post');
        }

        $loginData = $this->gadget->request->fetch(
            array('domain', 'username', 'password', 'usecrypt', 'loginkey', 'authstep', 'remember'),
            'post'
        );

        try {
            // check captcha
            if (!$httpAuthEnabled) {
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha('login');
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage(), 401);
                }
            }

            if (empty($loginData['authstep'])) {
                $this->gadget->session->update('temp.login.user', '');
                if ($loginData['username'] === '' && $loginData['password'] === '') {
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

                // set default domain if not set
                if (is_null($loginData['domain'])) {
                    $loginData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
                }

                // fetch user information from database
                $userModel = $GLOBALS['app']->loadObject('Jaws_User');
                $user = $userModel->VerifyUser($loginData['domain'], $loginData['username'], $loginData['password']);
                if (Jaws_Error::isError($user)) {
                    throw new Exception($user->getMessage(), 401);
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

                // two step verification?
                if ((bool)$this->gadget->registry->fetchByUser('two_step_verification', '', $user['id']))
                {
                    $loginData['authstep'] = 1;
                    $this->gadget->session->update('temp.login.user', $user);

                    // send notification to user
                    $this->gadget->action->load('Login')->LoginNotifyKey($user['email'], $user['mobile']);

                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }
            } else {
                // fetch user data from session
                $user = $this->gadget->session->fetch('temp.login.user');
                if (empty($user)) {
                    $loginData['authstep'] = 0;
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 401);
                }

                $loginkey = $this->gadget->session->fetch('loginkey');
                if (!isset($loginkey['text']) || ($loginkey['time'] < (time() - 300))) {
                    // send notification to user
                    $this->gadget->action->load('Login')->LoginNotifyKey($user['email'], $user['mobile']);

                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }

                // check verification key
                if ($loginkey['text'] != $loginData['loginkey']) {
                    throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
                }
            }

            // check user concurrents logins
            $existSessions = 0;
            if (!empty($user['concurrents'])) {
                $existSessions = $GLOBALS['app']->Session->GetUserSessions($user['id'], true);
            }
            if (!empty($existSessions) && $existSessions >= $user['concurrents']) {
                // login conflict event logging
                $GLOBALS['app']->Listener->Shout(
                    'Session',
                    'Log',
                    array('Users', 'Login', JAWS_WARNING, null, 403, $user['id'])
                );

                throw new Exception(_t('GLOBAL_ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // remove login trying count from session
            $this->gadget->session->delete('bad_login_count');
            // remove login key
            $this->gadget->session->delete('loginkey');
            // remove temp user data
            $this->gadget->session->delete('temp.login.user');

            return $user;
        } catch (Exception $error) {
            // increment login trying count in session
            $this->gadget->session->update(
                'bad_login_count',
                (int)$this->gadget->session->fetch('bad_login_count') + 1
            );

            unset($loginData['password']);
            $this->gadget->session->push(
                $error->getMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );
            if ($httpAuthEnabled) {
                return $this->gadget->action->loadAdmin('Login')->Login();
            }

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }

    }

    /**
     * Authorize
     *
     * @access  public
     * @return  void
     */
    function Authorize($loginData = null)
    {
        // check password age
        $password_max_age = (int)$GLOBALS['app']->Registry->fetch('password_max_age', 'Policy');
        if ($password_max_age > 0) {
            $expPasswordTime = time() - 3600 * $password_max_age;
            if ((int)$loginData['last_password_update'] <= $expPasswordTime) {
                $this->gadget->session->push(
                    _t('GLOBAL_ERROR_PASSWORD_EXPIRED'),
                    'Account.Response',
                    RESPONSE_WARNING,
                    $loginData
                );
                return Jaws_Header::Location($this->gadget->urlMap('Account'));
            }
        }

        return true;
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
            $urlParams['authtype'] = $authtype;
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = $referrer;
        }

        http_response_code($error->getCode());
        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location($this->gadget->urlMap('Login', $urlParams));
        } else {
            $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
            $admin_script = empty($admin_script)? 'admin.php' : $admin_script;
            return Jaws_Header::Location($admin_script . (empty($referrer)? '' : "?referrer=$referrer"));
        }

    }

}