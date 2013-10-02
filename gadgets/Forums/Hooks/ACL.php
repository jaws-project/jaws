<?php
/**
 * Forums - ACL hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Hooks_ACL extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of dynamic ACL keys
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $language = $this->gadget->registry->fetch('admin_language', 'Settings');
        $fModel = $this->gadget->load('Model')->load('Model', 'Forums');
        $items = $fModel->GetForums();
        if (!Jaws_Error::IsError($items)) {
            foreach ($items as $item) {
                define(
                    strtoupper('_'. $language. '_'. $this->gadget->name. '_ACL_FORUMACCESS_'. $item['id']),
                    _t('FORUMS_ACL_FORUM_ACCESS', $item['title'])
                );
            }
        }
    }

}