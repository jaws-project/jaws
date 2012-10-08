<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UsersAdminModel extends Jaws_Model
{
    /**
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
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

        //registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/Users/pluggable', 'false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Users/password_recovery', 'false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Users/register_notification', 'true');

        //Create the group 'Jaws_Users'
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User;
        $userModel->AddGroup('users', 'Users', '', true, false); //Don't check if it returns true or false
        
        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
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
            $result = $this->installSchema('schema.xml', $variables, '0.8.7.xml');
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

        return true;
    }

}