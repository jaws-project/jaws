<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Extra extends Jaws_Gadget_Model
{
    /**
     * Gets extra information of the user
     *
     * @access  public
     * @param   int     $uid    User ID
     * @return  array   Extra attributes
     */
    function GetUserExtra($uid)
    {
        return Jaws_ORM::getInstance()
            ->table('users')
            ->select('mailquota:integer', 'ftpquota:integer')
            ->where('id', (int)$uid)
            ->fetchRow();
    }

    /**
     * Updates extra information of the user
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   array   $data   Extra data
     * @return  array   Response array (notice or error)
     */
    function UpdateExtra($uid, $data)
    {
        $data['last_update'] = time();
        return Jaws_ORM::getInstance()
            ->table('users')
            ->update($data)
            ->where('id', (int)$uid)
            ->exec();
    }

}