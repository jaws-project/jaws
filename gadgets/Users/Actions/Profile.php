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
class Users_Actions_Profile extends Jaws_Gadget_Action
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
        $objDate = $GLOBALS['app']->loadDate();
        $user['dob'] = $objDate->Format($user['dob'], 'd MN Y');

        if (!empty($user['registered_date'])) {
            $user['registered_date'] = $objDate->Format($user['registered_date'], 'd MN Y');
        } else {
            $user['registered_date'] = '';
        }

        // Load the template
        $tpl = $this->gadget->loadTemplate('AboutUser.html');
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
        $objDate = $GLOBALS['app']->loadDate();
        $user['dob'] = $objDate->Format($user['dob'], 'd MN Y');

        if (!empty($user['registered_date'])) {
            $user['registered_date'] = $objDate->Format($user['registered_date'], 'd MN Y');
        } else {
            $user['registered_date'] = '';
        }

        // Load the template
        $tpl = $this->gadget->loadTemplate('Profile.html');
        $tpl->SetBlock('profile');
        $tpl->SetVariable('title',  _t('USERS_PROFILE_INFO'));
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
        $tpl = $this->gadget->loadTemplate('Profile.html');
        $tpl->SetBlock('activity');
        $tpl->SetVariable('title', _t('USERS_USER_ACTIVITY'));

        $activity = false;
        $gDir = JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $cmpModel = Jaws_Gadget::getInstance('Components')->loadModel('Gadgets');
        $gadgets  = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $gInfo) {
            if (!file_exists($gDir . $gadget. '/Hooks/Activity.php')) {
                continue;
            }

            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }
            $objHook = $objGadget->loadHook('Activity');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $activities = $objHook->Execute($uid, $uname);
            if (Jaws_Error::IsError($activities) || empty($activities)) {
                continue;
            }

            $tpl->SetBlock('activity/gadget');
            $tpl->SetVariable('gadget', _t('USERS_USER_ACTIVITY_IN_GADGET', $gInfo['title']));
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