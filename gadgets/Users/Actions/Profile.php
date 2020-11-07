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
        $users = $usrModel->GetUsers(false, false, true);
        if (!Jaws_Error::IsError($users)) {
            $pusers = array();
            $pusers[0] = $this::t('LOGGED_USER');
            foreach ($users as $user) {
                $pusers[$user['username']] = $user['nickname'];
            }

            $result[] = array(
                'title' => $this::t('USERS'),
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
        if (empty($user)) {
            if (!$this->app->session->user->logged) {
                return false;
            }
            $user = (int)$this->app->session->user->id;
        }

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
        $user['gender'] = $this::t('USERS_GENDER_'.$user['gender']);

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
        $tpl->SetVariable('title',  $this::t('ACTIONS_ABOUTUSER'));
        $tpl->SetVariable('avatar', $user['avatar']);
        // username
        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username',     $user['username']);
        // nickname
        $tpl->SetVariable('lbl_nickname', $this::t('USERS_NICKNAME'));
        $tpl->SetVariable('nickname',     $user['nickname']);
        // registered_date
        $tpl->SetVariable('lbl_registered_date', $this::t('USERS_REGISTRATION_DATE'));
        $tpl->SetVariable('registered_date',     $user['registered_date']);

        // auto paragraph content
        $user['about'] = Jaws_String::AutoParagraph($user['about']);
        $user = $user + array(
            'lbl_private'     => $this::t('USERS_PRIVATE'),
            'lbl_fname'       => $this::t('USERS_FIRSTNAME'),
            'lbl_lname'       => $this::t('USERS_LASTNAME'),
            'lbl_gender'      => $this::t('USERS_GENDER'),
            'lbl_ssn'         => $this::t('USERS_SSN'),
            'lbl_dob'         => $this::t('USERS_BIRTHDAY'),
            'lbl_public'      => $this::t('USERS_PUBLIC'),
            'lbl_url'         => Jaws::t('URL'),
            'lbl_about'       => $this::t('USERS_ABOUT'),
            'lbl_experiences' => $this::t('USERS_EXPERIENCES'),
            'lbl_occupations' => $this::t('USERS_OCCUPATIONS'),
            'lbl_interests'   => $this::t('USERS_INTERESTS'),
        );

        if (!$this->app->session->user->superadmin &&
            $this->app->session->user->id != $user['id'])
        {
            $user['ssn'] = Jaws::t('ERROR_ACCESS_DENIED');
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
        $user = $this->gadget->request->fetch('user', 'get');
        $user = $user?: $this->app->session->user->username;
        if (empty($user)) {
            return Jaws_Header::Location(
                $this->gadget->urlMap('Login', array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true))))
            );
        }

        if ($this->app->session->user->username != $user &&
            !$this->gadget->GetPermission('AccessUsersProfile')
        ) {
            return Jaws_HTTPError::Get(403);
        }

        $user = $this->app->users->GetUser($user, true, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        // Avatar
        $user['avatar'] = $this->app->users->GetAvatar(
            $user['avatar'],
            $user['email'],
            128,
            $user['last_update']
        );

        // Gender
        $user['gender'] = $this::t('USERS_GENDER_'.$user['gender']);

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
        $tpl->SetVariable('title',  $this::t('PROFILE_INFO'));
        if ($user['id'] == $this->app->session->user->id) {
            // Menu navigation
            $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        }
        $tpl->SetVariable('avatar', $user['avatar']);
        // username
        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username',     $user['username']);
        // nickname
        $tpl->SetVariable('lbl_nickname', $this::t('USERS_NICKNAME'));
        $tpl->SetVariable('nickname',     $user['nickname']);
        // registered_date
        $tpl->SetVariable('lbl_registered_date', $this::t('USERS_REGISTRATION_DATE'));
        $tpl->SetVariable('registered_date',     $user['registered_date']);

        // auto paragraph content
        $user['about'] = Jaws_String::AutoParagraph($user['about']);
        $user = $user + array(
            'lbl_private'     => $this::t('USERS_PRIVATE'),
            'lbl_fname'       => $this::t('USERS_FIRSTNAME'),
            'lbl_lname'       => $this::t('USERS_LASTNAME'),
            'lbl_gender'      => $this::t('USERS_GENDER'),
            'lbl_ssn'         => $this::t('USERS_SSN'),
            'lbl_dob'         => $this::t('USERS_BIRTHDAY'),
            'lbl_public'      => $this::t('USERS_PUBLIC'),
            'lbl_url'         => Jaws::t('URL'),
            'lbl_about'       => $this::t('USERS_ABOUT'),
            'lbl_experiences' => $this::t('USERS_EXPERIENCES'),
            'lbl_occupations' => $this::t('USERS_OCCUPATIONS'),
            'lbl_interests'   => $this::t('USERS_INTERESTS'),
        );
 
        if (!$this->app->session->user->superadmin &&
            $this->app->session->user->id != $user['id'])
        {
            $user['ssn'] = Jaws::t('ERROR_ACCESS_DENIED');
        }

        // set about item data
        $tpl->SetVariablesArray($user);

        if ($user['public'] || $this->app->session->user->logged) {
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
        $tpl->SetVariable('lbl_activities', $this::t('USER_ACTIVITIES'));
        $this->Activity($tpl, $user['id'], $user['username']);
        $tpl->ParseBlock('profile/activity');

        $tpl->SetBlock('profile/attributes');
        $tpl->SetVariable('lbl_attributes', $this::t('USER_ATTRIBUTES'));
        $this->UsersAttributes($tpl, $user['id'], $user['username']);
        $tpl->ParseBlock('profile/attributes');

        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

    /**
     * Builds user's custom attributes page
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  string  XHTML template content
     */
    function UsersAttributes(&$tpl, $uid, $uname)
    {
        $gDir = ROOT_JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $hooks = glob($gDir . '*/Hooks/UsersAttributes.php');
        $gadgets = preg_replace(
            '@'.preg_quote($gDir, '@'). '(\w*)/Hooks/UsersAttributes.php@',
            '${1}',
            $hooks
        );

        foreach ($gadgets as $gadget) {
            $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('UsersAttributes');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $attrs = $objHook->Execute($uid, $uname);
            if (Jaws_Error::IsError($attrs) || empty($attrs)) {
                continue;
            }
            // fetch user custom attributes
            $result = $objHook->gadget->users->fetch($uid, array('custom' => array_keys($attrs)), 'inner');
            if (Jaws_Error::IsError($result) || empty($result)) {
                continue;
            }

            _log_var_dump($result);
        }
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
        $gDir = ROOT_JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
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
            $tpl->SetVariable('gadget', $this::t('USER_ACTIVITIES_IN_GADGET', $gInfo['title']));
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
            $tpl->SetVariable('message', $this::t('USER_ACTIVITIES_EMPTY'));
            $tpl->ParseBlock('profile/activity/no_activity');
        }
    }

}