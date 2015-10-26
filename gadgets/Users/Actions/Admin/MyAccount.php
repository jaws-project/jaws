<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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

        $uModel = new Jaws_User();
        $uInfo = $uModel->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        if (Jaws_Error::IsError($uInfo) || empty($uInfo)) {
            return false;
        }

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('MyAccount.html');
        $tpl->SetBlock('MyAccount');
        $tpl->SetVariable('uid', $uInfo['id']);
        $tpl->SetVariable('legend_title', _t('USERS_USERS_ACCOUNT'));

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $GLOBALS['app']->Layout->AddScriptLink('libraries/js/rsa.lib.js');

            $tpl->SetBlock('MyAccount/encryption');
            // key length
            $length =& Piwi::CreateWidget('HiddenEntry', 'length', $JCrypt->length());
            $length->SetID('length');
            $tpl->SetVariable('length', $length->Get());
            // modulus
            $modulus =& Piwi::CreateWidget('HiddenEntry', 'modulus', $JCrypt->modulus());
            $modulus->SetID('modulus');
            $tpl->SetVariable('modulus', $modulus->Get());
            //exponent
            $exponent =& Piwi::CreateWidget('HiddenEntry', 'exponent', $JCrypt->exponent());
            $modulus->SetID('exponent');
            $tpl->SetVariable('exponent', $exponent->Get());
            $tpl->ParseBlock('MyAccount/encryption');
        }

        // username
        $username =& Piwi::CreateWidget('Entry', 'username', $uInfo['username']);
        $username->SetID('username');
        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $username->Get());

        // nickname
        $nickname =& Piwi::CreateWidget('Entry', 'nickname', $uInfo['nickname']);
        $nickname->SetID('nickname');
        $tpl->SetVariable('lbl_nickname', _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('nickname', $nickname->Get());

        // email
        $email =& Piwi::CreateWidget('Entry', 'email', $uInfo['email']);
        $email->SetID('email');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $email->Get());

        // pass1
        $pass1 =& Piwi::CreateWidget('PasswordEntry', 'pass1', '');
        $pass1->SetID('pass1');
        $tpl->SetVariable('lbl_pass1', _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('pass1', $pass1->Get());

        // pass2
        $pass2 =& Piwi::CreateWidget('PasswordEntry', 'pass2', '');
        $pass2->SetID('pass2');
        $tpl->SetVariable('lbl_pass2', _t('USERS_USERS_PASSWORD_VERIFY'));
        $tpl->SetVariable('pass2', $pass2->Get());

        $avatar =& Piwi::CreateWidget('Image',
                                      $uModel->GetAvatar($uInfo['avatar'],
                                                         $uInfo['email'],
                                                         128,
                                                         $uInfo['last_update']),
                                      $uInfo['username']);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());


        $btnSave =& Piwi::CreateWidget('Button',
                                       'SubmitButton',
                                       _t('GLOBAL_UPDATE'),
                                       STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript:updateMyAccount();");
        $tpl->SetVariable('save', $btnSave->Get());

        $tpl->SetVariable('incompleteUserFields', _t('USERS_MYACCOUNT_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('wrongPassword',        _t('USERS_MYACCOUNT_PASSWORDS_DONT_MATCH'));

        $tpl->ParseBlock('MyAccount');
        return $tpl->Get();
    }

}