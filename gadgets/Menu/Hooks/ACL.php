<?php
/**
 * Menu - ACL hook
 *
 * @category    GadgetHook
 * @package     Menu
 */
class Menu_Hooks_ACL extends Jaws_Gadget_Hook
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
        $items = $gModel->GetGroups();
        if (!Jaws_Error::IsError($items)) {
            foreach ($items as $item) {
                $this->gadget->translate->insert(
                    'ACL_GROUPACCESS_'. $item['id'],
                    _t('MENU_ACL_GROUPACCESS', $item['title'])
                );
            }
        }
    }

}