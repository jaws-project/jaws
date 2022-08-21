<?php
/**
 * LDAP login class
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
     * @param   string  $referrer   Referrer page url
     * @return  string  XHTML content
     */
    function Login($defaults = '', $referrer = '')
    {
        if (!function_exists('ldap_connect')) {
            return Jaws_Error::raiseError(
                'Undefined function ldap_connect()',
                __FUNCTION__
            );
        }

        $classname = "Users_Account_Default_Login";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Login($referrer);
    }

}