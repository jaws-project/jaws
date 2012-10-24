<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Admin_UserACL extends Jaws_Gadget_Model
{
    /**
     * Gets ACL permissions of the user
     *
     * @access  public
     * @param   string  $username  Username
     * @return  mixed   Array of ACL Keys or false
     */
    function GetUserACLKeys($username)
    {
        $acls = $GLOBALS['app']->ACL->GetAclPermissions($username, false);
        $perms = array();
        if (is_array($acls)) {
            foreach ($acls as $gadget => $keys) {
                $g = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($g)) {
                    continue;
                }

                if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
                    continue;
                }

                if (!isset($perms[$gadget])) {
                    $perms[$gadget] = array();
                    $perms[$gadget]['name'] = _t(strtoupper($gadget).'_NAME');
                }

                foreach ($keys as $k) {
                    $aclkey = '/ACL'.str_replace('/ACL/users/'.$username, '', $k['name']);

                    $perms[$gadget][$aclkey] = array(
                        'desc'  => $g->GetACLDescription($aclkey),
                        'value' => $k['value'],
                        'name'  => $k['name'],
                    );
                }
            }

            ksort($perms);
            return $perms;
        }

        return false;
    }

    /**
     * Updates modified user ACL keys
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   array   $keys   ACL Keys
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateUserACL($uid, $keys)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        if ($user = $userModel->GetUser((int)$uid)) {
            //Load user keys
            $GLOBALS['app']->ACL->LoadAllFiles();
            $GLOBALS['app']->ACL->LoadKeysOf($user['username'], 'users');
            foreach($keys as $key => $value) {
                //check user permission for this key
                $expkey = explode('/', $key);
                $aclkey = end($expkey);
                $gadget = prev($expkey);
                if (!$GLOBALS['app']->Session->GetPermission($gadget, $aclkey)) {
                    continue;
                }

                //Get the current value
                if ($key == '/ACL/users/' . $user['username'] . '/gadgets/ControlPanel/default' &&
                    $value === false && $uid == $currentUser)
                {
                    return new Jaws_Error(_t('USERS_USERS_CANT_AUTO_TURN_OFF_CP'), _t('USERS_NAME'));
                }

                if (is_null($value)) {
                    $GLOBALS['app']->ACL->DeleteKey($key);
                } else {
                    $valueString = ($value === true)? 'true' : 'false';
                    if (is_null($GLOBALS['app']->ACL->Get($key))) {
                        $GLOBALS['app']->ACL->NewKey($key, $valueString);
                    } else {
                        $GLOBALS['app']->ACL->Set($key, $valueString);
                    }
                }
            }
            return true;
        }

        return new Jaws_Error(_t('USERS_USER_NOT_EXIST'), _t('USERS_NAME'));
    }

}