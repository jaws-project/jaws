<?php
/**
 * Users Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Login extends Jaws_Gadget_Action
{
    /**
     * Get HTML login form
     *
     * @access  public
     * @return  string  XHTML template of the login form
     */
    function Login()
    {
        return $this->gadget->action->load('Login')->Login();
    }

    /**
     * Logins user, if something goes wrong then redirect user to login box and notify the error
     *
     * @access  public
     * @return  void
     */
    function Authenticate()
    {
        return $this->gadget->action->load('Login')->Authenticate();
    }

    /**
     * Logout user
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        return $this->gadget->action->load('Login')->Logout();
    }

}