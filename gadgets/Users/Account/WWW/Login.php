<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_Login extends Users_Account_WWW
{
    /**
     * Builds the login box
     *
     * @access  public
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function Login($referrer = '')
    {
        if ($this->app->registry->fetch('http_auth', 'Settings') == 'true') {
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $this->gadget->action->load('Login')->Authenticate();
            } else {
                $httpAuth = new Jaws_HTTPAuth();
                $httpAuth->showLoginBox();
                return false;
            }
        }

        $classname = "Users_Account_Default_Login";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Login($referrer);
    }

}