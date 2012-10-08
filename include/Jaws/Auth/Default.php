<?php
/**
 * Default auth class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_Default
{
    /**
     * User model
     * @access  private
     */
    var $_Model;

    /**
     * Authentication ID
     * @access  private
     */
    var $_AuthID = 0;

    /**
     * Constructor
     *
     * @access  public
     */
    function Jaws_Auth_Default()
    {
        $this->_Model = $GLOBALS['app']->loadClass('User', 'Jaws_User');
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     */
    function Auth($user, $password)
    {
        $result = $this->_Model->Valid($user, $password, false);
        if (!Jaws_Error::IsError($result)) {
            $this->_AuthID = $result['id'];
            return $this->_AuthID;
        }

        return $result;
    }

    /**
     * Attributes of logged user
     *
     * @access  public
     */
    function GetAttributes()
    {
        $info = $this->_Model->GetUser($this->_AuthID, true, true, true);
        if (Jaws_Error::IsError($info) || !isset($info['id'])) {
            return false;
        } else {
            $groups = $this->_Model->GetGroupsOfUser($info['username']);
            if (Jaws_Error::IsError($groups)) {
                return false;
            }

            $info['groups'] = $groups;
            $info['avatar'] = $this->_Model->GetAvatar($info['avatar'],
                                                       $info['email'],
                                                       $info['last_update']);
            $info['superadmin'] = $info['superadmin'];
            $info['internal']   = true;
            // Update login time
            $this->_Model->updateLoginTime($this->_AuthID);
        }

        return $info;
    }

}