<?php
/**
 * Github authentication class
 *
 * @category   Auth
 * @package    Core
 */
class Users_Account_Github_Login extends Users_Account_Github
{
    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Login($referrer = '')
    {
        // Generate a random hash and store in the session for security
        $state = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
        $this->gadget->session->state = $state;
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