<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_WWW_Registration extends Users_Account_WWW
{
    /**
     * Builds the registration form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Registration($defaults = '', $referrer = '')
    {
        $classname = "Users_Account_Default_Registration";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Registration($referrer);
    }

}