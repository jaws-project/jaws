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
        $users = $this->gadget->model->load('Users')->getUsers(
            0, 0,
            array(
                'superadmin' => true
            )
        );
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

        $user = $this->gadget->model->load('User')->getUser(
            $user,
            0,
            array('default' => true, 'account' => true, 'personal' => true)
        );
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

        $personal = $this->gadget->model->load('User')->getUser(
            $user,
            0,
            array('default' => true, 'account' => true, 'personal' => true)
        );
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        // Avatar
        $user['avatar'] = $this->gadget->urlMap('Avatar', array('user'  => $user['username']));
        /*
        $user['avatar'] = $this->app->users->GetAvatar(
            $user['avatar'],
            $user['email'],
            128,
            $user['last_update']
        );
        */

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
            'lbl_account'     => $this::t('USERS_ACCOUNT'),
            'lbl_private'     => $this::t('USERS_PRIVATE'),
            'lbl_email'       => Jaws::t('EMAIL'),
            'lbl_mobile'      => $this::t('CONTACTS_MOBILE_NUMBER'),
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
        $parsed = $this->Activity($tpl, $user['id'], $user['username']);
        $tpl->ParseBlock('profile/activity', !$parsed);

        if ($this->gadget->GetPermission('AccessUserAttributes')) {
            $tpl->SetBlock('profile/attributes');
            $tpl->SetVariable('lbl_attributes', $this::t('USER_ATTRIBUTES'));
            $this->UserAttributes($tpl, $user['id'], $user['username']);
            $tpl->ParseBlock('profile/attributes');
        }

        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

    /**
     * User's avatar image
     *
     * @access  public
     * @return  mixed   Avatar image content on successful, False otherwise
     */
    function Avatar()
    {
        $user = $this->gadget->request->fetch('user', 'get');
        $user = $user?: $this->app->session->user->username;
        if (empty($user)) {
            return false;
        }

        if ($this->app->session->user->username != $user &&
            !$this->gadget->GetPermission('AccessUsersProfile')
        ) {
            return false;
        }

        $image = $this->gadget->model->load('User')->getAvatar($user);
        if (!Jaws_Error::IsError($image)) {
            $objImage = Jaws_Image::factory();
            if (!Jaws_Error::IsError($objImage)) {
                $objImage->setData($image, true);
                $res = $objImage->display('', null, 2592000);// cached for a month
                if (!Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        return false;
    }

    /**
     * Builds user's custom attributes page
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  string  XHTML template content
     */
    function UserAttributes(&$tpl, $uid, $uname)
    {
        $gDir = ROOT_JAWS_PATH. 'gadgets/';
        $hooks = glob($gDir . '*/Hooks/UserAttributes.php');
        $gadgets = preg_replace(
            '@'.preg_quote($gDir, '@'). '(\w*)/Hooks/UserAttributes.php@',
            '${1}',
            $hooks
        );

        foreach ($gadgets as $gadget) {
            $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('UserAttributes');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $attrs = $objHook->Execute();
            if (Jaws_Error::IsError($attrs) || empty($attrs)) {
                continue;
            }

            // check access to this gadget custom attributes
            if (!$objHook->gadget->GetPermission('AccessUserAttributes')) {
                continue;
            }

            // fetch user custom attributes
            $attrValues = $objHook->gadget->users->fetch($uid, array('custom' => array_keys($attrs)), 'inner');
            if (Jaws_Error::IsError($attrValues) || empty($attrValues)) {
                continue;
            }

            $tpl->SetBlock('profile/attributes/gadget');
            $tpl->SetVariable('gadget', $gadget);
            $tpl->SetVariable('lbl_gadget', Jaws_Gadget::t("$gadget.TITLE"));
            foreach ($attrs as $attrName => $attrOptions) {
                // set default value
                $defaultValue = isset($attrOptions['value'])? $attrOptions['value'] : null;
                if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                    $defaultValue = $attrValues[$attrName];
                }

                switch ($attrOptions['type']) {
                    case 'select':
                        $defaultValue = $attrOptions['values'][$defaultValue];
                        break;

                    case 'user':
                        break;

                    case 'checkbox':
                        $defaultValue = !empty($defaultValue)? Jaws::t('YES') : Jaws::t('NO');
                        break;

                    case 'date':
                        $defaultValue = Jaws_Date::getInstance()->Format($defaultValue, 'Y/m/d');
                        break;

                    case 'country':
                        try {
                            $this->selectedCountry = $defaultValue;

                            $country = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCountry(
                                $defaultValue
                            );
                            if (Jaws_Error::IsError($country) || empty($country)) {
                                throw new Exception('');
                            }
                            $defaultValue = $country['title'];

                        } catch (Exception $error) {
                            // don nothing
                        }
                        break;

                    case 'province':
                        try {
                            $this->selectedProvince = $defaultValue;
                            if (empty($this->selectedCountry)) {
                                throw new Exception('');
                            }

                            $province = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetProvince(
                                $defaultValue
                            );
                            if (Jaws_Error::IsError($province) || empty($province)) {
                                throw new Exception('');
                            }
                            $defaultValue = $province['title'];

                        } catch (Exception $error) {
                            // don nothing
                        }
                        break;

                    case 'city':
                        try {
                            if (empty($this->selectedCountry) || empty($this->selectedProvince)) {
                                throw new Exception('');
                            }

                            $city = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCity(
                                $defaultValue
                            );
                            if (Jaws_Error::IsError($city) || empty($city)) {
                                throw new Exception('');
                            }
                            $defaultValue = $city['title'];

                        } catch (Exception $error) {
                            // don nothing
                        }
                        break;

                    default:
                        // do nothing
                }

                if (isset($attrOptions['hidden']) && $attrOptions['hidden']) {
                    continue;
                }

                $tpl->SetBlock('profile/attributes/gadget/item');
                $tpl->SetVariable('title', $attrOptions['title']);
                $tpl->SetVariable('value',  $defaultValue);
                $tpl->ParseBlock('profile/attributes/gadget/item');
            }
            $tpl->ParseBlock('profile/attributes/gadget');
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
        $parsed = false;
        $gDir = ROOT_JAWS_PATH. 'gadgets/';
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
            $parsed = true;
            $tpl->ParseBlock('profile/activity/gadget');
        }

        return $parsed;
    }

}