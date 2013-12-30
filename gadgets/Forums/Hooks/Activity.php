<?php
/**
 * Forums gadget hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 */
class Forums_Hooks_Activity extends Jaws_Gadget_Hook
{
    /**
     * Returns public Forums array
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  array   An array of user activity
     */
    function Execute($uid, $uname)
    {
        $entity = array();
        $model = $this->gadget->model->load('Topics');
        $topicCount = $model->GetUserTopicCount($uid);

        if ($topicCount > 0) {
            $entity[0]['title'] = _t('FORUMS_TOPICS');
            $entity[0]['count'] = $topicCount;
            $entity[0]['url'] = $this->gadget->urlMap('UserTopics', array('user' => $uname));
        }

        $model = $this->gadget->model->load('Posts');
        $postCount = $model->GetUserPostsCount($uid);
        if ($postCount > 0) {
            $entity[1]['title'] = _t('FORUMS_POSTS');
            $entity[1]['count'] = $postCount;
            $entity[1]['url'] = $this->gadget->urlMap('UserPosts', array('user' => $uname));
        }

        if ($postCount == 0 && $topicCount == 0) {
            return array();
        }

        return $entity;
    }

}