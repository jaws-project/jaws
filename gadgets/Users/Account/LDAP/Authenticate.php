<?php
/**
 * LDAP Authenticate class
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_LDAP_Authenticate extends Users_Account_LDAP
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
            array('domain', 'username', 'password', 'usecrypt', 'loginkey', 'loginstep', 'remember'),
            'post'
        );

        try {
            // check captcha
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha('login');
            if (Jaws_Error::IsError($resCheck)) {
                throw new Exception($resCheck->getMessage());
            }

            $loginData['loginstep'] = 0;
            if ($loginData['username'] === '' && $loginData['password'] === '') {
                throw new Exception(_t('GLOBAL_ERROR_LOGIN_WRONG'));
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

            $this->_LdapConnection = @ldap_connect($this->_Server, $this->_Port);
            if ($this->_LdapConnection) {
                $rdn = "uid=" . $loginData['username'] . "," . $this->_DN;
                $bind = @ldap_bind($this->_LdapConnection, $rdn, $loginData['password']);
                if ($bind) {
                    $filter="(uid=" . $loginData['username'] . ")";
                    $searchResult = ldap_search($this->_LdapConnection, $this->_DN, $filter);
                    if (@ldap_count_entries($this->_LdapConnection, $searchResult) > 1) {
                        $ldapUserInfo = @ldap_get_entries($this->_LdapConnection, $searchResult);
                    } else {
                        //throw new Exception("Can not find user info!");
                    }

                    $user = array();
                    $user['id']          = strtolower('LDAP:'.$loginData['username']);
                    $user['internal']    = false;
                    $user['domain']      = (int)$loginData['domain'];
                    $user['username']    = $loginData['username'];
                    $user['password']    = '';
                    $user['superadmin']  = false;
                    $user['groups']      = array();
                    $user['logon_hours'] = '';
                    $user['expiry_date'] = 0;
                    $user['nickname']    = $loginData['username'];
                    $user['concurrents'] = 0;
                    $user['email']       = '';
                    $user['mobile']      = '';
                    $user['ssn']         = '';
                    $user['url']         = '';
                    $user['avatar']      = 'gadgets/Users/Resources/images/photo48px.png';
                    $user['last_password_update'] = time();
                    $user['language']    = '';
                    $user['theme']       = '';
                    $user['editor']      = '';
                    $user['timezone']    = null;
                    $user['remember']    = false;
                    return $user;
                } else {
                    throw new Exception("LDAP bind to $rdn failed!");
                }
            } else {
                throw new Exception("LDAP connection to {$this->_Server}:{$this->_Port} failed!");
            }
        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                'Login.Response',
                RESPONSE_ERROR,
                $loginData
            );

            return Jaws_Error::raiseError($error->getMessage(), __FUNCTION__);
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