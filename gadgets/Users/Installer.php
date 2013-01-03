<?php
/**
 * Users Installer
 *
 * @category    GadgetModel
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $variables = array();
        $variables['logon_hours'] = str_pad('', 42, 'F');
        $result = $this->installSchema('schema.xml', $variables);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = JAWS_DATA . 'avatar';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('USERS_NAME'));
        }

        // Registry keys
        $this->gadget->AddRegistry(array(
            'pluggable' => 'false',
            'latest_limit' => '10',
            'password_recovery' => 'false',
            'register_notification' => 'true',
            'auth_method' => 'Default',
            'anon_register' => 'false',
            'anon_repetitive_email' => 'true',
            'anon_activation' => 'user',
            'anon_group' => '',
        ));

        // Create the group 'users'
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User;
        $result = $userModel->AddGroup(
            array(
                'name' => 'users',
                'title' => 'Users',
                'description' => '',
                'enabled' => true,
                'removable' => false
            )
        );
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.8.7', '<')) {
            $result = $this->installSchema('0.8.7.xml', '', '0.8.6.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $params= array();
            $params['user_type']  = 0;
            $params['superadmin'] = true;
            $sql = 'UPDATE [[users]] SET [superadmin] = {superadmin} WHERE [user_type] = {user_type}';
            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                //return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/ManageAuthenticationMethod', 'false');
        }

        if (version_compare($old, '0.8.9', '<')) {
            $variables = array();
            $variables['logon_hours'] = str_pad('', 42, 'F');
            $result = $this->installSchema('0.8.9.xml', $variables, '0.8.7.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            switch ($GLOBALS['db']->getDriver()) {
                case 'mysql':
                case 'mysqli':
                    $type ='unsigned';
                    break;

                default:
                    $type ='int';
            }

            $sql = "UPDATE [[users]] SET [status] = CAST([enabled] AS {$type})";
            $result = $GLOBALS['db']->query($sql);
            if (Jaws_Error::IsError($result)) {
                //return $result;
            }

            $new_dir = JAWS_DATA . 'avatar';
            if (!Jaws_Utils::mkdir($new_dir)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('USERS_NAME'));
            }
        }

        if (version_compare($old, '1.0.0', '<')) {
            $variables = array();
            $variables['logon_hours'] = str_pad('', 42, 'F');
            $result = $this->installSchema('schema.xml', $variables, '0.8.9.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $sql = "UPDATE [[users]] SET [registered_date] = {now}";
            $result = $GLOBALS['db']->query($sql, array('now'=> time()));
            if (Jaws_Error::IsError($result)) {
                //return $result;
            }

            // Update layout actions
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Users', 'LoginBox', 'LoginBox', 'LoginBox');
                $layoutModel->EditGadgetLayoutAction('Users', 'LoginLinks', 'LoginLinks', 'LoginBox');
            }

            // Registry key
            $this->gadget->AddRegistry('latest_limit', '10');

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserName',        'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserNickname',    'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserEmail',       'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserPassword',    'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserPersonal',    'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Users/EditUserPreferences', 'false');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Users/EditAccountPassword');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Users/EditAccountInformation');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Users/EditAccountProfile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Users/EditAccountPreferences');
        }

        return true;
    }

}