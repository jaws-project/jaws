<?php
/**
 * Forums Core Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Forums extends ForumsHTML
{
    /**
     * Display groups and forums
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Forums()
    {
        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Groups');
        $groups = $gModel->GetGroups(true);
        if (Jaws_Error::IsError($groups) || empty($groups)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('Forums.html');
        $tpl->SetBlock('forums');

        $tpl->SetVariable('title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('url', $this->GetURLFor('Forums'));

        $posts_limit = $GLOBALS['app']->Registry->Get('/gadgets/Forums/posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;
        foreach ($groups as $group) {
            $tpl->SetBlock('forums/group');
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('lbl_topics', _t('FORUMS_TOPICS'));
            $tpl->SetVariable('lbl_posts', _t('FORUMS_POSTS'));
            $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));

            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
            $forums = $fModel->GetForums($group['id'], true, true);
            if (Jaws_Error::IsError($forums)) {
                continue;
            }

            foreach ($forums as $forum) {
                $tpl->SetBlock('forums/group/forum');
                $tpl->SetVariable('status', (int)$forum['locked']);
                $tpl->SetVariable('title', $forum['title']);
                $tpl->SetVariable('url', $this->GetURLFor('Topics', array('fid' => $forum['id'])));
                $tpl->SetVariable('description', $forum['description']);
                $tpl->SetVariable('topics', $forum['topics']);
                $tpl->SetVariable('posts', $forum['posts']);

                // last post
                if (!empty($forum['last_topic_id'])) {
                    $tpl->SetBlock('forums/group/forum/lastpost');
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
                    $tpl->SetVariable('lastpost_date', $objDate->Format($forum['last_post_time']));
                    $url_params = array('fid' => $forum['id'], 'tid'=> $forum['last_topic_id']);
                    $last_post_page = floor(($forum['replies'] - 1)/$posts_limit) + 1;
                    if ($last_post_page > 1) {
                        $url_params['page'] = $last_post_page;
                    }
                    $tpl->SetVariable('lastpost_url', $this->GetURLFor('Posts', $url_params));
                    $tpl->ParseBlock('forums/group/forum/lastpost');
                }

                $tpl->ParseBlock('forums/group/forum');
            }

            $tpl->ParseBlock('forums/group');
        }

        $tpl->ParseBlock('forums');
        return $tpl->Get();
    }

}