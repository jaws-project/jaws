<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
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