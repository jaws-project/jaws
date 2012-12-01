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
class Users_Actions_Personal extends UsersHTML
{
    /**
     * Builds a simple form to update user personal (fname, lname, gender, ...)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Personal()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                ),
                true
            );
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserPersonal');
        $personal = $GLOBALS['app']->Session->PopSimpleResponse('Users.Personal.Data');
        if (empty($personal)) {
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $jUser = new Jaws_User;
            $personal  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        }

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Personal.html');
        $tpl->SetBlock('personal');
        $tpl->SetVariable('title', _t('USERS_PERSONAL_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        $tpl->SetVariable('lbl_fname',  _t('USERS_USERS_FIRSTNAME'));
        $tpl->SetVariable('fname',      $personal['fname']);
        $tpl->SetVariable('lbl_lname',  _t('USERS_USERS_LASTNAME'));
        $tpl->SetVariable('lname',      $personal['lname']);
        $tpl->SetVariable('lbl_gender', _t('USERS_USERS_GENDER'));
        $tpl->SetVariable('gender_0',   _t('USERS_USERS_GENDER_0'));
        $tpl->SetVariable('gender_1',   _t('USERS_USERS_GENDER_1'));
        $tpl->SetVariable('gender_2',   _t('USERS_USERS_GENDER_2'));
        $tpl->SetVariable('selected_gender_'.(int)$personal['gender'], 'selected="selected"');

        if (empty($personal['dob'])) {
            $dob = array('', '', '');
        } else {
            $date = $GLOBALS['app']->loadDate();
            $dob = $date->Format($personal['dob'], 'Y-m-d');
            $dob = explode('-', $dob);
        }

        $tpl->SetVariable('lbl_dob',    _t('USERS_USERS_BIRTHDAY'));
        $tpl->SetVariable('dob_year',   $dob[0]);
        $tpl->SetVariable('dob_month',  $dob[1]);
        $tpl->SetVariable('dob_day',    $dob[2]);
        $tpl->SetVariable('dob_sample', _t('USERS_USERS_BIRTHDAY_SAMPLE'));

        // website
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url',     empty($personal['url'])? 'http://' : $personal['url']);

        // about
        $tpl->SetVariable('lbl_about', _t('USERS_USERS_ABOUT'));
        $tpl->SetVariable('about',     $personal['about']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.Personal.Response')) {
            $tpl->SetBlock('personal/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('personal/response');
        }
        $tpl->ParseBlock('personal');
        return $tpl->Get();
    }

    /**
     * Updates user personal
     *
     * @access  public
     * @return  void
     */
    function UpdatePersonal()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                ),
                true
            );
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserPersonal');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(
            array('fname', 'lname', 'gender', 'dob_year', 'dob_month', 'dob_day', 'url', 'about'),
            'post'
        );

        // validate url
        if (!preg_match('|^\S+://\S+\.\S+.+$|i', $post['url'])) {
            $post['url'] = '';
        }

        $post['dob'] = null;
        if (!empty($post['dob_year']) && !empty($post['dob_year']) && !empty($post['dob_year'])) {
            $date = $GLOBALS['app']->loadDate();
            $dob  = $date->ToBaseDate($post['dob_year'], $post['dob_month'], $post['dob_day']);
            $post['dob'] = date('Y-m-d H:i:s', $dob['timestamp']);
        }

        $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Personal');
        $result = $model->UpdatePersonal(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['fname'],
            $post['lname'],
            $post['gender'],
            $post['dob'],
            $post['url'],
            $post['about']
        );
        if (!Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_MYACCOUNT_UPDATED'),
                                                         'Users.Personal.Response');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse($result->GetMessage(),
                                                         'Users.Personal.Response');
        }

        // unset unnecessary personal data
        unset($post['dob_day'],
              $post['dob_month'],
              $post['dob_year']);

        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Personal.Data');
        Jaws_Header::Location($this->GetURLFor('Personal'));
    }

}