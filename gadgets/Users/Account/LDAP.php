<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_LDAP extends Jaws_Gadget_Action
{
    /**
     * LDAP connection
     *
     * @var     string
     * @access  private
     */
    private $_LdapConnection;

    /**
     * LDAP server
     *
     * @var     string
     * @access  private
     */
    private $_Server = 'localhost';

    /**
     * LDAP port
     *
     * @var     string
     * @access  private
     */
    private $_Port = '389';

    /**
     * LDAP domain name
     *
     * @var     string
     * @access  private
     */
    private $_DN = 'dc=foobar,dc=org';

    /**
     * Builds the login box
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Login()
    {
        if (!function_exists('ldap_connect')) {
            return Jaws_Error::raiseError(
                'Undefined function ldap_connect()',
                __FUNCTION__
            );
        }

        $classname = "Users_Account_Default";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Login();
    }

    /**
     * Authenticate
     *
     * @access  public
     * @return  void
     */
    function Authenticate()
    {
        $loginData = $this->gadget->request->fetch(
            array('domain', 'username', 'password', 'usecrypt', 'loginkey', 'authstep', 'remember'),
            'post'
        );

        try {
            if (!$this->gadget->session->fetch('checksess')) {
                // do logout
                $GLOBALS['app']->Session->Logout();
                throw new Exception(_t('GLOBAL_ERROR_SESSION_NOTFOUND'));
            }

            // check captcha
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha('login');
            if (Jaws_Error::IsError($resCheck)) {
                throw new Exception($resCheck->getMessage());
            }

            $loginData['authstep'] = 0;
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
     * Login Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginError($error, $referrer)
    {
        $urlParams = array();
        $authtype = $this->gadget->request->fetch('authtype');
        if (!empty($get['authtype'])) {
            $urlParams['authtype'] = $get['authtype'];
        }
        if (!empty($referrer)) {
            $urlParams['referrer'] = $referrer;
        }

        if (JAWS_SCRIPT == 'index') {
            return Jaws_Header::Location($this->gadget->urlMap('Login', $urlParams), '', 401);
        } else {
            $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
            $admin_script = empty($admin_script)? 'admin.php' : $admin_script;
            return Jaws_Header::Location($admin_script . (empty($referrer)? '' : "?referrer=$referrer"), '', 401);
        }

    }

}