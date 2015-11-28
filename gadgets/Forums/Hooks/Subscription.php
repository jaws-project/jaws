<?php
/**
 * Forums gadget hook
 *
 * @category    GadgetHook
 * @package     Forums
 */
class Forums_Hooks_Subscription extends Jaws_Gadget_Hook
{
    /**
     * Returns available subscription items
     *
     * @access  public
     * @param   int     $level     Item levels (0, 1)
     * @return array An array of subscription
     */
    function Execute($level)
    {
        $items = array();

        if ($level == 0) {
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups(true);
            if (count($groups) > 0) {
                foreach ($groups as $group) {
                    $item = array();
                    $item['action'] = 'group';
                    $item['id'] = $group['id'];
                    $item['title'] = _t('FORUMS_GROUP') . ' ' . $group['title'];
                    $item['url'] = $this->gadget->urlMap('Group', array('gid' => $group['id']));
                    $items[] = $item;
                }
            }
        }

        if ($level == 1) {
            $fModel = $this->gadget->model->load('Forums');
            $forums = $fModel->GetForums(false, true, true);

            if (count($forums) > 0) {
                foreach ($forums as $forum) {
                    $item = array();
                    $item['action'] = 'forum';
                    $item['id'] = $forum['id'];
                    $item['title'] = _t('FORUMS_FORUM') . ' ' . $forum['title'];
                    $item['url'] = $this->gadget->urlMap('Forum', array('fid' => $forum['id']));
                    $items[$forum['gid']][] = $item;
                }
            }
        }

        return $items;
    }

}