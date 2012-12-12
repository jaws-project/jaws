<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_RecentPosts extends Jaws_Gadget_HTML
{
    /**
     * Get RecentPosts action params
     *
     * @access  public
     * @return  array list of RecentPosts action params
     */
    function RecentPostsLayoutParams()
    {
        $result = array();
        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Groups');
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
     * Displays list of recent posts ordered by date
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  string  XHTML content
     */
    function RecentPosts($gid = '')
    {
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('RecentPosts.html');

        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Groups');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group)) {
            $group = array();
            $group['id']    = 0;
            $group['title'] = _t('FORUMS_GROUPS_ALL');
        }

        // recent posts limit
        $recent_limit = $this->GetRegistry('recent_limit');
        $recent_limit = empty($recent_limit)? 5 : (int)$recent_limit;

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $posts = $pModel->GetRecentPosts($group['id'], $recent_limit);
        if (!Jaws_Error::IsError($posts)) {
            // date format
            $date_format = $this->GetRegistry('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

            // posts per page
            $posts_limit = $this->GetRegistry('posts_limit');
            $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

            $max_size = 128;
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetBlock('recentposts');
            // title
            $tpl->SetVariable('action_title', _t('FORUMS_LAYOUT_RECENT_POSTS'));
            $tpl->SetVariable('group_title', $group['title']);

            foreach ($posts as $post) {
                $tpl->SetBlock('recentposts/post');

                // topic subject/link
                $tpl->SetVariable('lbl_topic', $post['subject']);
                $tpl->SetVariable(
                    'url_topic',
                    $this->GetURLFor(
                        'Posts',
                        array('fid' => $post['fid'], 'tid'=> $post['tid'])
                    )
                );

                // post author
                $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
                $tpl->SetVariable(
                    'message',
                    $GLOBALS['app']->UTF8->substr(
                        strip_tags($this->ParseText($post['message'], 'Forums', 'index')),
                        0,
                        $max_size
                    ). ' ...'
                );
                $tpl->SetVariable('lbl_postedby',_t('FORUMS_POSTEDBY'));
                $tpl->SetVariable('username', $post['username']);
                $tpl->SetVariable('nickname', $post['nickname']);

                // user's profile
                $tpl->SetVariable(
                    'url_user',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $post['username'])
                    )
                );

                // post url
                $url_params = array('fid' => $post['fid'], 'tid'=> $post['tid']);
                $last_post_page = floor(($post['topic_replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('url_post', $this->GetURLFor('Posts', $url_params));

                $tpl->ParseBlock('recentposts/post');
            }
            $tpl->ParseBlock('recentposts');
        }
        return $tpl->Get();
    }

}