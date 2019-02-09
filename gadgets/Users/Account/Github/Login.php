<?php
/**
 * Github authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2019 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Account_Github_Login extends Users_Account_Github
{
    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   array   $loginData  Login data(username, password, ...)
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Login()
    {
        // Generate a random hash and store in the session for security
        $state = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
        $this->gadget->session->update('state', $state);
        $this->gadget->session->delete('access_token');

        $params = array(
            'client_id'    => $this->ClientID,
            'redirect_uri' => $this->gadget->urlMap(
                'Authenticate',
                array(),
                array('extension' => false, 'absolute' => true)
            ),
            'scope'        => 'user',
            'state'        => $state
        );

        // Redirect the user to Github's authorization page
        Jaws_Header::Location($this->authorizeURL . '?' . http_build_query($params));
        return false;
    }

}