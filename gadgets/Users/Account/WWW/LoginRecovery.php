<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_LoginRecovery extends Users_Account_WWW
{
    /**
     * Login recovery
     *
     * @access  public
     * @return  void
     */
    function LoginRecovery()
    {
        $classname = "Users_Account_Default_LoginRecovery";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->LoginRecovery();
    }

    /**
     * Login recovery error handling
     *
     * @access  public
     * @return  string  XHTML content
     */
    function LoginRecoveryError($error, $authtype, $referrer)
    {
        $classname = "Users_Account_Default_LoginRecovery";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->LoginRecoveryError($error, $authtype, $referrer);
    }

}