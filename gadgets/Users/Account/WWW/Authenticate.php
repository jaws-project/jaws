<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_Authenticate extends Users_Account_WWW
{
    /**
     * Authenticate
     *
     * @access  public
     * @return  void
     */
    function Authenticate()
    {
        if (($this->app->registry->fetch('http_auth', 'Settings') != 'true') ||
            (!isset($_SERVER['PHP_AUTH_USER'])) ||
            ($this->app->request->method() == 'post')
        ) {
            $classname = "Users_Account_Default_Authenticate";
            $objDefaultAccount = new $classname($this->gadget);
            return $objDefaultAccount->Authenticate();
        }

        $httpAuth = new Jaws_HTTPAuth();
        $httpAuth->AssignData();
        $this->app->request->update('username', $httpAuth->getUsername(), 'post');
        $this->app->request->update('password', $httpAuth->getPassword(), 'post');
        $this->app->request->update('usecrypt', 0, 'post');

        $loginData = $this->gadget->request->fetch(
            array('domain', 'username', 'password', 'usecrypt', 'loginkey', 'loginstep', 'remember'),
            'post'
        );

        // set default domain if not set
        if (is_null($loginData['domain'])) {
            $loginData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
        }

        try {
            // get bad logins count
            $bad_logins = $this->gadget->action->load('Login')->BadLogins($loginData['username'], 0);

            $max_lockedout_login_bad_count = $this->app->registry->fetch('password_bad_count', 'Policy');
            if ($bad_logins >= $max_lockedout_login_bad_count) {
                // forbidden access event logging
                $this->gadget->event->shout(
                    'Log',
                    array(
                        'action'   => 'Login',
                        'domain'   => (int)$loginData['domain'],
                        'username' => $loginData['username'],
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
            $user = $this->app->users->VerifyUser($loginData['domain'], $loginData['username'], $loginData['password']);
            if (Jaws_Error::isError($user)) {
                // increase bad logins count
                $this->gadget->action->load('Login')->BadLogins($loginData['username'], 1);
                throw new Exception($user->getMessage(), $user->getCode());
            }

            // fetch user groups
            $groups = $this->app->users->GetGroupsOfUser($user['id']);
            if (Jaws_Error::IsError($groups)) {
                $groups = array();
            }

            $user['groups'] = $groups;
            $user['avatar'] = $this->app->users->GetAvatar(
                $user['avatar'],
                $user['email'],
                48,
                $user['last_update']
            );
            $user['internal'] = true;
            $user['remember'] = (bool)$loginData['remember'];

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
                        'domain'   => (int)$user['domain'],
                        'username' => $user['username'],
                        'priority' => JAWS_WARNING,
                        'result'   => 403,
                        'status'   => false,
                    )
                );

                throw new Exception(Jaws::t('ERROR_LOGIN_CONCURRENT_REACHED'), 409);
            }

            // remove login key
            $this->gadget->session->delete('loginkey');
            // remove temp user data
            $this->gadget->session->delete('temp_login_user');
            // unset bad login entry
            $this->gadget->action->load('Login')->BadLogins($user['username'], -1);

            return $user;
        } catch (Exception $error) {
            unset($loginData['password']);
            $this->gadget->session->push(
                $error->getMessage(),
                RESPONSE_ERROR,
                'Login.Response',
                $loginData
            );

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
        $classname = "Users_Account_Default_Authenticate";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Authorize($loginData);
    }

    /**
     * Authenticate Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AuthenticateError($error, $authtype, $referrer)
    {
        $classname = "Users_Account_Default_Authenticate";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->AuthenticateError($error, $authtype, $referrer);
    }

}