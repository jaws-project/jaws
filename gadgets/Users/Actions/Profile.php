<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Profile extends UsersHTML
{
    /**
     * Builds user information page include (personal, contact, ... information)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Profile()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $request =& Jaws_Request::getInstance();
        $user = $request->get('user', 'get');
        if (empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($user, true, true, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        // Avatar
        $user['avatar'] = $usrModel->GetAvatar($user['avatar'],
                                               $user['email'],
                                               $user['last_update']);
        // Gender
        $user['gender'] = _t('USERS_USERS_GENDER_'.$user['gender']);

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Profile.html');
        $tpl->SetBlock('profile');
        $tpl->SetVariable('title',          _t('USERS_PROFILE_INFO'));
        $tpl->SetVariable('lbl_fname',      _t('USERS_USERS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lname',      _t('USERS_USERS_LASTNAME'));
        $tpl->SetVariable('lbl_nickname',   _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('lbl_gender',     _t('USERS_USERS_GENDER'));
        $tpl->SetVariable('lbl_dob',        _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('lbl_url',        _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_about',      _t('USERS_USERS_ABOUT'));
        $tpl->SetVariable('lbl_occupation', _t('USERS_USERS_OCCUPATION'));
        $tpl->SetVariable('lbl_interests',  _t('USERS_USERS_INTERESTS'));
        
        $tpl->SetVariablesArray($user);
        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

}