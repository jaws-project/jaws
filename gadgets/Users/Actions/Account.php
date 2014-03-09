<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
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
        $response = $GLOBALS['app']->Session->PopResponse('Users.Account.Response');
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

        $account['type'] = $response['type'];
        $account['text'] = $response['text'];

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

            // set new email
            $post['new_email'] = '';
            if ($post['email'] != $GLOBALS['app']->Session->GetAttribute('email')) {
                $post['new_email'] = $post['email'];
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
                $post['new_email'],
                $post['password']
            );
            // unset unnecessary account data
            unset($post['password'], $post['chkpassword']);
            if (!Jaws_Error::IsError($result)) {
                $message = _t('USERS_MYACCOUNT_UPDATED');
                if (!empty($post['new_email'])) {
                    $mResult = $this->ReplaceEmailNotification(
                        $GLOBALS['app']->Session->GetAttribute('user'),
                        $post['username'],
                        $post['nickname'],
                        $post['new_email'],
                        $post['email']
                    );
                    if (Jaws_Error::IsError($mResult)) {
                        $message = $message. "\n" . $mResult->getMessage();
                    } else {
                        $message = $message. "\n" . _t('USERS_EMAIL_REPLACEMENT_SENT');
                    }
                }
                $GLOBALS['app']->Session->PushResponse(
                    $message,
                    'Users.Account.Response'
                );
            } else {
                $GLOBALS['app']->Session->PushResponse(
                    $result->GetMessage(),
                    'Users.Account.Response',
                    RESPONSE_ERROR,
                    $post
                );
            }
        } else {
            // unset unnecessary account data
            unset($post['password'], $post['chkpassword']);
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_USERS_PASSWORDS_DONT_MATCH'),
                'Users.Account.Response',
                RESPONSE_ERROR,
                $post
            );
        }

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
        if ($this->gadget->registry->fetch('password_recovery') !== 'true') {
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

    /**
     * Sends replace email notification to user
     *
     * @access  public
     * @param   int     $user_id    User's ID
     * @param   string  $nickname   User's nickname
     * @param   string  $new_email  User's new email
     * @param   string  $old_email  User's old email
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function ReplaceEmailNotification($user_id, $username, $nickname, $new_email, $old_email)
    {
        $tpl = $this->gadget->template->load('NewEmail.txt');
        $tpl->SetBlock('Notification');
        $tpl->SetVariable('say_hello', _t('USERS_EMAIL_REPLACEMENT_HELLO', $nickname));
        $tpl->SetVariable('message', _t('USERS_EMAIL_REPLACEMENT_MSG'));

        $tpl->SetBlock('Notification/IP');
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        $tpl->SetVariable('ip', $_SERVER['REMOTE_ADDR']);
        $tpl->ParseBlock('Notification/IP');

        $tpl->SetVariable('lbl_username', _t('USERS_USERS_USERNAME'));
        $tpl->SetVariable('username', $username);

        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $old_email);

        $jUser = new Jaws_User;
        $verifyKey = $jUser->UpdateEmailVerifyKey($user_id);
        if (Jaws_Error::IsError($verifyKey)) {
            return $verifyKey;
        } else {
            $tpl->SetBlock('Notification/Activation');
            $tpl->SetVariable('lbl_activation_link', _t('USERS_ACTIVATE_ACTIVATION_LINK'));
            $tpl->SetVariable(
                'activation_link',
                $this->gadget->urlMap(
                    'ReplaceUserEmail',
                    array('key' => $verifyKey),
                    true
                )
            );
            $tpl->ParseBlock('Notification/Activation');
        }

        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->SetVariable('thanks',    _t('GLOBAL_THANKS'));

        $tpl->ParseBlock('Notification');
        $body = $tpl->Get();

        $subject = _t('USERS_EMAIL_REPLACEMENT_SUBJECT', $site_name);
        $mail = new Jaws_Mail;
        $mail->SetFrom();
        $mail->AddRecipient($new_email);
        $mail->SetSubject($subject);
        $mail->SetBody($this->gadget->ParseText($body));
        $mresult = $mail->send();
        if (Jaws_Error::IsError($mresult)) {
            return $mresult;
        }

        return true;
    }

}