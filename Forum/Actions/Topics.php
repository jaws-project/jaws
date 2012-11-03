<?php
/**
 * Forum Gadget
 *
 * @category    Gadget
 * @package     Forum
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forum_Actions_Topics extends ForumHTML
{
    /**
     * Display forum's topics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topics()
    {
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('id', 'page'), 'get');

        $model = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topics = $model->GetTopics($get['id']);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('title', _t('FORUM_NAME'));
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Forum', 'Forums'));
        $tpl->SetVariable('lbl_topics', _t('FORUM_TIPICS'));
        $tpl->SetVariable('lbl_replies', _t('FORUM_REPLIES'));
        $tpl->SetVariable('lbl_views', _t('FORUM_VIEWS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUM_LASTPOST'));

        foreach ($topics as $topic) {
            $tpl->SetBlock('topics/topic');
            $tpl->SetVariable('icon', '');
            if ($topic['locked']) {
                $tpl->SetVariable('status', _t('FORUM_LOCKED'));
            }
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Forum',
                                                                     'Topic', array('tid' => $topic['id']))
            );
            $tpl->SetVariable('replies', $topic['replies']);
            $tpl->SetVariable('views', $topic['views']);

            // last post
            if (!empty($topic['last_post_id'])) {
                $tpl->SetBlock('topics/topic/lastpost');
                $tpl->SetVariable('postedby_lbl',_t('FORUM_POSTEDBY'));

                $tpl->SetVariable('username', $topic['username']);
                $tpl->SetVariable('nickname', $topic['nickname']);
                $tpl->SetVariable('user_url',
                                  $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUM_LASTPOSTED'));
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time']));
                $tpl->SetVariable('lastpost_url',
                                  $GLOBALS['app']->Map->GetURLFor('Forum',
                                                                  'Topic', array('id' => $topic['id']))
                );
                $tpl->ParseBlock('topics/topic/lastpost');
            }

            $tpl->ParseBlock('topics/topic');
        }

        $tpl->SetBlock('topics/actions');
        $tpl->SetVariable('lbl_newtopic', _t('FORUM_NEWTOPIC'));
        $tpl->SetVariable('url_newtopic',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'NewTopic',
                                                          array('fid' => $get['id']))
        );
        $tpl->ParseBlock('topics/actions');

        $tpl->ParseBlock('topics');
        return $tpl->Get();
    }

    /**
     * Show new topic form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function NewTopic()
    {
        $request =& Jaws_Request::getInstance();
        $req = $request->get(array('subject', 'description', 'fid'));
        if (empty($req['fid'])) {
            return false;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Forums');
        $forum = $fModel->GetForum($req['fid']);
        if (Jaws_Error::IsError($forum) || empty($forum)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('NewTopic.html');
        $tpl->SetBlock('newtopic');

        $tpl->SetVariable('lbl_forum', $forum['title']);
        $tpl->SetVariable('url_forum',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'Topics',
                                                          array('id' => $forum['id']))
        );
        $tpl->SetVariable('title', _t('FORUM_TOPIC_ADD_TITLE'));
        $tpl->SetVariable('separator', _t('FORUM_SEPARATOR'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('fid', $req['fid']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('newtopic/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('newtopic/response');
        }

        $tpl->SetVariable('lbl_subject', _t('FORUM_TOPIC_SUBJECT'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('newtopic', _t('FORUM_TOPIC_ADD_BUTTON'));
        $tpl->SetVariable('subject', '');
        $tpl->SetVariable('description', '');

        $tpl->ParseBlock('newtopic');
        return $tpl->Get();
    }

    /**
     * Add/Edit a topic
     *
     * @access  public
     */
    function UpdateTopic()
    {
        $request =& Jaws_Request::getInstance();
        $topic = $request->get(array('fid', 'tid', 'subject', 'fast_url', 'description', 'published'),
                               'post');

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        if (empty($topic['tid'])) {
            $tid = $tModel->InsertTopic($GLOBALS['app']->Session->GetAttribute('user'),
                                        $topic['fid'],
                                        $topic['subject'],
                                        $topic['fast_url'],
                                        $topic['description'],
                                        $topic['published']);
        }

        if (Jaws_Error::IsError($tid)) {
            $GLOBALS['app']->Session->PushSimpleResponse($apid->getMessage(),
                                                         'Topic');
        } else {
            $topic['tid'] = $tid;
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUM_TOPIC_UPDATED'),
                                                         'Topic');
        }

        Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum',
                                                              'Topic',
                                                              array('tid' => $topic['tid'])), true);
    }

    /**
     * Display topic's posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topic()
    {
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('tid', 'page'), 'get');

        $model = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topic = $model->GetTopic($get['tid']);
        if (Jaws_Error::IsError($topic)) {
            return false;
        }
        $model->UpdateTopicViews($topic['id']);

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('Topic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('title', $topic['subject']);

        $pModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Posts');
        $posts = $pModel->GetPosts($get['tid']);
        if (Jaws_Error::IsError($posts)) {
            return false;
        }
        $objDate = $GLOBALS['app']->loadDate();
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser = new Jaws_User;

        foreach ($posts as $post) {
            $tpl->SetBlock('topic/post');
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable('user_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $post['username'])));
            $tpl->SetVariable('posts_count', $pModel->GetUserPostsCount($post['uid']));
            $tpl->SetVariable('joined_time', $objDate->Format($post['user_joined_time']));
            $tpl->SetVariable('createtime', $objDate->Format($post['createtime']));
            //
            $tpl->SetVariable('posts_lbl',_t('FORUM_USER_POST_COUNT'));
            $tpl->SetVariable('joined_lbl',_t('FORUM_USER_JOINED_TIME'));
            $tpl->SetVariable('postedby_lbl',_t('FORUM_POSTED_BY'));
            //
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('message', $post['message']);
            if ($post['last_update_uid'] != 0) {
                $userInfo = $jUser->GetUser((int)$post['last_update_uid']);
                $tpl->SetBlock('topic/post/update');
                $tpl->SetVariable('updatedby_lbl', _t('FORUM_POST_UPDATEDBY'));
                if (!empty($userInfo)) {
                    $tpl->SetVariable('username', $userInfo['username']);
                    $tpl->SetVariable('nickname', $userInfo['nickname']);
                }
                $tpl->SetVariable('user_url', $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $userInfo['username'])));
                $tpl->SetVariable('update_reason', $post['last_update_reason']);
                $tpl->SetVariable('update_time', $objDate->Format($post['last_update_time']));
                $tpl->ParseBlock('topic/post/update');
            }
            // Check User Can Edit Posts
            $tpl->SetBlock('topic/post/actions');
            $tpl->SetVariable('lbl_editpost',_t('GLOBAL_EDIT'));
            $tpl->SetVariable('url_editpost', $GLOBALS['app']->Map->GetURLFor('Forum', 'EditPost', array('pid' => $post['id'])));
            $tpl->SetVariable('lbl_deletepost',_t('GLOBAL_DELETE'));
            $tpl->SetVariable('url_deletepost', $GLOBALS['app']->Map->GetURLFor('Forum', 'DeletePost', array('pid' => $post['id'])));
            $tpl->ParseBlock('topic/post/actions');

            $tpl->ParseBlock('topic/post');
        }

        $tpl->SetBlock('topic/actions');
        $tpl->SetVariable('lbl_newpost', _t('FORUM_NEWPOST'));
        $tpl->SetVariable('url_newpost',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'NewPost',
                                                          array('tid' => $get['tid']))
        );
        if ($topic['locked']) {
            $tpl->SetVariable('lbl_lock_topic', _t('FORUM_UNLOCK_TOPIC'));
        } else {
            $tpl->SetVariable('lbl_lock_topic', _t('FORUM_LOCK_TOPIC'));
        }
        $tpl->SetVariable('url_lock_topic',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'LockTopic',
                                                          array('tid' => $get['tid']))
        );
        $tpl->ParseBlock('topic/actions');

        $tpl->ParseBlock('topic');
        return $tpl->Get();
    }
    /**
     * Locked a topic
     *
     * @access  public
     */
    function LockTopic()
    {
        $request =& Jaws_Request::getInstance();
        $topic = $request->get(array('tid'));

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topicInfo = $tModel->GetTopic((int)($topic['tid']));
        if (Jaws_Error::IsError($topicInfo) || empty($topicInfo)) {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUM_TOPIC_NOT_FOUND'), 'Topic');
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum', 'Topic',
                                                              array('tid' => $topic['tid'])), true);
        }

        $result = $tModel->LockTopic($topicInfo['id'], !$topicInfo['locked']);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($apid->getMessage(), 'Topic');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUM_TOPIC_LOCKED'), 'Topic');
        }

        Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum', 'Topic',
                                                              array('tid' => $topicInfo['id'])), true);
    }

}