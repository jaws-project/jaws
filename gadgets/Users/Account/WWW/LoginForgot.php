<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_LoginForgot extends Users_Account_WWW
{
    /**
     * Builds the login forgot form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function LoginForgot($defaults = '', $referrer = '')
    {
        $classname = "Users_Account_Default_LoginForgot";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->LoginForgot($referrer);
    }

}