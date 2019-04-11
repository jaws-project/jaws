<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_Register extends Users_Account_WWW
{
    /**
     * Register
     *
     * @access  public
     * @return  void
     */
    function Register()
    {
        $classname = "Users_Account_Default_Register";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Register();
    }

    /**
     * Register Error
     *
     * @access  public
     * @return  string  XHTML content
     */
    function RegisterError($error, $authtype, $referrer)
    {
        $classname = "Users_Account_Default_Register";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->RegisterError($error, $authtype, $referrer);
    }

}