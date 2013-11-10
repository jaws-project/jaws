<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_RecentTopics extends Jaws_Gadget_Action
{
    /**
     * Get RecentTopics action params
     *
     * @access  public
     * @return  array list of RecentTopics action params
     */
    function RecentTopicsLayoutParams()
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
     * Displays list of recent updated topics ordered by date
     *
     * @access  public
     * @param   mixed   $gid Group ID
     * @return  string  XHTML content
     */
    function RecentTopics($gid = '')
    {
        $gModel = $this->gadget->model->load('Groups');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group)) {
            $group = array();
            $group['id']    = 0;
            $group['title'] = _t('FORUMS_GROUPS_ALL');
        }

        // recent posts limit
        $recent_limit = $this->gadget->registry->fetch('recent_limit');
        $recent_limit = empty($recent_limit)? 5 : (int)$recent_limit;

        $tpl = $this->gadget->template->load('RecentTopics.html');
        $tModel = $this->gadget->model->load('Topics');
        $topics = $tModel->GetRecentTopics($group['id'], $recent_limit);
        if (!Jaws_Error::IsError($topics)) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

            // posts per page
            $posts_limit = $this->gadget->registry->fetch('posts_limit');
            $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

            $max_size = 128;
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock('recenttopics');
            // title
            $tpl->SetVariable('action_title', _t('FORUMS_LAYOUT_RECENT_POSTS'));
            $tpl->SetVariable('group_title', $group['title']);

            foreach ($topics as $topic) {
                $tpl->SetBlock('recenttopics/topic');

                // topic subject/link
                $tpl->SetVariable('lbl_topic', $topic['subject']);
                $tpl->SetVariable(
                    'url_topic',
                    $this->gadget->urlMap(
                        'Posts',
                        array('fid' => $topic['fid'], 'tid'=> $topic['id'])
                    )
                );

                // post author
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time'], $date_format));
                $tpl->SetVariable('lastpost_date_iso', $objDate->ToISO((int)$topic['last_post_time']));
                $tpl->SetVariable(
                    'message',
                    $GLOBALS['app']->UTF8->substr(
                        strip_tags($this->gadget->ParseText($topic['message'], 'Forums', 'index')),
                        0,
                        $max_size
                    ). ' ...'
                );
                $tpl->SetVariable('lbl_postedby',_t('FORUMS_POSTEDBY'));
                $tpl->SetVariable('username', $topic['username']);
                $tpl->SetVariable('nickname', $topic['nickname']);

                // user's profile
                $tpl->SetVariable(
                    'url_user',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $topic['username'])
                    )
                );

                // post url
                $url_params = array('fid' => $topic['fid'], 'tid'=> $topic['id']);
                $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('lastpost_url', $this->gadget->urlMap('Posts', $url_params));

                $tpl->ParseBlock('recenttopics/topic');
            }
            $tpl->ParseBlock('recenttopics');
        }

        return $tpl->Get();
    }

}