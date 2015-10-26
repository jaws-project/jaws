<?php
/**
 * Forums - ACL hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013-2015 Jaws Development Group
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
        $fModel = $this->gadget->model->load('Forums');
        $items = $fModel->GetForums();
        if (!Jaws_Error::IsError($items)) {
            foreach ($items as $item) {
                $this->gadget->translate->insert(
                    'ACL_FORUMPUBLIC_'. $item['id'],
                    _t('FORUMS_ACL_FORUM_PUBLIC', $item['title'])
                );
                $this->gadget->translate->insert(
                    'ACL_FORUMMEMBER_'. $item['id'],
                    _t('FORUMS_ACL_FORUM_MEMBER', $item['title'])
                );
                $this->gadget->translate->insert(
                    'ACL_FORUMMANAGE_'. $item['id'],
                    _t('FORUMS_ACL_FORUM_MANAGE', $item['title'])
                );
            }
        }

    }

}