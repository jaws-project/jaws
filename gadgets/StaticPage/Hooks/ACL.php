<?php
/**
 * StaticPage - ACL hook
 *
 * @category    GadgetHook
 * @package     StaticPage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Hooks_ACL extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of dynamic ACL keys
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $gModel = $this->gadget->model->load('Group');
        $groups = $gModel->GetGroups();
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $this->gadget->translate->insert(
                    'ACL_ACCESSGROUP_'. $group['id'],
                    $this::t('ACL_ACCESSGROUP', $group['title'])
                );
                $this->gadget->translate->insert(
                    'ACL_MANAGEGROUP_'. $group['id'],
                    $this::t('ACL_MANAGEGROUP', $group['title'])
                );
            }
        }

    }

}