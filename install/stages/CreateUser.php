<?php
/**
 * Creates a first user.
 *
 * @category    Application
 * @package     InstallStage
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Installer_CreateUser extends JawsInstallerStage
{
    /**
     * Default values
     *
     * @access private
     * @var array
     */
    var $_Fields = array(
        'username' => 'jawsadmin',
        'nickname' => 'Jaws Administrator',
        'email'    => 'admin@example.org',
        'password' => '',
        'repeat'   => ''
    );

    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $values = $this->_Fields;
        $keys = array_keys($values);
        $request = Jaws_Request::getInstance();
        $post = $request->fetch($keys, 'post');
        foreach ($this->_Fields as $key => $value) {
            if ($post[$key] !== null) {
                $values[$key] = $post[$key];
            }
        }

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/CreateUser/templates');
        $tpl->SetBlock('CreateUser');

        $tpl->setVariable('lbl_info',     _t('INSTALL_USER_INFO'));
        $tpl->setVariable('lbl_notice',   _t('INSTALL_USER_NOTICE'));
        $tpl->setVariable('lbl_user',     _t('INSTALL_USER_USER'));
        $tpl->setVariable('user_info',    _t('INSTALL_USER_USER_INFO'));
        $tpl->setVariable('lbl_pass',     _t('INSTALL_USER_PASS'));
        $tpl->setVariable('lbl_repeat',   _t('INSTALL_USER_REPEAT'));
        $tpl->setVariable('repeat_info',  _t('INSTALL_USER_REPEAT_INFO'));
        $tpl->setVariable('lbl_nickname', _t('INSTALL_USER_NAME'));
        $tpl->setVariable('name_info',    _t('INSTALL_USER_NAME_INFO'));
        $tpl->setVariable('lbl_email',    _t('INSTALL_USER_EMAIL'));
        $tpl->SetVariable('next',         _t('GLOBAL_NEXT'));

        if ($_SESSION['secure']) {
            $JCrypt = Jaws_Crypt::getInstance(
                array(
                    'pvt_key' => $_SESSION['pvt_key'],
                    'pub_key' => $_SESSION['pub_key'],
                )
            );
            if (!Jaws_Error::IsError($JCrypt)) {
                $tpl->SetVariable('length',   $JCrypt->length());
                $tpl->SetVariable('modulus',  $JCrypt->modulus());
                $tpl->SetVariable('exponent', $JCrypt->exponent());
                $tpl->SetVariable('func_onsubmit', 'EncryptPassword(this)');
            } else {
                $_SESSION['secure'] = false;
                $tpl->SetVariable('func_onsubmit', 'true');
            }
        } else {
            $tpl->SetVariable('func_onsubmit', 'true');
        }

        $tpl->SetVariable('username', $values['username']);
        $tpl->SetVariable('password', '');
        $tpl->SetVariable('repeat',   '');
        $tpl->SetVariable('nickname', $values['nickname']);
        $tpl->SetVariable('email',    $values['email']);

        $tpl->ParseBlock('CreateUser');
        return $tpl->Get();
    }

    /**
     * Validates any data provided to the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Returns either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Validate()
    {
        $request = Jaws_Request::getInstance();
        $post = $request->fetch(array('username', 'repeat', 'password', 'nickname'), 'post');

        if (isset($_SESSION['install']['data']['CreateUser'])) {
            $post = $_SESSION['install']['data']['CreateUser'] + $post;
            // Just so that we can keep the repeat check
            if ($_SESSION['install']['data']['CreateUser']['password']) {
                $post['repeat'] = $post['password'];
            }
        }

        if (!empty($post['username']) &&
            !empty($post['password']) &&
            !empty($post['repeat']) &&
            !empty($post['nickname']))
        {
            if ($_SESSION['secure']) {
                require_once JAWS_PATH . 'include/Jaws/Crypt.php';
                $JCrypt =  Jaws_Crypt::getInstance(
                    array(
                        'pvt_key' => $_SESSION['pvt_key'],
                        'pub_key' => $_SESSION['pub_key'],
                    )
                );
                if (!Jaws_Error::isError($JCrypt)) {
                    $post['repeat'] = $JCrypt->decrypt($post['repeat']);
                    $post['password'] = $JCrypt->decrypt($post['password']);
                } else {
                    return $JCrypt;
                }
            }

            if ($post['password'] !== $post['repeat']) {
                _log(JAWS_LOG_DEBUG,"The password and repeat boxes don't match, please try again.");
                return new Jaws_Error(_t('INSTALL_USER_RESPONSE_PASS_MISMATCH'), 0, JAWS_ERROR_WARNING);
            }

            return true;
        }

        _log(JAWS_LOG_DEBUG,"You must complete the username, nickname, password, and repeat boxes.");
        return new Jaws_Error(_t('INSTALL_USER_RESPONSE_INCOMPLETE'), 0, JAWS_ERROR_WARNING);
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        $request = Jaws_Request::getInstance();
        $post = $request->fetch(array('username', 'email', 'nickname', 'password'), 'post');

        if (isset($_SESSION['install']['data']['CreateUser'])) {
            $post = $_SESSION['install']['data']['CreateUser'] + $post;
        }

        if ($_SESSION['secure']) {
            require_once JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt =  Jaws_Crypt::getInstance(
                array(
                    'pvt_key' => $_SESSION['pvt_key'],
                    'pub_key' => $_SESSION['pub_key'],
                )
            );
            if (!Jaws_Error::isError($JCrypt)) {
                $post['password'] = $JCrypt->decrypt($post['password']);
            } else {
                return $JCrypt;
            }
        }

        $_SESSION['install']['CreateUser'] = array(
            'username' => $post['username'],
            'email'    => $post['email'],
            'nickname' => $post['nickname']
        );

        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['install']['Database']);
        #if (Jaws_Error::IsError($objDatabase)) {
        #   return new Jaws_Error("There was a problem connecting to the database, please check the details and try again.", 0, JAWS_ERROR_WARNING);
        #}

        require_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->Registry->Init();
        $GLOBALS['app']->loadPreferences(array('language' => $_SESSION['install']['language']), false);
        Jaws_Translate::getInstance()->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $userInfo = $userModel->GetUser($post['username']);
        if (!Jaws_Error::IsError($userInfo)) {
            //username exists
            if (isset($userInfo['username'])) {
                _log(JAWS_LOG_DEBUG,"Update existing user");
                $res = $userModel->UpdateUser(
                    $userInfo['id'],
                    array(
                        'username' => $post['username'], 
                        'nickname' => $post['nickname'],
                        'email'    => $post['email'],
                        'password' => $post['password'],
                    )
                );
            } else {
                _log(JAWS_LOG_DEBUG,"Adding first/new admin user to Jaws");
                $res = $userModel->AddUser(
                    array(
                        'username' => $post['username'],
                        'nickname' => $post['nickname'],
                        'email'    => $post['email'],
                        'password' => $post['password'],
                        'superadmin' => true,
                    )
                );
            }
        } else {
            $res = $userInfo;
        }

        if (Jaws_Error::IsError($res)) {
            _log(JAWS_LOG_DEBUG,"There was a problem while creating your user:");
            _log(JAWS_LOG_DEBUG,$res->GetMessage());
            return new Jaws_Error(_t('INSTALL_USER_RESPONSE_CREATE_FAILED'), 0, JAWS_ERROR_ERROR);
        }

        return true;
    }
}