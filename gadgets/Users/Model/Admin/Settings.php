<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the User gadget settings
     *
     * @access  public
     * @param   array   $settings   Users gadget settings array
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($settings)
    {
        if ($this->gadget->GetPermission('ManageAuthenticationMethod')) {
            $methods = $GLOBALS['app']->getAuthTypes();
            if ($methods == false || !in_array($settings['authtype'], $methods)) {
                unset($settings['authtype']);
            }
        } else {
            unset($settings['authtype']);
        }

        $keys = array(
            'authtype', 'anon_register', 'anon_activation', 'anon_group', 'password_recovery', 'reserved_users'
        );

        $res = true;
        foreach ($settings as $key => $value) {
            if (!in_array($key, $keys)) {
                continue;
            }
            $res = $res && $this->gadget->registry->update($key, $value);
        }

        return $res?: Jaws_Error::raiseError(_t('USERS_PROPERTIES_CANT_UPDATE'), __FUNCTION__);
    }

}