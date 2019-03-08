<?php
/**
 * POP3 login class
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_POP3_Login extends Users_Account_POP3
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
        if (!function_exists('imap_open')) {
            return Jaws_Error::raiseError(
                'Undefined function imap_open()',
                __FUNCTION__
            );
        }

        $classname = "Users_Account_Default_Login";
        $objDefaultAccount = new $classname($this->gadget);
        return $objDefaultAccount->Login($referrer);
    }

}