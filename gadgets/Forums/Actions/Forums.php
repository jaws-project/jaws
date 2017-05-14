<?php
/**
 * Forums Core Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Forums extends Jaws_Gadget_Action
{
    /**
     * Get RecentTopics action params
     *
     * @access  public
     * @return  array list of RecentTopics action params
     */
    function GroupLayoutParams()
    {
        $result = array();
        $gModel = $this->gadget->model->load('Groups');
        $groups = $gModel->GetGroups(true);
        if (!Jaws_Error::IsError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $pgroups  = array('0' => _t('FORUMS_GROUPS_ALL')) + $pgroups;
            $result[] = array(
                'title' => _t('FORUMS_GROUPS'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Display groups and forums
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Forums()
    {
        $gModel = $this->gadget->model->load('Groups');
        $groups = $gModel->GetGroups(true);
        if (Jaws_Error::IsError($groups) || empty($groups)) {
            return false;
        }

        $GLOBALS['app']->Layout->SetTitle(_t('FORUMS_FORUMS'));
        $tpl = $this->gadget->template->load('Forums.html');
        $tpl->SetBlock('forums');

        $tpl->SetVariable('title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('url', $this->gadget->urlMap('Forums'));

        foreach ($groups as $group) {
            $this->Group($group['id'], $tpl);
        }

        $tpl->ParseBlock('forums');
        return $tpl->Get();
    }

    /**
     * Display forums of a group
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Group($group = null, $tpl = null)
    {
        $group = isset($group)? $group : $this->gadget->request->fetch('gid', 'get');
        $gModel = $this->gadget->model->load('Groups');
        $group  = $gModel->GetGroup($group);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return Jaws_HTTPError::Get(404);
        }

        $objDate = Jaws_Date::getInstance();
        if (is_null($tpl)) {
            $block = 'groups';
            $GLOBALS['app']->Layout->SetTitle($group['title']);
            $tpl = $this->gadget->template->load('Group.html');
            $tpl->SetBlock("$block");
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
            $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('url', $this->gadget->urlMap('Group', array('gid' => $group['id'])));
            $tpl->SetVariable('description', $group['description']);
            $standalone = true;
        } else {
            $standalone = false;
            $block = 'forums';
        }

        // date format
        $date_format = $this->gadget->registry->fetch('date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

        $fModel = $this->gadget->model->load('Forums');
        $forums = $fModel->GetForums($group['id'], true, true, true);
        if (Jaws_Error::IsError($forums)) {
            return false;
        }

        $tpl->SetBlock("$block/group");
        $tpl->SetVariable('title', $group['title']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Group', array('gid' => $group['id'])));
        $tpl->SetVariable('lbl_topics', _t('FORUMS_TOPICS'));
        $tpl->SetVariable('lbl_posts', _t('FORUMS_POSTS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));
        foreach ($forums as $forum) {
            $tpl->SetBlock("$block/group/forum");
            $tpl->SetVariable('status', (int)$forum['locked']);
            $tpl->SetVariable('title', $forum['title']);
            $tpl->SetVariable('url', $this->gadget->urlMap('Topics', array('fid' => $forum['id'])));
            $tpl->SetVariable('description', $forum['description']);
            $tpl->SetVariable('topics', (int)$forum['topics']);
            $tpl->SetVariable('posts',  (int)$forum['posts']);

            //check access to private forum
            $accessToLastTopic = true;
            if ($forum['private']) {
                if (!$this->gadget->GetPermission('ForumManage', $forum['id'])) {
                    $accessToLastTopic = false;
                }
            }

            // last post
            if ($accessToLastTopic && !empty($forum['last_topic_id'])) {
                $tpl->SetBlock("$block/group/forum/lastpost");
                $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
                $tpl->SetVariable('username', $forum['username']);
                $tpl->SetVariable('nickname', $forum['nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $forum['username'])
                    )
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUMS_LASTPOST'));
                $tpl->SetVariable('lastpost_date', $objDate->Format($forum['last_post_time'], $date_format));
                $tpl->SetVariable('lastpost_date_iso', $objDate->ToISO((int)$forum['last_post_time']));
                $url_params = array('fid' => $forum['id'], 'tid'=> $forum['last_topic_id']);
                $last_post_page = floor(($forum['replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('lastpost_url', $this->gadget->urlMap('Posts', $url_params));
                $tpl->ParseBlock("$block/group/forum/lastpost");
            }

            $tpl->ParseBlock("$block/group/forum");
        }

        $tpl->ParseBlock("$block/group");
        if ($standalone) {
            $tpl->ParseBlock("$block");
            return $tpl->Get();
        }
    }

}