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
class Users_Model_Admin_GroupACL extends Jaws_Model
{
    /**
     * Returns an array with the ACL keys of a given group
     *
     * @access  public
     * @param   int     $guid   Group's ID
     * @return  array  List of ACL Keys
     */
    function GetGroupACLKeys($guid)
    {
        $acls = $GLOBALS['app']->ACL->GetGroupAclPermissions($guid);
        $perms = array();
        if (is_array($acls)) {
            foreach ($acls as $gadget => $keys) {
                if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
                    continue;
                }

                $g = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($g)) {
                    continue;
                }

                if (!isset($perms[$gadget])) {
                    $perms[$gadget] = array();
                    $perms[$gadget]['name'] = _t(strtoupper($gadget).'_NAME');
                }

                foreach ($keys as $k) {
                    $aclkey = '/ACL'.str_replace('/ACL/groups/'.$guid, '', $k['name']);
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
     * Updates the modified ACL group keys
     *
     * @access  public
     * @param   int     $guid   Group ID
     * @param   array   $keys   ACL Keys
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateGroupACL($guid, $keys)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();

        if ($group = $userModel->GetGroup((int)$guid)) {
            $GLOBALS['app']->ACL->LoadAllFiles();
            $GLOBALS['app']->ACL->LoadKeysOf($guid, 'groups');
            foreach ($keys as $key => $value) {
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
        return new Jaws_Error(_t('USERS_GROUPS_GROUP_NOT_EXIST'), _t('USERS_NAME'));
    }

}