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
     * Get the available authentication types
     *
     * @access  public
     * @return  array   Array with available authentication types
     */
    function GetAuthTypes()
    {
        return array_map('basename', glob(JAWS_PATH . 'gadgets/Users/Account/*', GLOB_ONLYDIR));
    }

    /**
     * Updates the User gadget settings
     *
     * @access  public
     * @param   array   $settings   Users gadget settings array
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateSettings($settings)
    {
        if ($this->gadget->GetPermission('ManageAuthenticationMethod')) {
            $methods = $this->GetAuthTypes();
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

        return $res;
    }

}