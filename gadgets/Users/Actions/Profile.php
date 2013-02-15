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

        $tpl->SetBlock('profile/activity');
        $userActivities = $this->GetUserActivity($user['id']);
        $tpl->SetVariable('title', _t('USERS_USER_ACTIVITY'));
        if (is_array($userActivities) && count($userActivities) > 0) {
            foreach ($userActivities as $gadget => $activities) {

                $tpl->SetBlock('profile/activity/subtitle');
                $tpl->SetVariable('text', 'xxxxxxxx');
                $tpl->ParseBlock('profile/activity/subtitle');

                $tpl->SetBlock('profile/activity/gadget');
                $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                $tpl->SetVariable('gadget', _t('USERS_USER_ACTIVITY_IN_GADGET', $info->GetTitle()));
                $tpl->ParseBlock('profile/activity/gadget');

                foreach ($activities as $activity) {
                    $tpl->SetBlock('profile/activity/item');
                    $tpl->SetVariable('title', _t('USERS_USER_ACTIVITY_ITEM', $activity['count'], $activity['title']));
                    $tpl->SetVariable('url', $activity['url']);
                    $tpl->ParseBlock('profile/activity/item');
                }

            }
        } else {
            $tpl->SetBlock('profile/activity/notfound');
            $tpl->SetVariable('message', _t('USERS_USER_NOT_HAVE_ACTIVITY'));
            $tpl->ParseBlock('profile/activity/notfound');
        }
        $tpl->ParseBlock('profile/activity');


        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

    /**
     * Returns the user activity results
     *
     * @access  public
     * @param   int     $uid    User id
     * @return  array   User activity results
     */
    function GetUserActivity($uid)
    {
        $result = array();

        $gadgetList = $this->GetSearchableGadgets();
        $gadgets = array_keys($gadgetList);
        foreach ($gadgets as $gadget) {
            $gHook = $GLOBALS['app']->LoadHook($gadget, 'UserActivity');
            if ($gHook === false) {
                continue;
            }

            $result[$gadget] = array();
            $gResult = $gHook->Hook($uid);

            if (!Jaws_Error::IsError($gResult) || !$gResult) {
                if (is_array($gResult) && !empty($gResult)) {
                    $result[$gadget] = $gResult;
                } else {
                    unset($result[$gadget]);
                }
            }

        }

        return $result;
    }

    /**
     * Gets searchable gadgets
     *
     * @access  public
     * @return  array   List of searchable gadgets
     */
    function GetSearchableGadgets()
    {
        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadgetList = $jms->GetGadgetsList(false, true, true);
        $gadgets = array();
        foreach ($gadgetList as $key => $gadget) {
            if (is_file(JAWS_PATH . 'gadgets/' . $gadget['realname'] . '/hooks/UserActivity.php'))
                $gadgets[$key] = $gadget;
        }
        return $gadgets;
    }


}