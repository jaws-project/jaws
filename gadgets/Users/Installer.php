<?php
/**
 * Users Installer
 *
 * @category    GadgetModel
 * @package     Users
 */
class Users_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('latest_limit', '10'),
        array('password_recovery', 'false'),
        array('register_notification', 'true'),
        array('authtype', 'Default'),
        array('anon_register', 'false'),
        array('anon_activation', 'user'),
        array('anon_group', ''),
        array('reserved_users', ''),
        array('multi_domain', 'false'),
        array('default_domain', '0'),
        array('default_concurrents', 0),
        array('login_transfer_gadget_index', ''),
        array('login_transfer_gadget_admin', 'ControlPanel'),
        array('two_step_verification', false, true),
    );

    /**
     * Default ACL value of the gadget backend
     *
     * @var     bool
     * @access  protected
     */
    var $default_admin_acl = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageUsers',
        'ManageGroups',
        'ManageOnlineUsers',
        'ManageSettings',
        'ManageUserACLs',
        'ManageGroupACLs',
        'ManageAuthenticationMethod',
        'ManageFriends',
        'AccessUserLayout',
        'ManageUserLayout',
        'AccessUsersLayout',
        'ManageUsersLayout',
        'EditUserName',
        'EditUserEmail',
        'EditUserMobile',
        'EditUserNickname',
        'EditUserPassword',
        'EditUserPersonal',
        'EditUserContacts',
        'EditUserPreferences',
        'AccessUserAttributes',
        'ModifyUserAttributes',
        'AccessGroupAttributes',
        'ModifyGroupAttributes',
        'EditUserBookmarks',
        'AccessUsersProfile',
    );

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

        $new_dir = ROOT_DATA_PATH . 'avatar';
        if (!$this->app->fileManagement::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        // Create the group 'users'
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
        if (Jaws_Error::IsError($result) && MDB2_ERROR_CONSTRAINT != $result->getCode()) {
            return $result;
        }

        // Add listener for UserChanges event
        $this->gadget->event->insert('UserChanges');

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
        if (version_compare($old, '2.0.0', '<')) {
            $variables = array();
            $variables['logon_hours'] = str_pad('', 42, 'F');
            $result = $this->installSchema('schema.xml', $variables, '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // update users passwords
            $usersTable = Jaws_ORM::getInstance()->table('users');
            $usersTable->update(
                array('password' => $usersTable->concat(array('{SSHA1}', 'text'), 'password'))
            )->where($usersTable->length('password'), 32, '>')
            ->exec();
            $usersTable->update(
                array('password' => $usersTable->concat(array('{MD5}', 'text'), 'password'))
            )->where($usersTable->length('password'), 32)
            ->exec();

            // ACL keys
            $this->gadget->acl->insert('ManageFriends');
            $this->gadget->acl->insert('AccessDashboard');
            $this->gadget->acl->insert('ManageDashboard');
        }

        if (version_compare($old, '2.1.0', '<')) {
            $this->gadget->registry->delete('anon_repetitive_email');
        }

        if (version_compare($old, '2.2.0', '<')) {
            $result = $this->installSchema('2.2.0.xml', array(), '2.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.3.0', '<')) {
            $result = $this->installSchema('2.3.0.xml', array(), '2.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $tblUsers = Jaws_ORM::getInstance()->table('users');
            $users = $tblUsers->select(
                'id:integer', 'email', 'url', 'nickname', 'fname', 'lname', 'avatar',
                'address', 'postal_code', 'phone_number', 'mobile_number', 'fax_number'
            )->orderBy('id')->fetchAll();
            if (Jaws_Error::IsError($users)) {
                return $users;
            }

            $tblUsers->beginTransaction();
            $tblContacts = Jaws_ORM::getInstance()->table('users_contacts');
            foreach ($users as $user) {
                $result = $tblContacts->insert(
                    array(
                        'owner'   => $user['id'],
                        'name'    => $user['nickname'],
                        'image'   => $user['avatar'],
                        'note'    => '',
                        'tel'     => json_encode(array('home' => $user['phone_number'])),
                        'fax'     => json_encode(array('home' => $user['fax_number'])),
                        'mobile'  => json_encode(array('home' => $user['mobile_number'])),
                        'url'     => json_encode(array('home' => $user['url'])),
                        'email'   => json_encode(array('home' => $user['email'])),
                        'address' => json_encode(array(
                            'home' => array(
                                'location' => $user['address'],
                                'postcode' => $user['postal_code']
                            )
                        )),
                    )
                )->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
                // link inserted contact to default user contact
                $result = $tblUsers->update(array('contact' => (int)$result))->where('id', $user['id'])->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            $tblUsers->commit();
        }

        if (version_compare($old, '2.4.0', '<')) {
            $result = $this->installSchema('2.4.0.xml', array(), '2.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.5.0', '<')) {
            $result = $this->installSchema('2.5.0.xml', array(), '2.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.6.0', '<')) {
            $result = $this->installSchema('2.6.0.xml', array(), '2.5.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.7.0', '<')) {
            $result = $this->installSchema('2.7.0.xml', array(), '2.6.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Registry keys
            $this->gadget->registry->insert('default_domain', '0');
        }

        if (version_compare($old, '2.8.0', '<')) {
            // Registry keys
            $this->gadget->registry->insert('reserved_users', '');
        }

        if (version_compare($old, '2.9.0', '<')) {
            $result = $this->installSchema('2.9.0.xml', array(), '2.7.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $this->gadget->acl->insert('EditUserMobile');
        }

        if (version_compare($old, '3.0.0', '<')) {
            $result = $this->installSchema('3.0.0.xml', array(), '2.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.1.0', '<')) {
            $result = $this->installSchema('3.1.0.xml', array(), '3.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.2.0', '<')) {
            // Registry keys
            $this->gadget->registry->insert('multi_domain', 'false');
            // ACL keys
            $this->gadget->acl->rename('ManageProperties', 'ManageSettings');
        }

        if (version_compare($old, '3.3.0', '<')) {
            // Registry keys
            $this->gadget->registry->insert('two_step_verification', false, true);
        }

        if (version_compare($old, '3.4.0', '<')) {
            $result = $this->installSchema('3.4.0.xml', array(), '3.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.5.0', '<')) {
            $result = $this->installSchema('3.5.0.xml', array(), '3.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.11.0', '<')) {
            // ACL keys
            $this->gadget->acl->insert('AccessUserLayout');
            $this->gadget->acl->insert('ManageUserLayout');
            $this->gadget->acl->insert('AccessUsersLayout');
            $this->gadget->acl->insert('ManageUsersLayout');
            $this->gadget->acl->delete('AccessDashboard');
            $this->gadget->acl->delete('ManageDashboard');
        }

        if (version_compare($old, '3.12.0', '<')) {
            $result = $this->installSchema('3.12.0.xml', array(), '3.5.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.14.0', '<')) {
            $result = $this->installSchema('3.14.0.xml', array(), '3.12.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '4.0.0', '<')) {
            $this->gadget->acl->insert('AccessUsersProfile', '', true);
        }

        if (version_compare($old, '4.1.0', '<')) {
            // Add listener for UserChanges event
            $this->gadget->event->insert('UserChanges');
        }

        if (version_compare($old, '4.2.0', '<')) {
            // ACL keys
            $this->gadget->acl->update('default_admin', '', true);
        }

        if (version_compare($old, '4.3.0', '<')) {
            $result = $this->installSchema('4.3.0.xml', array(), '3.14.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '4.4.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '4.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '4.5', '<')) {
            // Registry keys
            $this->gadget->registry->insert('login_transfer_gadget_index', '');
            $this->gadget->registry->insert('login_transfer_gadget_admin', 'ControlPanel');
        }

        if (version_compare($old, '4.7.0', '<')) {
            // do nothing!
        }

        if (version_compare($old, '4.8.0', '<')) {
            // ACL keys
            $this->gadget->acl->insert('AccessUserAttributes');
            $this->gadget->acl->insert('ModifyUserAttributes');
        }

        if (version_compare($old, '4.9.0', '<')) {
            // ACL keys
            $this->gadget->acl->insert('AccessGroupAttributes');
            $this->gadget->acl->insert('ModifyGroupAttributes');
        }

        if (version_compare($old, '5.0', '<')) {
            // Registry keys
            $this->gadget->registry->insert('default_concurrents', 0);
        }

        return true;
    }

}