<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Users extends Users_AdminHTML
{
    /**
     * Builds users datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function UsersDataGrid()
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $total = $uModel->GetUsersCount();

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(10);
        $datagrid->SetID('users_datagrid');
        $col = Piwi::CreateWidget('Column', _t('USERS_USERS_NICKNAME'), null, false);
        $datagrid->AddColumn($col);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'), null, false);
        $column1->SetStyle('width: 120px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 140px;');
        $datagrid->AddColumn($column2);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }

    /**
     * Prepares list of users for datagrid
     *
     * @access  public
     * @param   int    $group       User default group
     * @param   bool   $superadmin  Is created user superadmin or not
     * @param   int    $status      Status of created user
     * @param   string $term        Search term
     * @param   string $orderBy     Order by(id, username, nickname, email)
     * @param   int    $offset      Offset of data array
     * @return  array  Grid data
     */
    function GetUsers($group, $superadmin, $status, $term, $orderBy, $offset = null)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $users = $uModel->GetUsers($group, $superadmin, $status, $term, $orderBy, 10, $offset);
        if (Jaws_Error::IsError($users)) {
            return array();
        }

        $retData = array();
        foreach ($users as $user) {
            $usrData = array();
            $usrData['nickname'] = $user['nickname'];
            $usrData['username'] = $user['username'];

            $actions = '';
            if ($this->gadget->CheckPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('GLOBAL_EDIT'),
                                            "javascript: editUser(this, '".$user['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageUserACLs')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_ACLRULES'),
                                            "javascript: editUserACL(this, '".$user['id']."');",
                                            'gadgets/Users/images/acls.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_USERS_GROUPS'),
                                            "javascript: editUserGroups(this, '".$user['id']."');",
                                            'gadgets/Users/images/groups_mini.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_PERSONAL'),
                                            "javascript: editPersonal(this, '".$user['id']."');",
                                            'gadgets/Users/images/user_mini.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_PREFERENCES'),
                                            "javascript: editPreferences(this, '".$user['id']."');",
                                            STOCK_FONT);
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_CONTACTS'),
                                            "javascript: editContacts(this, '".$user['id']."');",
                                            'gadgets/Users/images/mail.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->CheckPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget('Link',
                                            _t('USERS_ACCOUNT_DELETE'),
                                            "javascript: deleteUser(this, '".$user['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }

            $usrData['actions'] = $actions;
            $retData[] = $usrData;
        }

        return $retData;
    }

    /**
     * Builds user administration UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Users()
    {
        $this->gadget->CheckPermission('ManageUsers');
        // DatePicker
        $calType = strtolower($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('calendar_language', 'Settings'));
        if ($calType != 'gregorian') {
            $GLOBALS['app']->Layout->AddScriptLink("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->AddScriptLink('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->AddScriptLink("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->AddHeadLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css',
                                             'stylesheet',
                                             'text/css');
        // xtree
        $GLOBALS['app']->Layout->AddScriptLink('libraries/xtree/xtree.js');
        // RSA encryption
        if ($this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true') {
            $GLOBALS['app']->Layout->AddScriptLink('libraries/js/rsa.lib.js');
        }

        $this->AjaxMe('script.js', $this->gadget->GetVersion());
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/Users.html');
        $tpl->SetBlock('Users');

        // Group Filter
        $filterGroup =& Piwi::CreateWidget('Combo', 'filter_group');
        $filterGroup->SetStyle('width: 150px;');
        $filterGroup->AddOption(_t('USERS_GROUPS_ALL_GROUPS'), -1, false);
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $groups = $userModel->GetGroups(null, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $filterGroup->AddOption($group['title'], $group['id']);
            }
        }
        $filterGroup->AddEvent(ON_CHANGE, "javascript: searchUser();");
        $filterGroup->SetDefault(-1);
        $tpl->SetVariable('filter_group', $filterGroup->Get());
        $tpl->SetVariable('lbl_filter_group', _t('USERS_GROUPS_GROUP'));

        // Type Filter
        $filterType =& Piwi::CreateWidget('Combo', 'filter_type');
        $filterType->SetStyle('width: 150px;');
        $filterType->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterType->AddOption(_t('USERS_USERS_TYPE_SUPERADMIN'), 1);
        $filterType->AddOption(_t('USERS_USERS_TYPE_NORMAL'),     0);
        $filterType->AddEvent(ON_CHANGE, "javascript: searchUser();");
        $filterType->SetDefault(-1);
        $tpl->SetVariable('filter_type', $filterType->Get());
        $tpl->SetVariable('lbl_filter_type', _t('USERS_USERS_TYPE'));

        // Status Filter
        $filterStatus =& Piwi::CreateWidget('Combo', 'filter_status');
        $filterStatus->SetStyle('width: 135px;');
        $filterStatus->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_0'), 0);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_1'), 1);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_2'), 2);
        $filterStatus->AddEvent(ON_CHANGE, "javascript: searchUser();");
        $filterStatus->SetDefault(-1);
        $tpl->SetVariable('filter_status', $filterStatus->Get());
        $tpl->SetVariable('lbl_filter_status', _t('GLOBAL_STATUS'));

        // Term
        $filterTerm =& Piwi::CreateWidget('Entry', 'filter_term', '');
        $filterTerm->SetStyle('width: 142px;');
        $filterTerm->SetID('filter_term');
        $filterTerm->AddEvent(ON_CHANGE, "javascript: searchUser();");
        $filterTerm->AddEvent(ON_KPRESS, "javascript: OnTermKeypress(this, event);");
        $tpl->SetVariable('lbl_filter_term', _t('USERS_USERS_SEARCH_TERM'));
        $tpl->SetVariable('filter_term', $filterTerm->Get());

        // Order types
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->SetStyle('width: 135px;');
        $orderType->AddOption(_t('USERS_USERS_REGISTRATION_DATE'). ' &darr;', '[id]');
        $orderType->AddOption(_t('USERS_USERS_REGISTRATION_DATE'). ' &uarr;', '[id] DESC');
        $orderType->AddOption(_t('USERS_USERS_USERNAME'). ' &darr;', '[username]');
        $orderType->AddOption(_t('USERS_USERS_USERNAME'). ' &uarr;', '[username] DESC');
        $orderType->AddOption(_t('USERS_USERS_NICKNAME'). ' &darr;', '[nickname]');
        $orderType->AddOption(_t('USERS_USERS_NICKNAME'). ' &uarr;', '[nickname] DESC');
        $orderType->AddEvent(ON_CHANGE, "javascript: searchUser();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', _t('USERS_USERS_ORDER_TYPE'));

        $tpl->SetVariable('menubar',        $this->MenuBar('Users'));
        $tpl->SetVariable('users_datagrid', $this->UsersDataGrid());
        $tpl->SetVariable('user_workarea',  $this->UserUI());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    _t('GLOBAL_SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "javascript: saveUser();");
        $tpl->SetVariable('save', $save->Get());

        $cancel =& Piwi::CreateWidget('Button',
                                      'cancel',
                                      _t('GLOBAL_CANCEL'),
                                      STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "javascript: stopUserAction();");
        $tpl->SetVariable('cancel', $cancel->Get());

        $tpl->SetVariable('addUser_title',         _t('USERS_USERS_ADD'));
        $tpl->SetVariable('editUser_title',        _t('USERS_USERS_EDIT'));
        $tpl->SetVariable('editUserACL_title',     _t('USERS_ACLRULES'));
        $tpl->SetVariable('editUserGroups_title',  _t('USERS_USERS_GROUPS'));
        $tpl->SetVariable('editPersonal_title',    _t('USERS_PERSONAL'));
        $tpl->SetVariable('editPreferences_title', _t('USERS_PREFERENCES'));
        $tpl->SetVariable('editContacts_title',    _t('USERS_CONTACTS'));
        $tpl->SetVariable('noGroup', _t('USERS_GROUPS_NOGROUP'));
        $tpl->SetVariable('wrongPassword', _t('USERS_USERS_PASSWORDS_DONT_MATCH'));
        $tpl->SetVariable('incompleteUserFields', _t('USERS_USERS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('permissionsMsg', _t('USERS_USERS_PERMISSIONS'));
        $tpl->SetVariable('selectUser', _t('USERS_USERS_SELECT_A_USER'));
        $tpl->SetVariable('confirmUserDelete', _t('USERS_USER_CONFIRM_DELETE'));
        $tpl->SetVariable('confirmResetACL', _t('USERS_RESET_ACL_CONFIRM'));
        $tpl->SetVariable('permissionAllow', _t('USERS_USERS_PERMISSION_ALLOW'));
        $tpl->SetVariable('permissionDeny',  _t('USERS_USERS_PERMISSION_DENY'));
        $tpl->SetVariable('permissionNone',  _t('USERS_USERS_PERMISSION_NONE'));
        $tpl->ParseBlock('Users');

        return $tpl->Get();
    }

    /**
     * Builds a form to edit user data
     *
     * @access  public
     * @return  string  XHTML form
     */
    function UserUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/User.html');
        $tpl->SetBlock('user');

        $use_crypt = $this->gadget->registry->fetch('crypt_enabled', 'Policy') == 'true';
        if ($use_crypt) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt = new Jaws_Crypt();
            $use_crypt = $JCrypt->Init();
        }

        if ($use_crypt) {
            $tpl->SetBlock('user/encryption');
            // modulus
            $modulus =& Piwi::CreateWidget('HiddenEntry',
                                           'modulus',
                                           $JCrypt->math->bin2int($JCrypt->pub_key->getModulus()));
            $modulus->SetID('modulus');
            $tpl->SetVariable('modulus', $modulus->Get());
            //exponent
            $exponent =& Piwi::CreateWidget('HiddenEntry',
                                            'exponent',
                                            $JCrypt->math->bin2int($JCrypt->pub_key->getExponent()));
            $modulus->SetID('exponent');
            $tpl->SetVariable('exponent', $exponent->Get());
            $tpl->ParseBlock('user/encryption');
        }

        // username
        $username =& Piwi::CreateWidget('Entry', 'username');
        $username->SetID('username');
        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $username->Get());

        // nickname
        $nickname =& Piwi::CreateWidget('Entry', 'nickname');
        $nickname->SetID('nickname');
        $tpl->SetVariable('lbl_nickname', _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('nickname', $nickname->Get());

        // email
        $email =& Piwi::CreateWidget('Entry', 'email');
        $email->SetID('email');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $email->Get());

        // superadmin
        $superadmin =& Piwi::CreateWidget('Combo', 'superadmin');
        $superadmin->SetID('superadmin');
        $superadmin->AddOption(_t('GLOBAL_NO'),  0);
        $superadmin->AddOption(_t('GLOBAL_YES'), 1);
        $superadmin->SetDefault(0);
        $tpl->SetVariable('lbl_superadmin', _t('USERS_USERS_TYPE_SUPERADMIN'));
        $tpl->SetVariable('superadmin', $superadmin->Get());

        // pass1
        $pass1 =& Piwi::CreateWidget('PasswordEntry', 'pass1');
        $pass1->SetID('pass1');
        $tpl->SetVariable('lbl_pass1', _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('pass1', $pass1->Get());

        // pass2
        $pass2 =& Piwi::CreateWidget('PasswordEntry', 'pass2');
        $pass2->SetID('pass2');
        $tpl->SetVariable('lbl_pass2', _t('USERS_USERS_PASSWORD_VERIFY'));
        $tpl->SetVariable('pass2', $pass2->Get());

        // concurrent logins
        $concurrents =& Piwi::CreateWidget('Entry', 'concurrents', '0');
        $concurrents->SetID('concurrents');
        $tpl->SetVariable('lbl_concurrents', _t('USERS_USERS_CONCURRENTS'));
        $tpl->SetVariable('concurrents', $concurrents->Get());

        // expiry date
        $dExpiry =& Piwi::CreateWidget('DatePicker', 'expiry_date', '');
        $dExpiry->SetId('expiry_date');
        $dExpiry->showTimePicker(true);
        $dExpiry->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $dExpiry->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $dExpiry->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_expiry_date', _t('USERS_USERS_EXPIRY_DATE'));
        $tpl->SetVariable('expiry_date', $dExpiry->Get());

        // status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->SetID('status');
        $status->AddOption(_t('USERS_USERS_STATUS_0'), 0);
        $status->AddOption(_t('USERS_USERS_STATUS_1'), 1);
        $status->AddOption(_t('USERS_USERS_STATUS_2'), 2);
        $status->SetDefault(1);
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        $tpl->ParseBlock('user');
        return $tpl->Get();
    }

    /**
     * Builds the user-groups UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function UserGroupsUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/UserGroups.html');
        $tpl->SetBlock('user_groups');
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();

        $user_groups =& Piwi::CreateWidget('CheckButtons', 'user_groups');
        $user_groups->setColumns(1);
        $groups = $uModel->GetGroups();
        foreach ($groups as $group) {
            $user_groups->AddOption($group['title']. ' ('. $group['name']. ')',
                                    $group['id'],
                                    'group_'. $group['id']);
        }
        $tpl->SetVariable('lbl_user_groups', _t('USERS_USERS_MARK_GROUPS'));
        $tpl->SetVariable('user_groups', $user_groups->Get());
        $tpl->ParseBlock('user_groups');
        return $tpl->Get();
    }

    /**
     * Builds a form to edit user's personal information
     *
     * @access  public
     * @return  string  XHTML form
     */
    function PersonalUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/Personal.html');
        $tpl->SetBlock('personal');

        // privacy
        $privacy =& Piwi::CreateWidget('Combo', 'privacy');
        $privacy->SetID('privacy');
        $privacy->AddOption(_t('GLOBAL_DISABLED'), 0);
        $privacy->AddOption(_t('GLOBAL_ENABLED'),  1);
        $privacy->SetDefault(1);
        $tpl->SetVariable('lbl_privacy', _t('USERS_USERS_PRIVACY'));
        $tpl->SetVariable('privacy', $privacy->Get());

        // avatar
        $entry =& Piwi::CreateWidget('FileEntry', 'upload_avatar', '');
        $entry->SetID('upload_avatar');
        $entry->SetSize(1);
        $entry->SetStyle('width:110px; padding:0;');
        $entry->AddEvent(ON_CHANGE, 'upload();');
        $tpl->SetVariable('upload_avatar', $entry->Get());

        // upload avatar button
        $button =& Piwi::CreateWidget('Button', 'btn_upload', '', STOCK_ADD);
        $tpl->SetVariable('btn_upload', $button->Get());

        // remove avatar button
        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'removeAvatar()');
        $tpl->SetVariable('btn_remove', $button->Get());

        // first name
        $fname =& Piwi::CreateWidget('Entry', 'fname');
        $fname->SetID('fname');
        $tpl->SetVariable('lbl_fname', _t('USERS_USERS_FIRSTNAME'));
        $tpl->SetVariable('fname', $fname->Get());

        // last name
        $lname =& Piwi::CreateWidget('Entry', 'lname');
        $lname->SetID('lname');
        $tpl->SetVariable('lbl_lname', _t('USERS_USERS_LASTNAME'));
        $tpl->SetVariable('lname', $lname->Get());

        // gender
        $gender =& Piwi::CreateWidget('Combo', 'gender');
        $gender->SetID('gender');
        $gender->AddOption(_t('USERS_USERS_GENDER_0'), 0);
        $gender->AddOption(_t('USERS_USERS_GENDER_1'), 1);
        $gender->AddOption(_t('USERS_USERS_GENDER_2'), 2);
        $gender->SetDefault(0);
        $tpl->SetVariable('lbl_gender', _t('USERS_USERS_GENDER'));
        $tpl->SetVariable('gender', $gender->Get());

        // dob
        $dob =& Piwi::CreateWidget('DatePicker', 'dob', '');
        $dob->SetId('dob');
        $dob->showTimePicker(true);
        $dob->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $dob->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $dob->setDateFormat('%Y-%m-%d');
        $tpl->SetVariable('lbl_dob', _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('dob', $dob->Get());

        // url
        $url =& Piwi::CreateWidget('Entry', 'url');
        $url->SetID('url');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $url->Get());

        // about
        $about =& Piwi::CreateWidget('TextArea', 'about');
        $about->SetID('about');
        $about->SetRows(7);
        $about->SetColumns(34);
        $tpl->SetVariable('lbl_about', _t('USERS_USERS_ABOUT'));
        $tpl->SetVariable('about', $about->Get());

        $tpl->ParseBlock('personal');
        return $tpl->Get();
    }

    /**
     * Builds a form to edit user's preferences
     *
     * @access  public
     * @return  string  XHTML form
     */
    function PreferencesUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/Preferences.html');
        $tpl->SetBlock('preferences');

        // language
        $language =& Piwi::CreateWidget('Combo', 'language');
        $language->SetID('language');
        $language->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), '-default-');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach($languages as $k => $v) {
            $language->AddOption($v, $k);
        }
        $language->SetDefault('-default-');
        $tpl->SetVariable('lbl_language', _t('USERS_ADVANCED_OPTS_LANGUAGE'));
        $tpl->SetVariable('language', $language->Get());

        // theme
        $theme =& Piwi::CreateWidget('ComboGroup', 'theme');
        $theme->SetID('theme');
        $theme->AddGroup('local',  _t('LAYOUT_THEME_LOCAL'));
        $theme->AddGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $theme->AddOption('local', _t('USERS_ADVANCED_OPTS_NOT_YET'), '-default-');
        $themes = Jaws_Utils::GetThemesList();
        foreach($themes as $k => $v) {
            $theme->AddOption($v['local']? 'local' : 'remote', $v['name'], $k);
        }
        $theme->SetDefault('-default-');
        $tpl->SetVariable('lbl_theme', _t('USERS_ADVANCED_OPTS_THEME'));
        $tpl->SetVariable('theme', $theme->Get());

        // editor
        $editor =& Piwi::CreateWidget('Combo', 'editor');
        $editor->SetID('editor');
        $editor->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), '-default-');
        $settingsModel = $GLOBALS['app']->loadGadget('Settings', 'AdminModel');
        $editors = $settingsModel->GetEditorList();
        foreach($editors as $k => $v) {
            $editor->AddOption($v, $k);
        }
        $editor->SetDefault('-default-');
        $tpl->SetVariable('lbl_editor', _t('USERS_ADVANCED_OPTS_EDITOR'));
        $tpl->SetVariable('editor', $editor->Get());

        // timezone
        $timezone =& Piwi::CreateWidget('Combo', 'timezone');
        $timezone->SetID('timezone');
        $timezone->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), '-default-');
        $settingsModel = $GLOBALS['app']->loadGadget('Settings', 'AdminModel');
        $timezones = $settingsModel->GetTimeZonesList();
        foreach($timezones as $k => $v) {
            $timezone->AddOption($v, $k);
        }
        $timezone->SetDefault('-default-');
        $tpl->SetVariable('lbl_timezone', _t('GLOBAL_TIMEZONE'));
        $tpl->SetVariable('timezone', $timezone->Get());

        $tpl->ParseBlock('preferences');
        return $tpl->Get();
    }

    /**
     * Builds a form to edit user's contacts
     *
     * @access  public
     * @return  string  XHTML form
     */
    function ContactsUI()
    {
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/Contacts.html');
        $tpl->SetBlock('contacts');

        // country
        $country =& Piwi::CreateWidget('Entry', 'country', '');
        $country->SetStyle('width: 142px;');
        $country->SetID('country');
        $tpl->SetVariable('lbl_country', _t('USERS_CONTACTS_COUNTRY'));
        $tpl->SetVariable('country', $country->Get());

        // city
        $city =& Piwi::CreateWidget('Entry', 'city', '');
        $city->SetStyle('width: 142px;');
        $city->SetID('city');
        $tpl->SetVariable('lbl_city', _t('USERS_CONTACTS_CITY'));
        $tpl->SetVariable('city', $city->Get());

        // address
        $address =& Piwi::CreateWidget('TextArea', 'address', '');
        $address->SetID('address');
        $address->SetRows(4);
        $address->SetColumns(34);
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('address', $address->Get());

        // postal_code
        $postalCode =& Piwi::CreateWidget('Entry', 'postal_code', '');
        $postalCode->SetStyle('width: 142px;');
        $postalCode->SetID('postal_code');
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('postal_code', $postalCode->Get());

        // phone_number
        $phoneNumber =& Piwi::CreateWidget('Entry', 'phone_number', '');
        $phoneNumber->SetStyle('width: 142px;');
        $phoneNumber->SetID('phone_number');
        $tpl->SetVariable('lbl_phone_number', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('phone_number', $phoneNumber->Get());

        // mobile_number
        $mobileNumber =& Piwi::CreateWidget('Entry', 'mobile_number', '');
        $mobileNumber->SetStyle('width: 142px;');
        $mobileNumber->SetID('mobile_number');
        $tpl->SetVariable('lbl_mobile_number', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile_number', $mobileNumber->Get());

        // fax_number
        $faxNumber =& Piwi::CreateWidget('Entry', 'fax_number', '');
        $faxNumber->SetStyle('width: 142px;');
        $faxNumber->SetID('fax_number');
        $tpl->SetVariable('lbl_fax_number', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('fax_number', $faxNumber->Get());

        $tpl->ParseBlock('contacts');
        return $tpl->Get();
    }

}