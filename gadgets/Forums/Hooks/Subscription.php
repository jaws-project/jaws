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
     * @return array An array of subscription
     */
    function Execute()
    {
        $forumItems = array();
        $items = array();

        $gModel = $this->gadget->model->load('Groups');
        $fModel = $this->gadget->model->load('Forums');

        $forums = $fModel->GetForums(false, true, true);
        if (count($forums) > 0) {
            foreach ($forums as $forum) {
                $item = array();
                $item['action'] = 'Forum';
                $item['reference'] = $forum['id'];
                $item['title'] = _t('FORUMS_FORUM') . ' ' . $forum['title'];
                $item['url'] = $this->gadget->urlMap('Topics', array('fid' => $forum['id']));
                $forumItems[$forum['gid']][] = $item;
            }
        }

        $groups = $gModel->GetGroups(true);
        if (count($groups) > 0) {
            foreach ($groups as $group) {
                $item = array();
                $item['selectable'] = false;
                $item['action'] = 'Group';
                $item['reference'] = $group['id'];
                $item['title'] = _t('FORUMS_GROUP') . ' ' . $group['title'];
                $item['url'] = $this->gadget->urlMap('Group', array('gid' => $group['id']));
                if(isset($forumItems[$group['id']])) {
                    $item['sub_items'] = $forumItems[$group['id']];
                }
                $items[] = $item;
            }
        }

        return $items;
    }

}