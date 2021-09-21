<?php
/**
 * Github authentication class
 *
 * @category   Auth
 * @package    Core
 */
class Users_Account_Github_Authenticate extends Users_Account_Github
{
    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   array   $loginData  Login data(username, password, ...)
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Authenticate()
    {
        $get = $this->gadget->request->fetch(array('error', 'error_description', 'state', 'code'), 'get');
        if(isset($get['error'])) {
            return Jaws_Error::raiseError($get['error_description'], __FUNCTION__);
        }

        // Verify the state matches our stored state
        if(!$get['state'] || $this->gadget->session->state != $get['state']) {
            return Jaws_Error::raiseError('state not matched!', __FUNCTION__);
        }

        //
        $httpRequest = new Jaws_HTTPRequest();
        $httpRequest->setHeader('Content-Type', 'application/json');
        $httpRequest->httpRequest->setHeader('Accept', 'application/json');
        if ($this->gadget->session->access_token) {
            $httpRequest->httpRequest->setHeader(
                'Authorization',
                'Bearer ' . $this->gadget->session->access_token
            );
        }

        $postData = json_encode(array(
            'client_id'     => $this->ClientID,
            'client_secret' => $this->ClientSecret,
            'state'         => $get['state'],
            'code'          => $get['code'],
            'redirect_uri'  => $this->gadget->urlMap(
                'Authenticate',
                array(),
                array('extension' => false, 'absolute' => true)
            ),
        ));
        $result = $httpRequest->rawPostData($this->tokenURL, $postData);
        if (Jaws_Error::IsError($result) || $result['status'] != 200) {
            return Jaws_Error::raiseError('Token URL post error!', __FUNCTION__);
        }

        $token = json_decode($result['body'], true);
        if (isset($token['access_token'])) {
            $this->gadget->session->access_token = $token['access_token'];
        } else {
            return Jaws_Error::raiseError($token['error_description'], $token['error']);
        }

        //
        $httpRequest->setHeader('Content-Type', 'application/json');
        $httpRequest->setHeader('Accept', 'application/json');
        if ($this->gadget->session->access_token) {
            $httpRequest->httpRequest->setHeader(
                'Authorization',
                'Bearer ' . $this->gadget->session->access_token
            );
        }
        $result = $httpRequest->get($this->apiBaseURL . 'user');
        if (Jaws_Error::IsError($result) || $result['status'] != 200) {
            return Jaws_Error::raiseError('Fetch authorize error!', __FUNCTION__);
        }

        $loginData = json_decode($result['body'], true);

        $user = array();
        $user['id']          = strtolower('Github:'.$loginData['login']);
        $user['internal']    = false;
        $user['domain']      = 0;
        $user['username']    = $loginData['login'];
        $user['password']    = '';
        $user['superadmin']  = false;
        $user['groups']      = array();
        $user['logon_hours'] = '';
        $user['expiry_date'] = 0;
        $user['nickname']    = $loginData['name'];
        $user['concurrents'] = 0;
        $user['email']       = $loginData['email'];
        $user['mobile']      = '';
        $user['ssn']         = '';
        $user['url']         = $loginData['html_url'];
        $user['avatar']      = $loginData['avatar_url'];
        $user['last_password_update'] = time();
        $user['language']    = '';
        $user['theme']       = '';
        $user['editor']      = '';
        $user['timezone']    = null;
        $user['remember']    = false;
        return $user;
    }

    /**
     * Login Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AuthenticateError($error, $authtype, $referrer)
    {
        return 'Github Authentication Error: '. $error->getMessage();
    }
}