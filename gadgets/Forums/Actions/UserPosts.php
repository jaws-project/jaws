<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2012-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_UserPosts extends Jaws_Gadget_Action
{
    /**
     * Displays list of user's posts ordered by date
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserPosts()
    {
        $rqst = $this->gadget->request->fetch(array('user', 'page'), 'get');
        $user = $rqst['user'];
        if (empty($user)) {
            return false;
        }

        $user = Jaws_Gadget::getInstance('Users')->model->load('User')->get($user);
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

        $tpl = $this->gadget->template->load('UserPosts.html');
        $pModel = $this->gadget->model->load('Posts');
        $posts = $pModel->GetUserPosts($user['id'], $posts_limit, ($page - 1) * $posts_limit);
        if (!Jaws_Error::IsError($posts)) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'EEEE dd MMMM yyyy' : $date_format;

            $max_size = 128;
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock('userposts');

            // title
            $tpl->SetVariable('title', $this::t('USER_POSTS', $user['nickname']));

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
                    Jaws_UTF8::substr(
                        strip_tags($this->gadget->plugin->parse($post['message'])),
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

            $post_counts = $pModel->GetUserPostsCount($user['id']);
            // Pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $posts_limit,
                $post_counts,
                'UserPosts',
                array('user' => $user['username']),
                $this::t('POSTS_COUNT', $post_counts)
            );

            $tpl->ParseBlock('userposts');
        }
        return $tpl->Get();
    }

}