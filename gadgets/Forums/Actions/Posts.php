<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Posts extends Jaws_Gadget_Action
{
    /**
     * Display topic posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Posts()
    {
        $this->AjaxMe('index.js');
        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            return Jaws_HTTPError::Get($topic->getCode());
        }
        if (empty($topic)) {
            return Jaws_HTTPError::Get(404);
        }

        if (!$topic['published'] || $topic['private']) {
            $logged_user = (int)$this->app->session->user->id;
            if ($logged_user != $topic['first_post_uid'] &&
                !$this->gadget->GetPermission('ForumManage', $rqst['fid'])
            ) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $limit = (int)$this->gadget->registry->fetch('posts_limit');
        $pModel = $this->gadget->model->load('Posts');
        $posts = $pModel->GetPosts($rqst['tid'], $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($posts)) {
            return false;
        }

        $res = $tModel->UpdateTopicViews($topic['id']);
        if (Jaws_Error::IsError($res)) {
            // do nothing
        }

        $tpl = $this->gadget->template->load('Posts.html');
        $tpl->SetBlock('posts');

        $tpl->SetVariable('findex_title', $this::t('FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable('forum_url', $this->gadget->urlMap('Topics', array('fid' => $topic['fid'])));
        $tpl->SetVariable('title', $topic['subject']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Posts', array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])));

        // display subscription if installed
        if (Jaws_Gadget::IsGadgetInstalled('Subscription')) {
            $sHTML = Jaws_Gadget::getInstance('Subscription')->action->load('Subscription');
            $tpl->SetVariable('subscription', $sHTML->ShowSubscription('Forums', 'Topic', $rqst['tid']));
        }

        // date format
        $date_format = $this->gadget->registry->fetch('date_format');
        $date_format = empty($date_format)? 'EEEE dd MMMM yyyy' : $date_format;

        // edit max/min limit time
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

        $objDate = Jaws_Date::getInstance();
        $startPostNumber = $limit * ($page - 1);
        $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
        foreach ($posts as $pnum => $post) {
            $tpl->SetBlock('posts/post');
            $startPostNumber ++;
            $tpl->SetVariable('post_number', $startPostNumber);
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('posts_count_lbl',$this::t('USERS_POSTS_COUNT'));
            $tpl->SetVariable('registered_date_lbl',$this::t('USERS_REGISTERED_DATE'));
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('posts_count', $pModel->GetUserPostsCount($post['uid']));
            $tpl->SetVariable(
                'user_posts',
                $this->gadget->urlMap('UserPosts', array('user' => $post['username']))
            );
            $tpl->SetVariable('registered_date', $objDate->Format($post['user_registered_date'], 'd MMMM yyyy'));
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            $tpl->SetVariable('post_id',  $post['id']);
            $tpl->SetVariable(
                'message', 
                $this->gadget->plugin->parse(
                    $post['message'],
                    Jaws_Plugin::PLUGIN_TYPE_ALLTYPES,
                    $post['id'],
                    'Post'
                )
            );
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            // user's avatar
            $tpl->SetVariable(
                'avatar',
                Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $post['username']))
            );

            // user's profile
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL(
                    'Users',
                    'Profile',
                    array('user' => $post['username'])
                )
            );

            // attachment
            Jaws_Gadget::getInstance('Files')->action->load('Files')->displayReferenceFiles(
                $tpl,
                array(
                    'gadget' => $this->gadget->name,
                    'action' => 'Post',
                    'reference' => $post['id']
                ),
                array(
                    'labels' => array(
                        'title' => $this::t('POSTS_ATTACHMENT')
                    )
                )
            );

            // update information
            if ($post['update_uid'] != 0) {
                $tpl->SetBlock('posts/post/update');
                $tpl->SetVariable('updatedby_lbl', $this::t('POSTS_UPDATEDBY'));
                $tpl->SetVariable('username', $post['updater_username']);
                $tpl->SetVariable('nickname', $post['updater_nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $this->app->map->GetMappedURL(
                        'Users',
                        'Profile',
                        array('user' => $post['updater_username'])
                    )
                );
                $tpl->SetVariable('update_time', $objDate->Format($post['update_time'], $date_format));
                $tpl->SetVariable('update_time_iso', $objDate->ToISO($post['update_time']));
                if (!empty($post['update_reason'])) {
                    $tpl->SetBlock('posts/post/update/reason');
                    $tpl->SetVariable('lbl_update_reason', $this::t('POSTS_EDIT_REASON'));
                    $tpl->SetVariable('update_reason', $post['update_reason']);
                    $tpl->ParseBlock('posts/post/update/reason');
                }
                $tpl->ParseBlock('posts/post/update');
            }

            // reply: check permission for add post
            if ($this->gadget->GetPermission('AddPost') && (!$topic['locked'] || $forumManage)) {
                $tpl->SetBlock('posts/post/replyPostAction');
                $tpl->SetVariable('lbl_reply_post', $this::t('POSTS_REPLY'));
                $tpl->SetVariable('pid', $post['id']);
                $tpl->ParseBlock('posts/post/replyPostAction');

//                $tpl->SetBlock('posts/post/action');
//                $tpl->SetVariable('action_lbl',$this::t('POSTS_REPLY'));
//                $tpl->SetVariable('action_title',$this::t('POSTS_REPLY_TITLE'));
//                $tpl->SetVariable(
//                    'action_url',
//                    $this->gadget->urlMap(
//                        'ReplyPost',
//                        array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
//                    )
//                );
//                $tpl->ParseBlock('posts/post/action');
            }

            if ($topic['first_post_id'] == $post['id']) {
                // check permission for edit topic
                if ($this->gadget->GetPermission('EditTopic') &&
                    ($post['uid'] == (int)$this->app->session->user->id || $forumManage) &&
                    (!$topic['locked'] || $forumManage) &&
                    ((time() - $post['insert_time']) <= $edit_max_limit_time || $forumManage)
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',$this::t('TOPICS_EDIT'));
                    $tpl->SetVariable('action_title',$this::t('TOPICS_EDIT_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->gadget->urlMap(
                            'EditTopic',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }

                // check permission for delete topic
                if ($this->gadget->GetPermission('DeleteTopic') &&
                    ($post['uid'] == (int)$this->app->session->user->id || $forumManage) &&
                    ((time() - $post['insert_time']) <= $edit_min_limit_time || $forumManage)
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',$this::t('TOPICS_DELETE'));
                    $tpl->SetVariable('action_title',$this::t('TOPICS_DELETE_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->gadget->urlMap(
                            'DeleteTopic',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }
            } else {
                // check permission for edit post
                if ($this->gadget->GetPermission('EditPost') &&
                    ($post['uid'] == (int)$this->app->session->user->id || $forumManage) &&
                    (!$topic['locked'] || $forumManage) &&
                    ((time() - $post['insert_time']) <= $edit_max_limit_time || $forumManage)
                ) {
                    $tpl->SetBlock('posts/post/editPostAction');
                    $tpl->SetVariable('lbl_edit_post', $this::t('POSTS_EDIT'));
                    $tpl->SetVariable('pid', $post['id']);
                    $tpl->ParseBlock('posts/post/editPostAction');
                }

                // check permission for delete post
                if ($this->gadget->GetPermission('DeletePost') &&
                    ($post['uid'] == (int)$this->app->session->user->id || $forumManage) &&
                    (!$topic['locked'] || $forumManage) &&
                    ((time() - $post['insert_time']) <= $edit_min_limit_time || $forumManage)
                ){
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',$this::t('POSTS_DELETE'));
                    $tpl->SetVariable('action_title',$this::t('POSTS_DELETE_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->gadget->urlMap(
                            'DeletePost',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }
            }

            $tpl->ParseBlock('posts/post');
        } // foreach posts

        // Pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $limit,
            $topic['replies'],
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            $this::t('POSTS_COUNT', $topic['replies'])
        );

        // check permission to add new post
        if ($this->gadget->GetPermission('AddPost') && (!$topic['locked'] || $forumManage)) {
            $tpl->SetBlock('posts/newPostAction');
            $tpl->SetVariable('lbl_new_post', $this::t('POSTS_NEW'));
            $tpl->ParseBlock('posts/newPostAction');

            // display post UI
            $tpl->SetBlock('posts/postUI');
            $tpl->SetVariable('post_ui', $this->GetPostUI($rqst['fid'], $rqst['tid']));
            $tpl->ParseBlock('posts/postUI');
        }

        // check permission to lock/unlock topic
        if ($forumManage) {
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable(
                'action_lbl',
                $topic['locked']? $this::t('TOPICS_UNLOCK') : $this::t('TOPICS_LOCK')
            );
            $tpl->SetVariable(
                'action_url',
                $this->gadget->urlMap('LockTopic', array('fid' => $rqst['fid'], 'tid' => $rqst['tid']))
            );
            $tpl->ParseBlock('posts/action');
        }

        // check permission to publish/draft topic
        if ($this->gadget->GetPermission('PublishTopic') && $forumManage){
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable(
                'action_lbl',
                $topic['published']? $this::t('TOPICS_DRAFT') : $this::t('TOPICS_PUBLISH')
            );
            $tpl->SetVariable(
                'action_url',
                $this->gadget->urlMap('PublishTopic', array('fid' => $rqst['fid'], 'tid' => $rqst['tid']))
            );
            $tpl->ParseBlock('posts/action');
        }

        $tpl->ParseBlock('posts');
        return $tpl->Get();
    }

    /**
     * return post UI
     *
     * @access  public
     * @param   int     $fid    Forum id
     * @param   int     $tid    Topic id
     * @return  string  XHTML template content
     */
    function GetPostUI($fid, $tid)
    {
        $tpl = $this->gadget->template->load('Posts.html');
        $tpl->SetBlock('post');
        $tpl->SetVariable('fid', $fid);
        $tpl->SetVariable('tid', $tid);

        /*
        if (!empty($post['id'])) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'EEEE dd MMMM yyyy' : $date_format;
            // post meta data
            $tpl->SetBlock('post/post_meta');
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $post['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            $tpl->ParseBlock('post/post_meta');
        }
        */

        // message
        $tpl->SetVariable('lbl_message', $this::t('POSTS_MESSAGE'));
        $message = $this->gadget->action->load('Editor')->load('message', '');
        $message->setId('message');
        $message->TextArea->SetRows(8);
        $tpl->SetVariable('message', $message->Get());

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment')
        ) {
            Jaws_Gadget::getInstance('Files')->action->load('Files')->loadReferenceFiles(
                $tpl,
                array(
                    'gadget' => $this->gadget->name,
                    'action' => 'Post',
                    'reference' => 0
                ),
                array(
                    'labels' => array(
                        'title'  => $this::t('POSTS_ATTACHMENT'),
                        'browse' => $this::t('POSTS_EXTRA_ATTACHMENT'),
                        'remove' => $this::t('POSTS_ATTACHMENT_REMOVE')
                    )
                )
            );
        }

        $tpl->SetVariable('lbl_update_reason', $this::t('POSTS_EDIT_REASON'));
        $tpl->SetVariable('lbl_send_notification', $this::t('NOTIFICATION_MESSAGE'));

        // display captcha
        $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $htmlPolicy->loadCaptcha($tpl);

        // buttons
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));

        $tpl->ParseBlock('post');
        return $tpl->Get();
    }

    /**
     * Get a post info
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetPost()
    {
        $post = $this->gadget->request->fetch(array('pid'), 'post');
        return $this->gadget->model->load('Posts')->GetPost($post['pid']);
    }

    /**
     * Add/Edit a post
     *
     * @access  public
     */
    function UpdatePost()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(
            array('fid', 'tid', 'pid', 'subject', 'message', 'update_reason', 'notification'),
            'post'
        );
        if (empty($post['fid']) || !$this->gadget->GetPermission('ForumPublic', $post['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($post['message'])) {
            $this->gadget->session->push(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                RESPONSE_ERROR,
                'UpdatePost',
                $post
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check captcha only in new post action
        if (empty($post['pid'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha();
            if (Jaws_Error::IsError($resCheck)) {
                $this->gadget->session->push(
                    $resCheck->getMessage(),
                    RESPONSE_ERROR,
                    'UpdatePost',
                    $post
                );
                Jaws_Header::Referrer();
            }
        }

        $tModel = $this->gadget->model->load('Topics');
        $topic  = $tModel->GetTopic($post['tid'], $post['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $send_notification =
            $this->gadget->GetPermission('ForumManage', $post['fid'])? (bool)$post['notification'] : true;
        // edit min/max limit time
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

        $pModel = $this->gadget->model->load('Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost(
                $this->app->session->user->id,
                $post['tid'],
                $post['fid'],
                $post['message'],
                $post['message']
            );
            $event_type = 'new';
            $error_message = $this::t('POSTS_NEW_ERROR');
            $last_post_page = floor($topic['replies']/$posts_limit) + 1;
        } else {
            $oldPost = $pModel->GetPost($post['pid'], $post['tid'], $post['fid']);
            if (Jaws_Error::IsError($oldPost) || empty($oldPost)) {
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            // check edit permissions
            $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
            $update_uid = (int)$this->app->session->user->id;
            if ((!$this->gadget->GetPermission('EditPost')) ||
                ($oldPost['uid'] != $update_uid && !$forumManage) ||
                ($topic['locked'] && !$forumManage) ||
                ((time() - $oldPost['insert_time']) > $edit_max_limit_time && !$forumManage)
            ) {
                return Jaws_HTTPError::Get(403);
            }

            if ((time() - $oldPost['insert_time']) <= $edit_min_limit_time) {
                $update_uid = 0;
                $send_notification = false;
                $post['update_reason'] = '';
            }

            $result = $pModel->UpdatePost(
                $post['pid'],
                $update_uid,
                $post['message'],
                $post['update_reason']
            );
            $event_type = 'edit';
            // no notification for topic creator
            $topic['email'] = '';
            $error_message = $this::t('POSTS_EDIT_ERROR');
            $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
        }

        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $error_message,
                RESPONSE_ERROR,
                'UpdatePost',
                $post
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment')
        ) {
            Jaws_Gadget::getInstance('Files')->action->load('Files')->uploadReferenceFiles(
                array(
                    'gadget' => $this->gadget->name,
                    'action' => 'Post',
                    'reference' => $result
                )
            );
        }

        $post['pid'] = $result;
        $url_params = array('fid' => $post['fid'], 'tid' => $post['tid']);
        if ($last_post_page > 1) {
            $url_params['page'] = $last_post_page;
        }
        $post_link = $this->gadget->urlMap('Posts', $url_params, array('absolute' => true));

        // send email notification
        if ($send_notification) {
            $result = $pModel->PostNotification(
                $topic['email'],
                $event_type,
                $topic['forum_title'],
                $post_link,
                $topic['subject'],
                $this->gadget->plugin->parse($post['message'])
            );
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
        }

        // redirect to topic posts page
        return Jaws_Header::Location($post_link);
    }

    /**
     * Delete a post
     *
     * @access  public
     */
    function DeletePost()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'pid', 'delete_reason', 'notification', 'confirm'));
        $pModel = $this->gadget->model->load('Posts');
        $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($post) || empty($post) || $post['id'] == $post['topic_first_post_id']) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $topic_link = $this->gadget->urlMap(
                'Posts',
                array('fid' => $post['fid'], 'tid' => $post['tid']),
                array('absolute' => true)
            );

            if (!is_null($rqst['confirm'])) {
                // delete min limit time
                $delete_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

                // check delete permissions
                $forumManage = $this->gadget->GetPermission('ForumManage', $post['fid']);
                if ((!$this->gadget->GetPermission('DeletePost')) ||
                    ($post['uid'] != (int)$this->app->session->user->id && !$forumManage) ||
                    ($post['topic_locked'] && !$forumManage) ||
                    ((time() - $post['insert_time']) > $delete_limit_time && !$forumManage)
                ) {
                    return Jaws_HTTPError::Get(403);
                }

                $result = $pModel->DeletePost(
                    $post['id'],
                    $post['tid'],
                    $post['fid']
                );
                if (Jaws_Error::IsError($result)) {
                    $this->gadget->session->push(
                        $this::t('POSTS_DELETE_ERROR'),
                        RESPONSE_NOTICE,
                        'DeletePost'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $send_notification =
                    $this->gadget->GetPermission('ForumManage', $post['fid'])? (bool)$rqst['notification'] : true;
                // send delete notification
                if ($send_notification) {
                    $result = $pModel->PostNotification(
                        '',         // Topic creator's email
                        'delete',   // event_type
                        $post['forum_title'],
                        $topic_link,
                        $post['subject'],
                        $this->gadget->plugin->parse($post['message']),
                        $this->gadget->plugin->parse($rqst['delete_reason'])
                    );
                    if (Jaws_Error::IsError($result)) {
                        // do nothing
                    }
                }
            }

            // redirect to topic posts list
            return Jaws_Header::Location($topic_link);
        } else {
            $tpl = $this->gadget->template->load('DeletePost.html');
            $tpl->SetBlock('post');

            $tpl->SetVariable('fid', $post['fid']);
            $tpl->SetVariable('tid', $post['tid']);
            $tpl->SetVariable('pid', $post['id']);
            $tpl->SetVariable('findex_title', $this::t('FORUMS'));
            $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
            $tpl->SetVariable('forum_title', $post['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->gadget->urlMap('Topics', array('fid'=> $post['fid']))
            );
            $tpl->SetVariable('topic_title', $post['subject']);
            $tpl->SetVariable(
                'topic_url',
                $this->gadget->urlMap('Posts', array('fid'=> $post['fid'], 'tid' => $post['tid']))
            );
            $tpl->SetVariable('title', $this::t('POSTS_DELETE_TITLE'));

            // error response
            if ($response = $this->gadget->session->pop('DeletePost')) {
                $tpl->SetVariable('response_type', $response['type']);
                $tpl->SetVariable('response_text', $response['text']);
            }

            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'EEEE dd MMMM yyyy' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $post['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            
            // message
            $tpl->SetVariable('message', $this->gadget->plugin->parseAdmin($post['message']));

            // delete reason
            $tpl->SetVariable('lbl_delete_reason', $this::t('POSTS_DELETE_REASON'));

            // notification
            if ($this->gadget->GetPermission('ForumManage', $post['fid'])) {
                $tpl->SetBlock('post/notification');
                $tpl->SetVariable('lbl_send_notification', $this::t('NOTIFICATION_MESSAGE'));
                $tpl->SetBlock('post/notification/checked');
                $tpl->ParseBlock('post/notification/checked');
                $tpl->ParseBlock('post/notification');
            }

            $tpl->SetVariable('btn_submit_title', $this::t('POSTS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', Jaws::t('CANCEL'));
            $tpl->ParseBlock('post');
            return $tpl->Get();
        }
    }

}