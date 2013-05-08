<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Account extends Users_HTML
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
            Jaws_Header::Location(
                $this->gadget->GetURLFor(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $GLOBALS['app']->Session->CheckPermission(
            'Users',
            'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword',
            false);

        $account = $GLOBALS['app']->Session->PopSimpleResponse('Users.Account.Data');
        if (empty($account)) {
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $jUser = new Jaws_User;
            $account  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        }

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Account.html');
        $tpl->SetBlock('account');
        $tpl->SetVariable('title', _t('USERS_ACCOUNT_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        $tpl->SetVariable('lbl_username',    _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username',        $account['username']);
        $tpl->SetVariable('lbl_nickname',    _t('USERS_USERS_NICKNAME'));
        $tpl->SetVariable('nickname',        $account['nickname']);
        $tpl->SetVariable('lbl_email',       _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email',           $account['email']);
        $tpl->SetVariable('lbl_password',    _t('USERS_USERS_PASSWORD'));
        $tpl->SetVariable('emptypassword',   _t('USERS_NOCHANGE_PASSWORD'));
        $tpl->SetVariable('lbl_chkpassword', _t('USERS_USERS_PASSWORD_VERIFY'));
        if (!$this->gadget->GetPermission('EditUserName')) {
            $tpl->SetVariable('username_disabled', 'disabled="disabled"');
        }
        if (!$this->gadget->GetPermission('EditUserNickname')) {
            $tpl->SetVariable('nickname_disabled', 'disabled="disabled"');
        }
        if (!$this->gadget->GetPermission('EditUserEmail')) {
            $tpl->SetVariable('email_disabled', 'disabled="disabled"');
        }
        if (!$this->gadget->GetPermission('EditUserPassword')) {
            $tpl->SetVariable('password_disabled', 'disabled="disabled"');
        }

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
            Jaws_Header::Location(
                $this->gadget->GetURLFor(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $GLOBALS['app']->Session->CheckPermission(
            'Users',
            'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword',
            false);

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('username', 'nickname', 'email', 'password', 'chkpassword'), 'post');
        if ($post['password'] === $post['chkpassword']) {
            // check edit username permission
            if (empty($post['username']) ||
                !$this->gadget->GetPermission('EditUserName'))
            {
                $post['username'] = $GLOBALS['app']->Session->GetAttribute('username');
            }
            // check edit nickname permission
            if (empty($post['nickname']) ||
                !$this->gadget->GetPermission('EditUserNickname'))
            {
                $post['nickname'] = $GLOBALS['app']->Session->GetAttribute('nickname');
            }
            // check edit email permission
            if (empty($post['email']) ||
                !$this->gadget->GetPermission('EditUserEmail'))
            {
                $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            }
            // check edit password permission
            if (empty($post['password']) ||
                !$this->gadget->GetPermission('EditUserPassword'))
            {
                $post['password'] = null;
            }

            $model  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Account');
            $result = $model->UpdateAccount(
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['username'],
                $post['nickname'],
                $post['email'],
                $post['password']
            );
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
        unset($post['password'], $post['chkpassword']);
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Users.Account.Data');
        Jaws_Header::Location($this->gadget->GetURLFor('Account'));
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
        if ($this->gadget->GetRegistry('password_recovery') !== 'true') {
            return parent::_404();
        }

        $request =& Jaws_Request::getInstance();
        $key = $request->get('key', 'get');

        $uModel  = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Account');
        $result = $uModel->ChangePassword($key);
        if (Jaws_Error::IsError($result)) {
            return $result->GetMessage();
        }

        return _t($result? 'USERS_FORGOT_PASSWORD_CHANGED' : 'USERS_FORGOT_KEY_NOT_VALID');
    }

}