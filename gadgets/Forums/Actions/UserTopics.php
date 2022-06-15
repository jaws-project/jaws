<?php
/**
 * Forums Gadget
 *
 * @category   Gadget
 * @package    Forums
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2012-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_UserTopics extends Jaws_Gadget_Action
{
    /**
     * Displays list of user's posts ordered by date
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserTopics()
    {
        $rqst = $this->gadget->request->fetch(array('user', 'page'), 'get');
        $user = $rqst['user'];
        if (empty($user)) {
            return false;
        }
        $user = Jaws_Gadget::getInstance('Users')->model->load('User')->get($user);

        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        // topics per page
        $limit = $this->gadget->registry->fetch('topics_limit');
        $limit = empty($limit)? 10 : (int)$limit;

        $tpl = $this->gadget->template->load('UserTopics.html');
        $tModel = $this->gadget->model->load('Topics');
        $topics = $tModel->GetUserTopics($user['id'], $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($topics)) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

            $max_size = 128;
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock('topics');

            $userURL = $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $user['username']));
            $tpl->SetVariable('index_title', $this::t('TOPICS'));
            $tpl->SetVariable('title', $user['nickname']);
            $tpl->SetVariable('url', $userURL);
            $tpl->SetVariable('lbl_topics', $this::t('TOPICS'));
            $tpl->SetVariable('lbl_replies', $this::t('REPLIES'));
            $tpl->SetVariable('lbl_views', $this::t('VIEWS'));
            $tpl->SetVariable('lbl_lastpost', $this::t('LASTPOST'));

            // posts per page
            $posts_limit = $this->gadget->registry->fetch('posts_limit');
            $posts_limit = empty($posts_limit) ? 10 : (int)$posts_limit;
            foreach ($topics as $topic) {
                $tpl->SetBlock('topics/topic');
                $tpl->SetVariable('lbl_forum', $this::t('FORUM'));
                $tpl->SetVariable('forum', $topic['title']);
                $tpl->SetVariable('forum_url', $this->gadget->urlMap('Topics', array('fid' => $topic['fid'])));
                $tpl->SetVariable('status', (int)$topic['locked']);
                $published_status = ((int)$topic['published'] === 1) ? 'published' : 'draft';
                $tpl->SetVariable('published_status', $published_status);
                $tpl->SetVariable('title', $topic['subject']);
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap('Posts', array('fid' => $topic['fid'], 'tid' => $topic['id']))
                );
                $tpl->SetVariable('replies', $topic['replies']);
                $tpl->SetVariable('views', $topic['views']);
                // first post
                $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
                $tpl->SetVariable('username', $user['username']);
                $tpl->SetVariable('nickname', $user['nickname']);
                $tpl->SetVariable('user_url', $userURL);
                $tpl->SetVariable('firstpost_date', $objDate->Format($topic['first_post_time'], $date_format));
                $tpl->SetVariable('firstpost_date_iso', $objDate->ToISO((int)$topic['first_post_time']));

                // last post
                if (!empty($topic['last_post_id'])) {
                    $tpl->SetBlock('topics/topic/lastpost');
                    $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
                    $tpl->SetVariable('username', $topic['last_username']);
                    $tpl->SetVariable('nickname', $topic['last_nickname']);
                    $tpl->SetVariable(
                        'user_url',
                        $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $topic['last_username']))
                    );
                    $tpl->SetVariable('lastpost_lbl',$this::t('LASTPOST'));
                    $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time'], $date_format));
                    $tpl->SetVariable('lastpost_date_iso', $objDate->ToISO((int)$topic['last_post_time']));
                    $url_params = array('fid' => $topic['fid'], 'tid'=> $topic['id']);
                    $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
                    if ($last_post_page > 1) {
                        $url_params['page'] = $last_post_page;
                    }
                    $tpl->SetVariable('lastpost_url', $this->gadget->urlMap('Posts', $url_params));
                    $tpl->ParseBlock('topics/topic/lastpost');
                }

                $tpl->ParseBlock('topics/topic');
            }

            $topicCounts = $tModel->GetUserTopicCount($user['id']);
            // Pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $limit,
                $topicCounts,
                'UserTopics',
                array('user' => $user['username']),
                $this::t('POSTS_COUNT', $topicCounts)
            );

            $tpl->ParseBlock('topics');
        }
        return $tpl->Get();
    }

}