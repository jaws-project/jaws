<?php
/**
 * Creates a first user.
 *
 * @category    Application
 * @package     InstallStage
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Installer_CreateUser extends JawsInstaller
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
            if (array_key_exists($key, $post) && !is_null($post[$key])) {
                $values[$key] = $post[$key];
            }
        }

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/CreateUser/templates');
        $tpl->SetBlock('CreateUser');

        $tpl->setVariable('lbl_info',     $this::t('USER_INFO'));
        $tpl->setVariable('lbl_notice',   $this::t('USER_NOTICE'));
        $tpl->setVariable('lbl_user',     $this::t('USER_USER'));
        $tpl->setVariable('user_info',    $this::t('USER_USER_INFO'));
        $tpl->setVariable('lbl_pass',     $this::t('USER_PASS'));
        $tpl->setVariable('lbl_repeat',   $this::t('USER_REPEAT'));
        $tpl->setVariable('repeat_info',  $this::t('USER_REPEAT_INFO'));
        $tpl->setVariable('lbl_nickname', $this::t('USER_NAME'));
        $tpl->setVariable('name_info',    $this::t('USER_NAME_INFO'));
        $tpl->setVariable('lbl_email',    $this::t('USER_EMAIL'));
        $tpl->SetVariable('next',         Jaws::t('NEXT'));

        if ($_SESSION['secure']) {
            $JCrypt = Jaws_Crypt::getInstance(
                array(
                    'pvt_key' => $_SESSION['pvt_key'],
                    'pub_key' => $_SESSION['pub_key'],
                )
            );
            if (!Jaws_Error::IsError($JCrypt)) {
                $tpl->SetVariable('pubkey', $JCrypt->getPublic());
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
                require_once ROOT_JAWS_PATH . 'include/Jaws/Crypt.php';
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
                _log(JAWS_DEBUG,"The password and repeat boxes don't match, please try again.");
                return new Jaws_Error($this::t('USER_RESPONSE_PASS_MISMATCH'), 0, JAWS_ERROR_WARNING);
            }

            return true;
        }

        _log(JAWS_DEBUG,"You must complete the username, nickname, password, and repeat boxes.");
        return new Jaws_Error($this::t('USER_RESPONSE_INCOMPLETE'), 0, JAWS_ERROR_WARNING);
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
            require_once ROOT_JAWS_PATH . 'include/Jaws/Crypt.php';
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

        require_once ROOT_JAWS_PATH . 'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['install']['Database']);
        #if (Jaws_Error::IsError($objDatabase)) {
        #   return new Jaws_Error("There was a problem connecting to the database, please check the details and try again.", 0, JAWS_ERROR_WARNING);
        #}

        require_once ROOT_JAWS_PATH . 'include/Jaws.php';
        $jawsApp = Jaws::getInstance();
        $jawsApp->registry->init();
        $jawsApp->loadPreferences(array('language' => $_SESSION['install']['language']), false);
        Jaws_Translate::getInstance()->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);

        $userInfo = Jaws_Gadget::getInstance('Users')->model->load('User')->get($post['username']);
        if (!Jaws_Error::IsError($userInfo)) {
            //username exists
            if (isset($userInfo['username'])) {
                _log(JAWS_DEBUG,"Update existing user");
                $res = Jaws_Gadget::getInstance('Users')->model->load('User')->update(
                    $userInfo['id'],
                    array(
                        'username' => $post['username'], 
                        'nickname' => $post['nickname'],
                        'email'    => $post['email'],
                        'password' => $post['password'],
                    )
                );
            } else {
                _log(JAWS_DEBUG,"Adding first/new admin user to Jaws");
                $res = Jaws_Gadget::getInstance('Users')->model->load('User')->add(
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
            _log(JAWS_DEBUG,"There was a problem while creating your user:");
            _log(JAWS_DEBUG,$res->GetMessage());
            return new Jaws_Error($this::t('USER_RESPONSE_CREATE_FAILED'), 0, JAWS_ERROR_ERROR);
        }

        return true;
    }
}