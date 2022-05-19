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
                'domain', 'account', 'rcvstep', 'resend', 'rcvkey',
                'pubkey', 'password', 'usecrypt', 'remember'
            ),
            'post'
        );
        $rcvryData['rcvstep'] = (int)$rcvryData['rcvstep'];

        // set default domain if not set
        if (is_null($rcvryData['domain'])) {
            $rcvryData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
        }

        try {
            if ($rcvryData['rcvstep'] == 3) { // user forgot set password step
                // fetch user data from session
                $userData = $this->gadget->session->temp_recovery_user;
                if (empty($userData)) {
                    $rcvryData['rcvstep'] = 1;
                    throw new Exception($this::t('USERS_INCOMPLETE_FIELDS'), 401);
                }

                if ($rcvryData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $rcvryData['password'] = $JCrypt->decrypt($rcvryData['password']);
                    }
                } else {
                    $rcvryData['password'] = Jaws_XSS::defilter($rcvryData['password']);
                }

                $result = $this->gadget->model->load('User')->updatePassword(
                    (int)$userData['id'],
                    $rcvryData['password'],
                    false
                );
                if (Jaws_Error::IsError($result)) {
                    throw new Exception($result->getMessage(), 206);
                }

                // remove temp user data
                $this->gadget->session->delete('temp_recovery_user');

                $user = $this->gadget->model->load('User')->getUser(
                    (int)$userData['id'],
                    $userData['domain'],
                    array('account' => true)
                );
                if (Jaws_Error::IsError($user) || empty($user)) {
                    $rcvryData['rcvstep'] = 1;
                    throw new Exception($this::t('USER_NOT_EXIST'), 401);
                }

                // fetch user groups
                $groups = $this->gadget->model->load('Groups')->getGroups(0, 0, $user['id']);
                if (Jaws_Error::IsError($groups)) {
                    $groups = array_column($groups, 'name', 'id');
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
                $user['last_password_update'] = time();

                $rcvryData['rcvstep'] = 4;
                unset($rcvryData['password'], $rcvryData['old_password']);
                $this->gadget->session->push(
                    '',
                    RESPONSE_NOTICE,
                    'Recovery.Response',
                    $rcvryData
                );

                return $user;

            } elseif ($rcvryData['rcvstep'] == 2) { // user forgot verification step
                // check captcha
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha('login');
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage(), 401);
                }

                // fetch user data from session
                $userData = $this->gadget->session->temp_recovery_user;
                if (empty($userData)) {
                    $rcvryData['rcvstep'] = 1;
                    throw new Exception($this::t('USERS_INCOMPLETE_FIELDS'), 401);
                }

                $rcvkey = $this->gadget->session->rcvkey;
                if (!isset($rcvkey['text']) || ($rcvkey['time'] < (time() - 300)) ||
                   (!empty($rcvryData['resend']) && ($rcvkey['time'] < (time() - 90)))
                ) {
                    // send recovery key notification to user
                    $this->gadget->action->load('Recovery')->NotifyRecoveryKey($userData);
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }

                // check verification key
                if ($rcvkey['text'] != $rcvryData['rcvkey']) {
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }

                // goto next step
                $rcvryData['rcvstep'] = 3;
                throw new Exception('', 206);
            }

            // user forgot first step
            // check captcha
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha('login');
            if (Jaws_Error::IsError($resCheck)) {
                throw new Exception($resCheck->getMessage(), 401);
            }

            $userData = $this->app->users->GetUserByTerm($rcvryData['domain'], $rcvryData['account']);
            if (Jaws_Error::IsError($userData) || empty($userData)) {
                throw new Exception($this::t('USER_NOT_EXIST'), 401);
            }
            $this->gadget->session->temp_recovery_user = $userData;

            // goto next step
            $rcvryData['rcvstep'] = 2;

            // send notification to user
            $this->gadget->action->load('Recovery')->NotifyRecoveryKey($userData);
            throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);

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
    function LoginRecoveryError($result, $authtype, $referrer)
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
                $this->gadget->urlMap('LoginForgot', $urlParams),
                'Recovery.Response'
            );
        }

        // 201 http code for success login
        http_response_code(201);
        return Jaws_Header::Location(
            $referrer,
            'Recovery.Response'
        );
    }

}