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
class Users_Actions_Profile extends Jaws_Gadget_HTML
{
    /**
     * Get Profile action params(superadmin users list)
     *
     * @access  public
     * @return  array list of Profile action params(superadmin users list)
     */
    function ProfileLayoutParams()
    {
        $result = array();
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        $users = $usrModel->GetUsers(false, true);
        if (!Jaws_Error::IsError($users)) {
            $pusers = array();
            foreach ($users as $user) {
                $pusers[$user['username']] = $user['nickname'];
            }

            $result[] = array(
                'title' => _t('USERS_USERS'),
                'value' => $pusers
            );
        }

        return $result;
    }

    /**
     * Builds user information page include (personal, contact, ... information)
     *
     * @access  public
     * @param   string  Optional username
     * @return  string  XHTML template content
     */
    function Profile($user = '')
    {
        $tplFile  = 'AboutUser.html';
        $tplTitle = _t('USERS_LAYOUT_PROFILE');
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (empty($user)) {
            $request =& Jaws_Request::getInstance();
            $user = $request->get('user', 'get');
            if (empty($user)) {
                return Jaws_HTTPError::Get(404);
            }

            $tplFile  = 'Profile.html';
            $tplTitle = _t('USERS_PROFILE_INFO');
        }

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($user, true, true, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        // Avatar
        $user['avatar'] = $usrModel->GetAvatar(
            $user['avatar'],
            $user['email'],
            128,
            $user['last_update']
        );

        // Gender
        $user['gender'] = _t('USERS_USERS_GENDER_'.$user['gender']);

        // Date of birth
        $objDate = $GLOBALS['app']->loadDate();
        $user['dob'] = $objDate->Format($user['dob'], 'd MN Y');

        if (!empty($user['registered_date'])) {
            $user['registered_date'] = $objDate->Format($user['registered_date'], 'd MN Y');
        } else {
            $user['registered_date'] = '';
        }

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        // $tplFile is Profile.html or AboutUser.html
        $tpl->Load($tplFile);
        $tpl->SetBlock('profile');
        $tpl->SetVariable('title',          $tplTitle);
        $tpl->SetVariable('lbl_username',   _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('lbl_fname',      _t('USERS_USERS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lname',      _t('USERS_USERS_LASTNAME'));
        $tpl->SetVariable('lbl_nickname',   _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('lbl_gender',     _t('USERS_USERS_GENDER'));
        $tpl->SetVariable('lbl_dob',        _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('lbl_url',        _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_about',      _t('USERS_USERS_ABOUT'));
        $tpl->SetVariable('lbl_occupation', _t('USERS_USERS_OCCUPATION'));
        $tpl->SetVariable('lbl_interests',  _t('USERS_USERS_INTERESTS'));
        $tpl->SetVariable('lbl_registered_date', _t('USERS_USERS_REGISTRATION_DATE'));
        $tpl->SetVariablesArray($user);
        if (!empty($user['url'])) {
            $tpl->SetBlock('profile/website');
            $tpl->SetVariable('url', $user['url']);
            $tpl->ParseBlock('profile/website');
        }
        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

}