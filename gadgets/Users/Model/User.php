<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_User extends Jaws_Gadget_Model
{
    /**
     * Updates user profile
     *
     * @access  public
     * @param   int     $id     User ID
     * @param   array   $pData  Personal information data
     * @return  bool    Returns true on success, false on failure
     */
    function updatePersonal($id, $pData)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($pData),
            array('fname', 'lname', 'gender', 'ssn', 'dob', 'extra',
                'pgpkey', 'signature', 'about', 'experiences',
                'occupations', 'interests', 'avatar', 'privacy',
            )
        );
        foreach ($invalids as $invalid) {
            unset($pData[$invalid]);
        }

        // get user information
        $user = $this->getUser((int)$id, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            
            if (!isset($this->app) ||
                !property_exists($this->app, 'session') ||
                $this->app->session->user->id != $user['id']
            ) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (array_key_exists('avatar', $pData)) {
            if (!empty($user['avatar'])) {
                Jaws_Utils::Delete(AVATAR_PATH. $user['avatar']);
            }

            if (!empty($pData['avatar'])) {
                $fileinfo = pathinfo($pData['avatar']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    if (!in_array($fileinfo['extension'], array('gif','jpg','jpeg','png','svg'))) {
                        return false;
                    } else {
                        $new_avatar = $user['username']. '.'. $fileinfo['extension'];
                        @rename(Jaws_Utils::upload_tmp_dir(). '/'. $pData['avatar'],
                                AVATAR_PATH. $new_avatar);
                        $pData['avatar'] = $new_avatar;
                    }
                }
            }
        }

        $pData['last_update'] = time();
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $result = $usersTable->update($pData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (isset($this->app) && property_exists($this->app, 'session') && $this->app->session->user->id == $id) {
            foreach($pData as $k => $v) {
                if ($k == 'avatar') {
                    $this->app->session->user = array(
                        'avatar' => $this->app->users->GetAvatar($v, $user['email'], 48, $pData['last_update'])
                    );
                } else {
                    $this->app->session->user = array($k => $v);
                }
            }
        }

        // Let everyone know a user has been added
        $res = $this->gadget->event->shout(
            'UserChanges',
            array('action' => 'UpdateUser', 'user' => $id)
        );
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return true;
    }

}