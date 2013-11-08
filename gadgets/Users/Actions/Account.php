<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Account extends Jaws_Gadget_Action
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
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

       $this->gadget->CheckPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', '', false);
        $response = $GLOBALS['app']->Session->PopResponse('Users.Account.Data');
        if (!isset($response['data'])) {
            $jUser = new Jaws_User;
            $account = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), true, true);
        } else {
            $account = $response['data'];
        }

        $account['title']  = _t('USERS_ACCOUNT_INFO');
        $account['update'] = _t('USERS_USERS_ACCOUNT_UPDATE');
        $account['lbl_username']    = _t('USERS_USERS_USERNAME');
        $account['lbl_nickname']    = _t('USERS_USERS_NICKNAME');
        $account['lbl_email']       = _t('GLOBAL_EMAIL');
        $account['lbl_password']    = _t('USERS_USERS_PASSWORD');
        $account['emptypassword']   = _t('USERS_NOCHANGE_PASSWORD');
        $account['lbl_chkpassword'] = _t('USERS_USERS_PASSWORD_VERIFY');

        if (!$this->gadget->GetPermission('EditUserName')) {
            $account['username_disabled'] = 'disabled="disabled"';
        }
        if (!$this->gadget->GetPermission('EditUserNickname')) {
            $account['nickname_disabled'] = 'disabled="disabled"';
        }
        if (!$this->gadget->GetPermission('EditUserEmail')) {
            $account['email_disabled'] = 'disabled="disabled"';
        }
        if (!$this->gadget->GetPermission('EditUserPassword')) {
            $account['password_disabled'] = 'disabled="disabled"';
        }

        if (empty($account['avatar'])) {
            $user_current_avatar = $GLOBALS['app']->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $GLOBALS['app']->getDataURL() . "avatar/" . $account['avatar'];
            $user_current_avatar .= !empty($account['last_update']) ? "?" . $account['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $account['avatar'] = $avatar->Get();

        $account['response'] = $GLOBALS['app']->Session->PopResponse('Users.Account.Response');

        // Load the template
        $tpl = $this->gadget->template->load('Account.html');
        return $tpl->fetch($account);
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
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', '', false);
        $post = jaws()->request->fetch(array('username', 'nickname', 'email', 'password', 'chkpassword'), 'post');
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

            $model  = $this->gadget->model->load('Account');
            $result = $model->UpdateAccount(
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['username'],
                $post['nickname'],
                $post['email'],
                $post['password']
            );
            if (!Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('USERS_MYACCOUNT_UPDATED'),
                    'Users.Account.Response'
                );
            } else {
                $GLOBALS['app']->Session->PushResponse(
                    $result->GetMessage(),
                    'Users.Account.Response',
                    RESPONSE_ERROR
                );
            }
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_USERS_PASSWORDS_DONT_MATCH'),
                'Users.Account.Response',
                RESPONSE_ERROR
            );
        }

        // unset unnecessary account data
        unset($post['password'], $post['chkpassword']);
        $GLOBALS['app']->Session->PushResponse(
            '',
            'Users.Account.Data',
            RESPONSE_NOTICE,
            $post
        );
        Jaws_Header::Location($this->gadget->urlMap('Account'));
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
            return Jaws_HTTPError::Get(404);
        }

        $key = jaws()->request->fetch('key', 'get');

        $uModel  = $this->gadget->model->load('Account');
        $result = $uModel->ChangePassword($key);
        if (Jaws_Error::IsError($result)) {
            return $result->GetMessage();
        }

        return _t($result? 'USERS_FORGOT_PASSWORD_CHANGED' : 'USERS_FORGOT_KEY_NOT_VALID');
    }

}