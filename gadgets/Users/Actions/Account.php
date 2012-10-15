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
class Users_Actions_Account extends UsersHTML
{
    /**
     * Builds a simple form to update user account info(nickname, email, password)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Account()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor(
                                                'Users',
                                                'LoginBox',
                                                array('referrer'  => Jaws_Utils::getRequestURL(false))), true);
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserAccount');
        $account = $GLOBALS['app']->Session->PopSimpleResponse('Users.Account.Data');
        if (empty($account)) {
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $jUser = new Jaws_User;
            $account  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Account.html');
        $tpl->SetBlock('account');
        $tpl->SetVariable('title', _t('USERS_ACCOUNT_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        $tpl->SetVariable('lbl_email',         _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email',             $xss->filter($account['email']));
        $tpl->SetVariable('lbl_nickname',      _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('nickname',          $xss->filter($account['nickname']));
        $tpl->SetVariable('lbl_password',      _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('emptypassword',     _t('USERS_NOCHANGE_PASSWORD'));
        $tpl->SetVariable('lbl_checkpassword', _t('USERS_USERS_PASSWORD_VERIFY'));

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Users.Account.Response')) {
            $tpl->SetBlock('account/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('account/response');
        }
        $tpl->ParseBlock('account');
        return $tpl->Get();
    }

    /**
     * Updates user account information
     *
     * @access  public
     * @return  void
     */
    function UpdateAccount()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor(
                                                'Users',
                                                'LoginBox',
                                                array('referrer'  => Jaws_Utils::getRequestURL(false))), true);
        }

        $GLOBALS['app']->Session->CheckPermission('Users', 'EditUserAccount');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('email', 'nickname', 'password', 'password_check'), 'post');

        if ($post['password'] === $post['password_check']) {
            $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Account');
            $result = $model->UpdateAccount($GLOBALS['app']->Session->GetAttribute('user'),
                                            $GLOBALS['app']->Session->GetAttribute('username'),
                                            $post['email'],
                                            $post['nickname'],
                                            $post['password']);
            if (!Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_MYACCOUNT_UPDATED'),
                                                             'Users.Account.Response');
            } else {
                $GLOBALS['app']->Session->PushSimpleResponse($result->GetMessage(),
                                                             'Users.Account.Response');
            }
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('USERS_USERS_PASSWORDS_DONT_MATCH'),
                                                         'Users.Account.Response');
        }

        // unset unnecessary account data
        unset($post['password'], $post['password_check']);
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Account.Data');
        Jaws_Header::Location($this->GetURLFor('Account'));
    }

    /**
     * Checks if given recovery key really exists, it it does then generates
     * a new password(pronounceable) and sends it to the user mailbox
     *
     * @access  public
     * @return  string  XHTML template
     */
    function ChangePassword()
    {
        if ($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery') !== 'true') {
            return parent::_404();
        }

        $request =& Jaws_Request::getInstance();
        $key     = $request->get('key', 'get');

        $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Account');
        $result = $model->ChangePassword($key);

        if (Jaws_Error::IsError($result)) {
            return $result->GetMessage();
        }

        return _t('USERS_FORGOT_PASSWORD_CHANGED');
    }

}