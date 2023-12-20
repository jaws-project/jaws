<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Default_Register extends Users_Account_Default
{
    /**
     * Register
     *
     * @access  public
     * @return  void
     */
    function Register()
    {
        $rgstrData = $this->gadget->request->fetch(
            array(
                'domain', 'username', 'email', 'mobile', 'nickname', 'password',
                'fname', 'lname', 'gender', 'ssn', 'dob', 'regstep', 'resend', 'regkey', 'usecrypt',
                'remember', 'defaults:array'
            ),
            'post'
        );

        $rgstrData['regstep'] = (int)$rgstrData['regstep'];

        // set default domain if not set
        if (is_null($rgstrData['domain'])) {
            $rgstrData['domain'] = (int)$this->gadget->registry->fetch('default_domain');
        }

        try {
            if ($rgstrData['regstep'] == 2) {
                // check captcha
                $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
                $resCheck = $htmlPolicy->checkCaptcha();
                if (Jaws_Error::IsError($resCheck)) {
                    throw new Exception($resCheck->getMessage(), 401);
                }

                // fetch user data from session
                $userData = $this->gadget->session->temp_register_user;
                if (empty($userData)) {
                    $rgstrData['regstep'] = 1;
                    throw new Exception($this::t('USERS_INCOMPLETE_FIELDS'), 401);
                }

                $regkey = $this->gadget->session->regkey;
                if (!isset($regkey['text']) || ($regkey['time'] < (time() - 300)) ||
                   (!empty($rgstrData['resend']) && ($regkey['time'] < (time() - 90)))
                ) {
                    // send notification to user
                    $this->gadget->action->load('Registration')->NotifyRegistrationKey($userData);
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }

                // check verification key
                if ($regkey['text'] != $rgstrData['regkey']) {
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }

                // update user status(enabled)
                $this->gadget->model->load('Registration')->updateUserStatus($userData['id'], 1);

            } else {
                $rgstrData['regstep'] = 1;

                if (empty($rgstrData['password'])) {
                    throw new Exception($this::t('USERS_INCOMPLETE_FIELDS'), 401);
                }

                if ($rgstrData['usecrypt']) {
                    $JCrypt = Jaws_Crypt::getInstance();
                    if (!Jaws_Error::IsError($JCrypt)) {
                        $rgstrData['password'] = $JCrypt->decrypt($rgstrData['password']);
                    }
                } else {
                    $rgstrData['password'] = Jaws_XSS::defilter($rgstrData['password']);
                }
                // birthday
                $dob = null;
                if (!empty($rgstrData['dob'])) {
                    $dob = Jaws_Date::getInstance()->ToBaseDate(explode('-', $rgstrData['dob']), 'Y-m-d');
                    $dob = $this->app->UserTime2UTC($dob, 'Y-m-d');
                }
                $rgstrData['dob'] = $dob;

                $userData = $this->gadget->model->load('Registration')->InsertUser($rgstrData);
                if (Jaws_Error::IsError($userData)) {
                    throw new Exception($userData->getMessage(), 401);
                }
                // user define default data for pass to user register listener gadgets
                $userData['defaults'] = $rgstrData['defaults'];
                // store user data in session
                $this->gadget->session->temp_register_user = $userData;

                if ($this->gadget->registry->fetch('anon_activation') == 'user')
                {
                    $rgstrData['regstep'] = 2;
                    // send notification to user
                    $this->gadget->action->load('Registration')->NotifyRegistrationKey($userData);
                    throw new Exception(Jaws::t('LOGINKEY_REQUIRED'), 206);
                }
            }

            $rgstrData['regstep'] = 3;
            // remove temp user date from session
            $this->gadget->session->delete('temp_register_user');

            $this->gadget->session->push(
                $this::t('REGISTRATION_ACTIVATED'),
                RESPONSE_NOTICE,
                'Registration.Response',
                $rgstrData
            );

            // auto login if user activated
            if ($this->gadget->registry->fetch('anon_activation') != 'admin') {
                unset($userData['password']);

                // add required attributes for auto login into jaws
                $userData['internal']    = true;
                $userData['superadmin']  = false;
                $userData['logon_hours'] = '';
                $userData['expiry_date'] = 0;
                $userData['concurrents'] = 0;
                $userData['avatar']      = 'gadgets/Users/Resources/images/photo48px.png';
                $userData['remember']    = (bool)$rgstrData['remember'];
                $userData['last_password_update'] = time();
                return $userData;
            }

            throw new Exception($this::t('REGISTRATION_REGISTERED'), 201);

        } catch (Exception $error) {
            unset($rgstrData['password']);
            $this->gadget->session->push(
                $error->getMessage(),
                ($error->getCode() == 201)? RESPONSE_NOTICE : RESPONSE_ERROR,
                'Registration.Response',
                $rgstrData
            );

            return Jaws_Error::raiseError($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Register Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function RegisterError($result, $authtype, $referrer)
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
                $this->gadget->urlMap('Registration', $urlParams),
                'Registration.Response'
            );
        }

        // 201 http code for success login
        http_response_code(201);
        return Jaws_Header::Location(
            $referrer,
            'Registration.Response'
        );
    }

}