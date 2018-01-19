<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Users extends Users_Actions_Admin_Default
{
    /**
     * Builds users datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function UsersDataGrid()
    {
        $uModel = new Jaws_User();
        $total = $uModel->GetUsersCount();

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(10);
        $datagrid->SetID('users_datagrid');
        $col = Piwi::CreateWidget('Column', _t('USERS_USERS_NICKNAME'), null, false);
        $datagrid->AddColumn($col);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'), null, false);
        $column1->SetStyle('width: 180px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 160px;');
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
            if ($this->gadget->GetPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('GLOBAL_EDIT'),
                    "javascript:Jaws_Gadget.getInstance('Users').editUser(this, '".$user['id']."');",
                    STOCK_EDIT
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageUserACLs')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_ACLS'),
                    "javascript:Jaws_Gadget.getInstance('Users').editACL(this, '".$user['id']."', 'UserACL');",
                    'gadgets/Users/Resources/images/acls.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_USERS_GROUPS'),
                    "javascript:Jaws_Gadget.getInstance('Users').editUserGroups(this, '".$user['id']."');",
                    'gadgets/Users/Resources/images/groups_mini.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_PERSONAL'),
                    "javascript:Jaws_Gadget.getInstance('Users').editPersonal(this, '".$user['id']."');",
                    'gadgets/Users/Resources/images/user_mini.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_CONTACTS'),
                    "javascript:Jaws_Gadget.getInstance('Users').editContacts(this, '".$user['id']."');",
                    'gadgets/Users/Resources/images/mail.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_EXTRA'),
                    "javascript:Jaws_Gadget.getInstance('Users').editExtra(this, '".$user['id']."');",
                    'gadgets/Users/Resources/images/extra.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageUsers')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_ACCOUNT_DELETE'),
                    "javascript:Jaws_Gadget.getInstance('Users').deleteUser(this, '".$user['id']."');",
                    STOCK_DELETE
                );
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
        $this->AjaxMe('script.js');
        // DatePicker
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        // set default value of javascript variables
        $this->gadget->define('addUser_title', _t('USERS_USERS_ADD'));
        $this->gadget->define('editUser_title', _t('USERS_USERS_EDIT'));
        $this->gadget->define('editACL_title', _t('USERS_ACLS'));
        $this->gadget->define('editUserGroups_title', _t('USERS_USERS_GROUPS'));
        $this->gadget->define('editPersonal_title', _t('USERS_PERSONAL'));
        $this->gadget->define('editContacts_title', _t('USERS_CONTACTS'));
        $this->gadget->define('editExtra_title', _t('USERS_EXTRA'));
        $this->gadget->define('noGroup', _t('USERS_GROUPS_NOGROUP'));
        $this->gadget->define('confirmUserDelete', _t('USERS_USER_CONFIRM_DELETE'));
        $this->gadget->define('wrongPassword', _t('USERS_USERS_PASSWORDS_DONT_MATCH'));
        $this->gadget->define('incompleteUserFields', _t('USERS_USERS_INCOMPLETE_FIELDS'));

        $tpl = $this->gadget->template->loadAdmin('Users.html');
        $tpl->SetBlock('Users');

        // RSA encryption
        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock('Users/encryption');
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock('Users/encryption');
        }

        // Group Filter
        $filterGroup =& Piwi::CreateWidget('Combo', 'filter_group');
        $filterGroup->AddOption(_t('USERS_GROUPS_ALL_GROUPS'), -1, false);
        $userModel = new Jaws_User();
        $groups = $userModel->GetGroups(null, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $filterGroup->AddOption($group['title'], $group['id']);
            }
        }
        $filterGroup->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchUser();");
        $filterGroup->SetDefault(-1);
        $tpl->SetVariable('filter_group', $filterGroup->Get());
        $tpl->SetVariable('lbl_filter_group', _t('USERS_GROUPS_GROUP'));

        // Type Filter
        $filterType =& Piwi::CreateWidget('Combo', 'filter_type');
        $filterType->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterType->AddOption(_t('USERS_USERS_TYPE_SUPERADMIN'), 1);
        $filterType->AddOption(_t('USERS_USERS_TYPE_NORMAL'),     0);
        $filterType->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchUser();");
        $filterType->SetDefault(-1);
        $tpl->SetVariable('filter_type', $filterType->Get());
        $tpl->SetVariable('lbl_filter_type', _t('USERS_USERS_TYPE'));

        // Status Filter
        $filterStatus =& Piwi::CreateWidget('Combo', 'filter_status');
        $filterStatus->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_0'), 0);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_1'), 1);
        $filterStatus->AddOption(_t('USERS_USERS_STATUS_2'), 2);
        $filterStatus->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchUser();");
        $filterStatus->SetDefault(-1);
        $tpl->SetVariable('filter_status', $filterStatus->Get());
        $tpl->SetVariable('lbl_filter_status', _t('GLOBAL_STATUS'));

        // Term
        $filterTerm =& Piwi::CreateWidget('Entry', 'filter_term', '');
        $filterTerm->SetID('filter_term');
        $filterTerm->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchUser();");
        $filterTerm->AddEvent(ON_KPRESS, "Jaws_Gadget.getInstance('Users').OnTermKeypress(this, event);");
        $tpl->SetVariable('lbl_filter_term', _t('USERS_USERS_SEARCH_TERM'));
        $tpl->SetVariable('filter_term', $filterTerm->Get());

        // Order types
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('USERS_USERS_REGISTRATION_DATE'). ' &darr;', 'id');
        $orderType->AddOption(_t('USERS_USERS_REGISTRATION_DATE'). ' &uarr;', 'id desc');
        $orderType->AddOption(_t('USERS_USERS_USERNAME'). ' &darr;', 'username');
        $orderType->AddOption(_t('USERS_USERS_USERNAME'). ' &uarr;', 'username desc');
        $orderType->AddOption(_t('USERS_USERS_NICKNAME'). ' &darr;', 'nickname');
        $orderType->AddOption(_t('USERS_USERS_NICKNAME'). ' &uarr;', 'nickname desc');
        $orderType->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchUser();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', _t('USERS_USERS_ORDER_TYPE'));

        $tpl->SetVariable('menubar',        $this->MenuBar('Users'));
        $tpl->SetVariable('users_datagrid', $this->UsersDataGrid());
        $tpl->SetVariable('workarea',  $this->UserUI());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    _t('GLOBAL_SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').saveUser();");
        $tpl->SetVariable('save', $save->Get());

        $cancel =& Piwi::CreateWidget('Button',
                                      'cancel',
                                      _t('GLOBAL_CANCEL'),
                                      STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').stopUserAction();");
        $tpl->SetVariable('cancel', $cancel->Get());
        $tpl->SetVariable('selectUser', _t('USERS_USERS_SELECT_A_USER'));
        $tpl->SetVariable('confirmResetACL', _t('USERS_RESET_ACL_CONFIRM'));
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
        $tpl = $this->gadget->template->loadAdmin('User.html');
        $tpl->SetBlock('user');

        // domains
        if ($this->gadget->registry->fetch('multi_domain') == 'true') {
            $domains = $this->gadget->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                array_unshift($domains, array('id' => 0, 'title' => _t('USERS_NODOMAIN')));
                $domainCombo =& Piwi::CreateWidget('Combo', 'domain');
                $tpl->SetBlock('user/domain');
                foreach ($domains as $domain) {
                    $domainCombo->AddOption($domain['title'], $domain['id']);
                }
                $domainCombo->SetDefault(0);
                $tpl->SetVariable('domain', $domainCombo->Get());
                $tpl->SetVariable('lbl_domain', _t('USERS_DOMAIN'));
                $tpl->ParseBlock('user/domain');
            }
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

        // mobile
        $mobile =& Piwi::CreateWidget('Entry', 'mobile');
        $mobile->SetID('mobile');
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile', $mobile->Get());

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
        $pass1->setAutoComplete(false);
        $tpl->SetVariable('lbl_pass1', _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('pass1', $pass1->Get());

        // pass2
        $pass2 =& Piwi::CreateWidget('PasswordEntry', 'pass2');
        $pass2->SetID('pass2');
        $pass2->setAutoComplete(false);
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
        $dExpiry->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $dExpiry->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $dExpiry->setDateFormat('%Y/%m/%d');
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
        $tpl = $this->gadget->template->loadAdmin('UserGroups.html');
        $tpl->SetBlock('user_groups');
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
        $tpl = $this->gadget->template->loadAdmin('Personal.html');
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
        $entry->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').upload();");
        $tpl->SetVariable('upload_avatar', $entry->Get());

        // upload avatar button
        $button =& Piwi::CreateWidget('Button', 'btn_upload', '', STOCK_ADD);
        $tpl->SetVariable('btn_upload', $button->Get());

        // remove avatar button
        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').removeAvatar();");
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

        // social security number
        $ssn =& Piwi::CreateWidget('Entry', 'ssn');
        $ssn->SetID('ssn');
        $tpl->SetVariable('lbl_ssn', _t('USERS_USERS_SSN'));
        $tpl->SetVariable('ssn', $ssn->Get());

        // dob
        $dob =& Piwi::CreateWidget('DatePicker', 'dob', '');
        $dob->SetId('dob');
        $dob->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $dob->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
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
        $about->SetRows(4);
        $about->SetColumns(34);
        $tpl->SetVariable('lbl_about', _t('USERS_USERS_ABOUT'));
        $tpl->SetVariable('about', $about->Get());

        $tpl->ParseBlock('personal');
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
        $tpl = $this->gadget->template->loadAdmin('Contacts.html');
        $tpl->SetBlock('contacts');

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_home', _t('USERS_CONTACTS_HOME'));
        $tpl->SetVariable('lbl_work', _t('USERS_CONTACTS_WORK'));
        $tpl->SetVariable('lbl_other', _t('USERS_CONTACTS_OTHER'));
        $tpl->SetVariable('lbl_tel', _t('USERS_CONTACTS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_fax', _t('USERS_CONTACTS_FAX_NUMBER'));
        $tpl->SetVariable('lbl_mobile', _t('USERS_CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_province', _t('GLOBAL_PROVINCE'));
        $tpl->SetVariable('lbl_city', _t('GLOBAL_CITY'));
        $tpl->SetVariable('lbl_address', _t('USERS_CONTACTS_ADDRESS'));
        $tpl->SetVariable('lbl_postal_code', _t('USERS_CONTACTS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_note', _t('USERS_CONTACTS_NOTE'));

        // province
        $zModel = Jaws_Gadget::getInstance('Settings')->model->load('Zones');
        $provinces = $zModel->GetProvinces(364);
        if (!Jaws_Error::IsError($provinces) && count($provinces) > 0) {
            array_unshift($provinces, array('province' => 0, 'title' => ''));
            foreach ($provinces as $province) {
                $tpl->SetBlock('contacts/province_home');
                $tpl->SetVariable('value', $province['province']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_home']) && $contacts['province_home'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_home');

                $tpl->SetBlock('contacts/province_work');
                $tpl->SetVariable('value', $province['province']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_work']) && $contacts['province_work'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_work');

                $tpl->SetBlock('contacts/province_other');
                $tpl->SetVariable('value', $province['province']);
                $tpl->SetVariable('title', $province['title']);
                $tpl->SetVariable('selected', '');
                if (isset($contacts['province_other']) && $contacts['province_other'] == $province['id']) {
                    $tpl->SetVariable('selected', 'selected');
                }
                $tpl->ParseBlock('contacts/province_other');
            }
        }

        $tpl->ParseBlock('contacts');
        return $tpl->Get();
    }

    /**
     * Builds a form to edit user's extra
     *
     * @access  public
     * @return  string  XHTML form
     */
    function ExtraUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Extra.html');
        $tpl->SetBlock('extra');

        $tpl->SetVariable('lbl_mailquota', _t('USERS_EXTRA_MAILQUOTA'));
        $tpl->SetVariable('lbl_ftpquota', _t('USERS_EXTRA_FTPQUOTA'));

        $tpl->ParseBlock('extra');
        return $tpl->Get();
    }

    /**
     * Logout user
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        $GLOBALS['app']->Session->Logout();
        $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
        return Jaws_Header::Location($admin_script?: 'admin.php');
    }

}