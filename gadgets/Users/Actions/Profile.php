<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
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
        $tplTitle = _t('USERS_ACTIONS_PROFILE');
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
        $tpl->SetVariable('title',  $tplTitle);
        $tpl->SetVariable('avatar', $user['avatar']);
        // username
        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username',     $user['username']);
        // nickname
        $tpl->SetVariable('lbl_nickname', _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('nickname',     $user['nickname']);
        // registered_date
        $tpl->SetVariable('lbl_registered_date', _t('USERS_USERS_REGISTRATION_DATE'));
        $tpl->SetVariable('registered_date',     $user['registered_date']);

        // auto paragraph content
        $user['about'] = $this->gadget->ParseText($user['about']);
        $user = $user + array(
            'lbl_fname'       => _t('USERS_USERS_FIRSTNAME'),
            'lbl_lname'       => _t('USERS_USERS_LASTNAME'),
            'lbl_gender'      => _t('USERS_USERS_GENDER'),
            'lbl_dob'         => _t('USERS_USERS_BIRTHDAY'),
            'lbl_url'         => _t('GLOBAL_URL'),
            'lbl_about'       => _t('USERS_USERS_ABOUT'),
            'lbl_experiences' => _t('USERS_USERS_EXPERIENCES'),
            'lbl_occupations' => _t('USERS_USERS_OCCUPATIONS'),
            'lbl_interests'   => _t('USERS_USERS_INTERESTS'),
        );

        // set about item data
        $tpl->SetVariablesArray($user);

        if ($user['public'] || $GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('profile/public');

            // set profile item data
            $tpl->SetVariablesArray($user);
            if (!empty($user['url'])) {
                $tpl->SetBlock('profile/public/website');
                $tpl->SetVariable('url', $user['url']);
                $tpl->ParseBlock('profile/public/website');
            }
            $tpl->ParseBlock('profile/public');
        }

        $tpl->ParseBlock('profile');
        return $tpl->Get().$this->Activity($user['id'], $user['username']);
    }

    /**
     * Builds user's activity page
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  string  XHTML template content
     */
    function Activity($uid, $uname)
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Profile.html');
        $tpl->SetBlock('activity');
        $tpl->SetVariable('title', _t('USERS_USER_ACTIVITY'));

        $activity = false;
        $gDir = JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $jmsModel = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadgets  = $jmsModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $gInfo) {
            if (!file_exists($gDir . $gadget. '/hooks/Activity.php')) {
                continue;
            }

            $gHook = $GLOBALS['app']->LoadHook($gadget, 'Activity');
            if ($gHook === false) {
                continue;
            }

            $activities = $gHook->Hook($uid, $uname);
            if (Jaws_Error::IsError($activities) || empty($activities)) {
                continue;
            }

            $tpl->SetBlock('activity/gadget');
            $tpl->SetVariable('gadget', _t('USERS_USER_ACTIVITY_IN_GADGET', $gInfo['name']));
            foreach ($activities as $activity) {
                $tpl->SetBlock('activity/gadget/item');
                $tpl->SetVariable('count', $activity['count']);
                $tpl->SetVariable('title', $activity['title']);
                $tpl->SetVariable('url',   $activity['url']);
                $tpl->ParseBlock('activity/gadget/item');
            }
            $activity = true;
            $tpl->ParseBlock('activity/gadget');
        }

        if (!$activity) {
            $tpl->SetBlock('activity/no_activity');
            $tpl->SetVariable('message', _t('USERS_USER_NOT_HAVE_ACTIVITY'));
            $tpl->ParseBlock('activity/no_activity');
        }

        $tpl->ParseBlock('activity');
        return $tpl->Get();
    }

}