<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Account extends Jaws_Gadget_Model
{
    /**
     * Updates user profile
     *
     * @access  public
     * @param   int      $uid       User ID
     * @param   string   $username  Username
     * @param   string   $nickname  User's display name
     * @param   string   $email     User's email
     * @param   string   $new_email User's new_email
     * @param   string   $mobile    User's mobile number
     * @return  mixed    True on success or Jaws_Error on failure
     */
    function UpdateAccount($uid, $username, $nickname, $email, $new_email, $mobile)
    {
        $uData = array(
            'username' => $username,
            'nickname' => $nickname,
            'email'    => $email,
            'mobile'   => $mobile,
        );
        if (!empty($new_email)) {
            $uData['new_email'] = $new_email;
        }

        $jUser  = new Jaws_User;
        if ($jUser->UserEmailExists($new_email)) {
            return Jaws_Error::raiseError(
                $this::t('EMAIL_ALREADY_EXISTS', $new_email),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        if ($jUser->UserMobileExists($mobile, $uid)) {
            return Jaws_Error::raiseError(
                $this::t('MOBILE_ALREADY_EXISTS', $mobile),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        return $jUser->UpdateUser($uid, $uData);
    }

}