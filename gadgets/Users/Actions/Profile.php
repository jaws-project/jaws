<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Profile extends UsersHTML
{
    /**
     * Builds a simple form to update user profile (fname, lname, gender, ...)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Profile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor(
                                                'Users',
                                                'LoginBox',
                                                array('referrer'  => Jaws_Utils::getRequestURL(false))), true);
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditAccountProfile');
        $profile = $GLOBALS['app']->Session->PopSimpleResponse('Users.Profile.Data');
        if (empty($profile)) {
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $jUser = new Jaws_User;
            $profile  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Profile.html');
        $tpl->SetBlock('profile');
        $tpl->SetVariable('title', _t('USERS_PERSONAL_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        $tpl->SetVariable('lbl_fname',         _t('USERS_USERS_FIRSTNAME'));
        $tpl->SetVariable('fname',             $xss->filter($profile['fname']));
        $tpl->SetVariable('lbl_lname',         _t('USERS_USERS_LASTNAME'));
        $tpl->SetVariable('lname',             $xss->filter($profile['lname']));
        $tpl->SetVariable('lbl_gender',        _t('USERS_USERS_GENDER'));
        $tpl->SetVariable('gender_male',       _t('USERS_USERS_MALE'));
        $tpl->SetVariable('gender_female',     _t('USERS_USERS_FEMALE'));
        if (empty($profile['gender'])) {
            $tpl->SetVariable('male_selected',   'selected="selected"');
        } else {
            $tpl->SetVariable('female_selected', 'selected="selected"');
        }

        if (empty($profile['dob'])) {
            $dob = array('', '', '');
        } else {
            $date = $GLOBALS['app']->loadDate();
            $dob = $date->Format($profile['dob'], 'Y-m-d');
            $dob = explode('-', $dob);
        }

        $tpl->SetVariable('lbl_dob',    _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('dob_year',   $dob[0]);
        $tpl->SetVariable('dob_month',  $dob[1]);
        $tpl->SetVariable('dob_day',    $dob[2]);
        $tpl->SetVariable('dob_sample', _t('USERS_USERS_BIRTHDAY_SAMPLE'));

        $tpl->SetVariable('lbl_url',           _t('GLOBAL_URL'));
        $tpl->SetVariable('url',               $xss->filter($profile['url']));

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.Profile.Response')) {
            $tpl->SetBlock('profile/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('profile/response');
        }
        $tpl->ParseBlock('profile');
        return $tpl->Get();
    }

    /**
     * Updates user profile
     *
     * @access  public
     * @return  void
     */
    function UpdateProfile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor(
                                                'Users',
                                                'LoginBox',
                                                array('referrer'  => Jaws_Utils::getRequestURL(false))), true);
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditAccountProfile');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('fname', 'lname', 'gender', 'dob_year', 'dob_month', 'dob_day', 'url'), 'post');

        $post['dob'] = null;
        if (!empty($post['dob_year']) && !empty($post['dob_year']) && !empty($post['dob_year'])) {
            $date = $GLOBALS['app']->loadDate();
            $dob  = $date->ToBaseDate($post['dob_year'], $post['dob_month'], $post['dob_day']);
            $post['dob'] = date('Y-m-d H:i:s', $dob['timestamp']);
        }

        $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Personal');
        $result = $model->UpdatePersonal($GLOBALS['app']->Session->GetAttribute('user'),
                                         $post['fname'],
                                         $post['lname'],
                                         $post['gender'],
                                         $post['dob'],
                                         $post['url']);
        if (!Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_MYACCOUNT_UPDATED'),
                                                         'Users.Profile.Response');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse($result->GetMessage(),
                                                         'Users.Profile.Response');
        }

        // unset unnecessary profile data
        unset($post['dob_day'],
              $post['dob_month'],
              $post['dob_year']);

        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Profile.Data');
        Jaws_Header::Location($this->GetURLFor('Profile'));
    }

}