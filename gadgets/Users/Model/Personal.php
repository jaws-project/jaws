<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Personal extends Jaws_Gadget_Model
{
    /**
     * Updates user profile
     *
     * @access  public
     * @param   int     $uid       User ID
     * @param   array   $pData     First name
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdatePersonal($uid, $pData)
    {
        $jUser = new Jaws_User;
        $result = $jUser->UpdatePersonal($uid, $pData);
        return $result;
    }

}