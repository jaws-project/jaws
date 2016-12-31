<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Profile extends Users_Actions_Default
{
    /**
     * Get AboutUser action params(superadmin users list)
     *
     * @access  public
     * @return  array list of AboutUser action params(superadmin users list)
     */
    function AboutUserLayoutParams()
    {
        $result = array();
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
     * Builds about user information block
     *
     * @access  public
     * @param   string  Optional username
     * @return  string  XHTML template content
     */
    function AboutUser($user)
    {
        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($user, true, true);
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
        $objDate = Jaws_Date::getInstance();
        $user['dob'] = $objDate->Format($user['dob'], 'd MN Y');

        if (!empty($user['registered_date'])) {
            $user['registered_date'] = $objDate->Format($user['registered_date'], 'd MN Y');
        } else {
            $user['registered_date'] = '';
        }

        // Load the template
        $tpl = $this->gadget->template->load('AboutUser.html');
        $tpl->SetBlock('aboutuser');
        $tpl->SetVariable('title',  _t('USERS_ACTIONS_ABOUTUSER'));
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
        $user['about'] = Jaws_String::AutoParagraph($user['about']);
        $user = $user + array(
            'lbl_private'     => _t('USERS_USERS_PRIVATE'),
            'lbl_fname'       => _t('USERS_USERS_FIRSTNAME'),
            'lbl_lname'       => _t('USERS_USERS_LASTNAME'),
            'lbl_gender'      => _t('USERS_USERS_GENDER'),
            'lbl_ssn'         => _t('USERS_USERS_SSN'),
            'lbl_dob'         => _t('USERS_USERS_BIRTHDAY'),
            'lbl_public'      => _t('USERS_USERS_PUBLIC'),
            'lbl_url'         => _t('GLOBAL_URL'),
            'lbl_about'       => _t('USERS_USERS_ABOUT'),
            'lbl_experiences' => _t('USERS_USERS_EXPERIENCES'),
            'lbl_occupations' => _t('USERS_USERS_OCCUPATIONS'),
            'lbl_interests'   => _t('USERS_USERS_INTERESTS'),
        );

        if (!$GLOBALS['app']->Session->IsSuperAdmin() &&
            $GLOBALS['app']->Session->GetAttribute('user') != $user['id'])
        {
            $user['ssn'] = _t('GLOBAL_ERROR_ACCESS_DENIED');
        }

        $tpl->SetVariablesArray($user);

        $tpl->ParseBlock('aboutuser');
        return $tpl->Get();
    }

    /**
     * Builds user information page include (personal, contact, ... information)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Profile()
    {
        $user = jaws()->request->fetch('user', 'get');
        if (empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($user, true, true, true);
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
        $objDate = Jaws_Date::getInstance();
        $user['dob'] = $objDate->Format($user['dob'], 'd MN Y');

        if (!empty($user['registered_date'])) {
            $user['registered_date'] = $objDate->Format($user['registered_date'], 'd MN Y');
        } else {
            $user['registered_date'] = '';
        }

        // Load the template
        $tpl = $this->gadget->template->load('Profile.html');
        $tpl->SetBlock('profile');
        $tpl->SetVariable('title',  _t('USERS_PROFILE_INFO'));
        if ($user['id'] == $GLOBALS['app']->Session->GetAttribute('user')) {
            $tpl->SetVariable('menubar', $this->MenuBar('Profile'));
            $tpl->SetVariable(
                'submenubar',
                $this->SubMenuBar('Profile', array('Profile', 'Account', 'Personal', 'Preferences', 'Contacts'))
            );
        }
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
        $user['about'] = Jaws_String::AutoParagraph($user['about']);
        $user = $user + array(
            'lbl_private'     => _t('USERS_USERS_PRIVATE'),
            'lbl_fname'       => _t('USERS_USERS_FIRSTNAME'),
            'lbl_lname'       => _t('USERS_USERS_LASTNAME'),
            'lbl_gender'      => _t('USERS_USERS_GENDER'),
            'lbl_ssn'         => _t('USERS_USERS_SSN'),
            'lbl_dob'         => _t('USERS_USERS_BIRTHDAY'),
            'lbl_public'      => _t('USERS_USERS_PUBLIC'),
            'lbl_url'         => _t('GLOBAL_URL'),
            'lbl_about'       => _t('USERS_USERS_ABOUT'),
            'lbl_experiences' => _t('USERS_USERS_EXPERIENCES'),
            'lbl_occupations' => _t('USERS_USERS_OCCUPATIONS'),
            'lbl_interests'   => _t('USERS_USERS_INTERESTS'),
        );
 
        if (!$GLOBALS['app']->Session->IsSuperAdmin() &&
            $GLOBALS['app']->Session->GetAttribute('user') != $user['id'])
        {
            $user['ssn'] = _t('GLOBAL_ERROR_ACCESS_DENIED');
        }

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

        $tpl->SetBlock('profile/activity');
        $tpl->SetVariable('lbl_activities', _t('USERS_USER_ACTIVITIES'));
        $this->Activity($tpl, $user['id'], $user['username']);
        $tpl->ParseBlock('profile/activity');

        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

    /**
     * Builds user's activity page
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  string  XHTML template content
     */
    function Activity(&$tpl, $uid, $uname)
    {
        $activity = false;
        $gDir = JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets  = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $gInfo) {
            if (!file_exists($gDir . $gadget. '/Hooks/Users.php')) {
                continue;
            }

            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }
            $objHook = $objGadget->hook->load('Users');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $activities = $objHook->Execute($uid, $uname);
            if (Jaws_Error::IsError($activities) || empty($activities)) {
                continue;
            }

            $tpl->SetBlock('profile/activity/gadget');
            $tpl->SetVariable('gadget', _t('USERS_USER_ACTIVITIES_IN_GADGET', $gInfo['title']));
            foreach ($activities as $activity) {
                $tpl->SetBlock('profile/activity/gadget/item');
                if (isset($activity['count'])) {
                    $tpl->SetBlock('profile/activity/gadget/item/count');
                    $tpl->SetVariable('count', $activity['count']);
                    $tpl->ParseBlock('profile/activity/gadget/item/count');
                }
                $tpl->SetVariable('title', $activity['title']);
                $tpl->SetVariable('url',   $activity['url']);
                $tpl->ParseBlock('profile/activity/gadget/item');
            }
            $activity = true;
            $tpl->ParseBlock('profile/activity/gadget');
        }

        if (!$activity) {
            $tpl->SetBlock('profile/activity/no_activity');
            $tpl->SetVariable('message', _t('USERS_USER_ACTIVITIES_EMPTY'));
            $tpl->ParseBlock('profile/activity/no_activity');
        }
    }

}