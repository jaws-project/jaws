<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_MyAccount extends Users_Actions_Admin_Default
{
    /**
     * Builds account settings for logged users
     *
     * @access  public
     * @return  string  XHTML content
     */
    function MyAccount()
    {
        $this->gadget->CheckPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', false);

        $uInfo = $this->app->users->GetUser($this->app->session->user->id, true, true);
        if (Jaws_Error::IsError($uInfo) || empty($uInfo)) {
            return false;
        }

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('MyAccount.html');
        $tpl->SetBlock('MyAccount');
        $tpl->SetVariable('uid', $uInfo['id']);
        $tpl->SetVariable('legend_title', $this::t('USERS_ACCOUNT'));

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $tpl->SetBlock('MyAccount/encryption');
            $tpl->SetVariable('pubkey', $JCrypt->getPublic());
            $tpl->ParseBlock('MyAccount/encryption');
        }

        // username
        $username =& Piwi::CreateWidget('Entry', 'username', $uInfo['username']);
        $username->SetID('username');
        $tpl->SetVariable('lbl_username', $this::t('USERS_USERNAME'));
        $tpl->SetVariable('username', $username->Get());

        // nickname
        $nickname =& Piwi::CreateWidget('Entry', 'nickname', $uInfo['nickname']);
        $nickname->SetID('nickname');
        $tpl->SetVariable('lbl_nickname', $this::t('USERS_NICKNAME'));
        $tpl->SetVariable('nickname', $nickname->Get());

        // email
        $email =& Piwi::CreateWidget('Entry', 'email', $uInfo['email']);
        $email->SetID('email');
        $tpl->SetVariable('lbl_email', Jaws::t('EMAIL'));
        $tpl->SetVariable('email', $email->Get());

        // mobile
        $mobile =& Piwi::CreateWidget('Entry', 'mobile', $uInfo['mobile']);
        $mobile->SetID('mobile');
        $tpl->SetVariable('lbl_mobile', $this::t('CONTACTS_MOBILE_NUMBER'));
        $tpl->SetVariable('mobile', $mobile->Get());

        // pass1
        $pass1 =& Piwi::CreateWidget('PasswordEntry', 'pass1', '');
        $pass1->SetID('pass1');
        $tpl->SetVariable('lbl_pass1', $this::t('USERS_PASSWORD'));
        $tpl->SetVariable('pass1', $pass1->Get());

        // pass2
        $pass2 =& Piwi::CreateWidget('PasswordEntry', 'pass2', '');
        $pass2->SetID('pass2');
        $tpl->SetVariable('lbl_pass2', $this::t('USERS_PASSWORD_VERIFY'));
        $tpl->SetVariable('pass2', $pass2->Get());

        $avatar =& Piwi::CreateWidget(
            'Image',
            $this->app->users->GetAvatar(
                $uInfo['avatar'],
                $uInfo['email'],
                128,
                $uInfo['last_update']
            ),
            $uInfo['username']
        );
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());


        $btnSave =& Piwi::CreateWidget('Button',
                                       'SubmitButton',
                                       Jaws::t('UPDATE'),
                                       STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').updateMyAccount();");
        $tpl->SetVariable('save', $btnSave->Get());

        $tpl->SetVariable('incompleteUserFields', $this::t('MYACCOUNT_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('wrongPassword',        $this::t('MYACCOUNT_PASSWORDS_DONT_MATCH'));

        $tpl->ParseBlock('MyAccount');
        return $tpl->Get();
    }

}