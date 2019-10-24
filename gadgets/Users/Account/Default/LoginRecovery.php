<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_LoginRecovery extends Users_Account_Default
{
    /**
     * Login recovery
     *
     * @access  public
     * @return  void
     */
    function LoginRecovery()
    {
        $rcvryData = $this->gadget->request->fetch(
            array(
                'domain', 'account', 'rcvstep', 'resend', 'rcvkey', 'usecrypt', 'remember'
            ),
            'post'
        );

        try {
            // check captcha
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha('login');
            if (Jaws_Error::IsError($resCheck)) {
                throw new Exception($resCheck->getMessage(), 401);
            }

            if (empty($rcvryData['rcvstep'])) {
                /*
                $this->gadget->session->delete('temp_recovery_user');
                if ($rcvryData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $rcvryData['password'] = $JCrypt->decrypt($rcvryData['password']);
                    }
                } else {
                    $rcvryData['password'] = Jaws_XSS::defilter($rcvryData['password']);
                }
                */

                // set default domain if not set
                if (is_null($rcvryData['domain'])) {
                    $rcvryData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
                }

                $userData = $this->app->users->GetUserByTerm($rcvryData['domain'], $rcvryData['account']);
                if (Jaws_Error::IsError($userData) || empty($userData)) {
                    throw new Exception(_t('USERS_USER_NOT_EXIST'), 401);
                }

                $rcvryData['rcvstep'] = 1;
                $this->gadget->session->temp_recovery_user = $userData;

                // send notification to user
                $this->gadget->action->load('Recovery')->NotifyRecoveryKey($userData);

                throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);

            }

            // fetch user data from session
            $userData = $this->gadget->session->temp_recovery_user;
            if (empty($userData)) {
                $rcvryData['rcvstep'] = 0;
                throw new Exception(_t('USERS_USERS_INCOMPLETE_FIELDS'), 401);
            }

            $rcvkey = $this->gadget->session->rcvkey;
            if (!isset($rcvkey['text']) || ($rcvkey['time'] < (time() - 300)) ||
               (!empty($rcvryData['resend']) && ($rcvkey['time'] < (time() - 90)))
            ) {
                // send recovery key notification to user
                $this->gadget->action->load('Recovery')->NotifyRecoveryKey($userData);
                throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
            }

            // check verification key
            if ($rcvkey['text'] != $rcvryData['rcvkey']) {
                throw new Exception(_t('GLOBAL_LOGINKEY_REQUIRED'), 206);
            }

            $user = $this->app->users->GetUserNew(
                $userData['domain'],
                (int)$userData['id'],
                array('account' => true)
            );
            if (Jaws_Error::IsError($user) || empty($user)) {
                $rcvryData['rcvstep'] = 0;
                throw new Exception(_t('USERS_USER_NOT_EXIST'), 401);
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
            $user['remember'] = false;
            // force user to change his password
            $user['last_password_update'] = -1;

            return $user;

        } catch (Exception $error) {
            unset($rcvryData['password'], $rcvryData['password_check']);
            $this->gadget->session->push(
                $error->getMessage(),
                ($error->getCode() == 201)? RESPONSE_NOTICE : RESPONSE_ERROR,
                'Recovery.Response',
                $rcvryData
            );

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Login recovery error handling
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginRecoveryError($error, $authtype, $referrer)
    {
        $urlParams = array();
        if (!empty($authtype)) {
            $urlParams['authtype'] = strtolower($authtype);
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = $referrer;
        }

        http_response_code($error->getCode());
        return Jaws_Header::Location($this->gadget->urlMap('LoginForgot', $urlParams));
    }

}