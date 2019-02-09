<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_LDAP_Login extends Users_Account_LDAP
{
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

}