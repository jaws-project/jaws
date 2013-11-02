<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_UserPosts extends Forums_Actions_Default
{
    /**
     * Displays list of user's posts ordered by date
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserPosts()
    {
        $rqst = jaws()->request->fetch(array('uid', 'page'), 'get');
        $uid = (int) $rqst['uid'];
        if(empty($uid)) {
            return;
        }

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $user = $userModel->GetUser($uid);

        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

        $tpl = $this->gadget->loadTemplate('UserPosts.html');
        $pModel = $this->gadget->loadModel('Posts');
        $posts = $pModel->GetUserPosts($uid, $posts_limit, ($page - 1) * $posts_limit);
        $post_counts = $pModel->GetUserPostsCount($uid);
        if (!Jaws_Error::IsError($posts)) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

            $max_size = 128;
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetBlock('userposts');

            // title
            $tpl->SetVariable('action_title', _t('FORUMS_USER_POSTS', $user['nickname']));

            foreach ($posts as $post) {
                $tpl->SetBlock('userposts/post');

                // topic subject/link
                $tpl->SetVariable('lbl_topic', $post['subject']);
                $tpl->SetVariable(
                    'url_topic',
                    $this->gadget->urlMap(
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
                        strip_tags($this->gadget->ParseText($post['message'], 'Forums', 'index')),
                        0,
                        $max_size
                    ). ' ...'
                );

                // post url
                $url_params = array('fid' => $post['fid'], 'tid'=> $post['tid']);
                $last_post_page = floor(($post['topic_replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('url_post', $this->gadget->urlMap('Posts', $url_params));

                $tpl->ParseBlock('userposts/post');
            }

            // page navigation
            $this->GetPagesNavigation(
                $tpl,
                'userposts',
                $page,
                $posts_limit,
                $post_counts,
                _t('FORUMS_POSTS_COUNT', $post_counts),
                'UserPosts',
                array('uid' => $uid)
            );

            $tpl->ParseBlock('userposts');
        }
        return $tpl->Get();
    }

}