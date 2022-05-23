<?php
/**
 * Users - ACL hook
 *
 * @category    GadgetHook
 * @package     Users
 */
class Users_Hooks_ACL extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of dynamic ACL keys
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $groups = $this->gadget->model->load('Group')->list();
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $this->gadget->translate->insert(
                    'ACL_GROUPMANAGE_'. $group['id'],
                    _t('USERS_ACL_GROUP_MANAGE', $group['title'])
                );
            }
        }

    }

}