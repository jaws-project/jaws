<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Posts extends ForumsHTML
{
    /**
     * Display topic posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Posts()
    {
        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        $limit = (int)$GLOBALS['app']->Registry->Get('/gadgets/Forums/posts_limit');
        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $posts = $pModel->GetPosts($rqst['tid'], $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($posts)) {
            return false;
        }

        $res = $tModel->UpdateTopicViews($topic['id']);
        if (Jaws_Error::IsError($res)) {
            // do nothing
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('Posts.html');
        $tpl->SetBlock('posts');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable('forum_url', $this->GetURLFor('Topics', array('fid' => $topic['fid'])));
        $tpl->SetVariable('title', $topic['subject']);
        $tpl->SetVariable('url', $this->GetURLFor('Posts', array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])));

        // date format
        $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        $objDate = $GLOBALS['app']->loadDate();
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        foreach ($posts as $pnum => $post) {
            $tpl->SetBlock('posts/post');
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('posts_count_lbl',_t('FORUMS_USERS_POSTS_COUNT'));
            $tpl->SetVariable('registered_date_lbl',_t('FORUMS_USERS_REGISTERED_DATE'));
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('posts_count', $pModel->GetUserPostsCount($post['uid']));
            $tpl->SetVariable('registered_date', $objDate->Format($post['user_registered_date'], 'd MN Y'));
            $tpl->SetVariable('createtime', $objDate->Format($post['createtime'], $date_format));
            $tpl->SetVariable('createtime_iso', $objDate->ToISO($post['createtime']));
            $tpl->SetVariable('message',  $this->ParseText($post['message'], 'Forums'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            // user's avatar
            $tpl->SetVariable(
                'avatar',
                $usrModel->GetAvatar(
                    $post['avatar'],
                    $post['email'],
                    80,
                    $post['user_last_update']
                )
            );

            // user's profile
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'Profile',
                    array('user' => $post['username'])
                )
            );

            // update information
            if ($post['last_update_uid'] != 0) {
                $tpl->SetBlock('posts/post/update');
                $tpl->SetVariable('updatedby_lbl', _t('FORUMS_POSTS_UPDATEDBY'));
                $tpl->SetVariable('username', $post['username']);
                $tpl->SetVariable('nickname', $post['nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $post['username'])
                    )
                );
                $tpl->SetVariable('update_time', $objDate->Format($post['last_update_time'], $date_format));
                $tpl->SetVariable('update_time_iso', $objDate->ToISO($post['last_update_time']));
                if (!empty($post['last_update_reason'])) {
                    $tpl->SetBlock('posts/post/update/reason');
                    $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
                    $tpl->SetVariable('update_reason', $post['last_update_reason']);
                    $tpl->ParseBlock('posts/post/update/reason');
                }
                $tpl->ParseBlock('posts/post/update');
            }

            if ($topic['first_post_id'] == $post['id']) {
                // check permission for edit topic
                if ($this->GetPermission('EditTopic') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     $this->GetPermission('EditOthersTopic')
                    ) &&
                    (!$topic['locked'] || $this->GetPermission('EditLockedTopic'))
                ){
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_TOPICS_EDIT'));
                    $tpl->SetVariable('action_title',_t('FORUMS_TOPICS_EDIT_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->GetURLFor(
                            'EditTopic',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }

                // check permission for delete topic
                if ($this->GetPermission('DeleteTopic') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     $this->GetPermission('DeleteOthersTopic')
                    )
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_TOPICS_DELETE'));
                    $tpl->SetVariable('action_title',_t('FORUMS_TOPICS_DELETE_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->GetURLFor(
                            'DeleteTopic',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }
            } else {
                // check permission for edit post
                if ($this->GetPermission('EditPost') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     $this->GetPermission('EditOthersPost')
                    ) &&
                    (!$topic['locked'] || $this->GetPermission('EditPostInLockedTopic'))
                ){
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_POSTS_EDIT'));
                    $tpl->SetVariable('action_title',_t('FORUMS_POSTS_EDIT_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->GetURLFor(
                            'EditPost',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }

                // check permission for delete post
                if ($this->GetPermission('DeletePost') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     $this->GetPermission('DeleteOthersPost')
                    ) &&
                    (!$topic['locked'] || $this->GetPermission('DeletePostInLockedTopic'))
                ){
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_POSTS_DELETE'));
                    $tpl->SetVariable('action_title',_t('FORUMS_POSTS_DELETE_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->GetURLFor(
                            'DeletePost',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }
            }

            $tpl->ParseBlock('posts/post');
        } // foreach posts

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'posts',
            $page,
            $limit,
            $topic['replies'],
            _t('FORUMS_POSTS_COUNT', $topic['replies']),
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id'])
        );

        // check permission to add new post
        if ($this->GetPermission('AddPost') &&
            (!$topic['locked'] || $this->GetPermission('AddPostToLockedTopic'))
        ){
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable('action_lbl', _t('FORUMS_POSTS_NEW'));
            $tpl->SetVariable(
                'action_url',
                $this->GetURLFor('NewPost', array('fid' => $rqst['fid'], 'tid' => $rqst['tid']))
            );
            $tpl->ParseBlock('posts/action');
        }

        // check permission to lock/unlock topic
        if ($this->GetPermission('LockTopic')){
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable(
                'action_lbl',
                $topic['locked']? _t('FORUMS_TOPICS_UNLOCK') : _t('FORUMS_TOPICS_LOCK')
            );
            $tpl->SetVariable(
                'action_url',
                $this->GetURLFor('LockTopic', array('fid' => $rqst['fid'], 'tid' => $rqst['tid']))
            );
            $tpl->ParseBlock('posts/action');
        }

        $tpl->ParseBlock('posts');
        return $tpl->Get();
    }

    /**
     * Show new post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function NewPost()
    {
        return $this->EditPost();
    }

    /**
     * Show edit post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditPost()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid', 'pid'), 'get');
        if (empty($rqst['fid']) || empty($rqst['tid'])) {
            return false;
        }

        if (empty($rqst['pid'])) {
            $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
            $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($topic) || empty($topic)) {
                return false;
            }

            $post = array();
            $post['id']  = 0;
            $post['fid'] = $topic['fid'];
            $post['tid'] = $topic['id'];
            $post['forum_title'] = $topic['forum_title'];
            $post['subject'] = $topic['subject'];
            $post['message'] = '';
            $post['last_update_reason'] = '';
            $title = _t('FORUMS_POSTS_NEW_TITLE');
            $btn_title = _t('FORUMS_POSTS_NEW_BUTTON');
        } else {
            $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
            $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($post) || empty($post)) {
                return false;
            }

            $title = _t('FORUMS_POSTS_EDIT_TITLE');
            $btn_title = _t('FORUMS_POSTS_EDIT_BUTTON');
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditPost.html');
        $tpl->SetBlock('post');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
        $tpl->SetVariable('forum_title', $post['forum_title']);
        $tpl->SetVariable(
            'forum_url',
            $this->GetURLFor('Topics', array('fid'=> $post['fid']))
        );
        $tpl->SetVariable('topic_title', $post['subject']);
        $tpl->SetVariable(
            'topic_url',
            $this->GetURLFor('Posts', array('fid' => $post['fid'], 'tid' => $post['tid']))
        );
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $post['fid']);
        $tpl->SetVariable('tid', $post['tid']);
        $tpl->SetVariable('pid', $post['id']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('UpdatePost')) {
            $tpl->SetBlock('post/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('post/response');
        }

        if (!empty($post['id'])) {
            // date format
            $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetBlock('post/post_meta');
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $post['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('createtime', $objDate->Format($post['createtime'], $date_format));
            $tpl->SetVariable('createtime_iso', $objDate->ToISO($post['createtime']));
            $tpl->ParseBlock('post/post_meta');
        }

        // message
        $tpl->SetVariable('message', $post['message']);
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));

        // attachment
        if ($this->GetPermission('AddPostAttachment')) {
            $tpl->SetBlock('post/attachment');
            $tpl->SetVariable('lbl_attachment',_t('FORUMS_POSTS_ATTACHMENT'));
            $tpl->SetVariable('lbl_remove_attachment',_t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
            $tpl->ParseBlock('post/attachment');
        }

        // update reason
        if (!empty($post['id'])) {
            $tpl->SetBlock('post/update_reason');
            $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $post['last_update_reason']);
            $tpl->ParseBlock('post/update_reason');
        }

        // buttons
        $tpl->SetVariable('btn_submit_title', $btn_title);
        $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('post');
        return $tpl->Get();
    }

    /**
     * Add/Edit a post
     *
     * @access  public
     */
    function UpdatePost()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $post = $request->get(
            array('fid', 'tid', 'pid', 'subject', 'message', 'update_reason', 'remove_attachment'),
            'post'
        );

        if ($GLOBALS['app']->Session->IsSuperAdmin()) {
            $post['message'] = $request->get('message', 'post', false);
        }

        if (empty($post['message'])) {
            $GLOBALS['app']->Session->PushSimpleResponse(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                'UpdatePost'
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic  = $tModel->GetTopic($post['tid'], $post['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        if (($GLOBALS['app']->Registry->Get('/gadgets/Forums/enable_attachment') == 'true') &&
            $this->GetPermission('AddPostAttachment'))
        {
            $res = Jaws_Utils::UploadFiles(
                $_FILES,
                JAWS_DATA. 'forums',
                '',
                'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                false
            );
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'UpdatePost');
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            $post['attachment'] = isset($res['post_attachment'][0])? $res['post_attachment'][0] : '';
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost(
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['tid'],
                $post['fid'],
                $post['message']
            );
            $event_subject = _t('FORUMS_POSTS_NEW_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_POSTS_NEW_NOTIFICATION_MESSAGE');
            $error_message = _t('FORUMS_POSTS_NEW_ERROR');
        } else {
            $result = $pModel->UpdatePost(
                $post['pid'],
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['message'],
                $post['update_reason']
            );
            $event_subject = _t('FORUMS_POSTS_EDIT_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_POSTS_EDIT_NOTIFICATION_MESSAGE');
            $error_message = _t('FORUMS_POSTS_EDIT_ERROR');
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($error_message, 'UpdatePost');
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $post['pid'] = $result;
        $post_link = $this->GetURLFor(
            'Posts',
            array('fid' => $post['fid'], 'tid' => $post['tid']),
            true,
            'site_url'
        );
        $result = $pModel->PostNotification(
            $topic['email'],
            $event_subject,
            $event_message,
            $post_link,
            $topic['subject'],
            $this->ParseText($post['message'])
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to topic posts page
        Jaws_Header::Location($post_link);
    }

    /**
     * Delete a post
     *
     * @access  public
     */
    function DeletePost()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid', 'pid', 'confirm'));

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($post) || empty($post) || $post['id'] == $post['topic_first_post_id']) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if (!empty($rqst['confirm'])) {
                $result = $pModel->DeletePost($post['id'], $post['tid'], $post['fid']);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushSimpleResponse(
                        _t('FORUMS_POSTS_DELETE_ERROR'),
                        'DeletePost'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }
            }

            $event_subject = _t('FORUMS_POSTS_DELETE_NOTIFICATION_SUBJECT', $post['forum_title']);
            $event_message = _t('FORUMS_POSTS_DELETE_NOTIFICATION_MESSAGE');
            $topic_link = $this->GetURLFor(
                'Posts',
                array('fid' => $post['fid'], 'tid' => $post['tid']),
                true,
                'site_url'
            );
            $result = $pModel->PostNotification(
                $post['email'],
                $event_subject,
                $event_message,
                $topic_link,
                $post['subject'],
                $this->ParseText($post['message'])
            );
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // redirect to topic posts list
            Jaws_Header::Location($topic_link);
        } else {
            $tpl = new Jaws_Template('gadgets/Forums/templates/');
            $tpl->Load('DeletePost.html');
            $tpl->SetBlock('post');

            $tpl->SetVariable('fid', $post['fid']);
            $tpl->SetVariable('tid', $post['tid']);
            $tpl->SetVariable('pid', $post['id']);
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
            $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
            $tpl->SetVariable('forum_title', $post['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->GetURLFor('Topics', array('fid'=> $post['fid']))
            );
            $tpl->SetVariable('topic_title', $post['subject']);
            $tpl->SetVariable(
                'topic_url',
                $this->GetURLFor('Posts', array('fid'=> $post['fid'], 'tid' => $post['tid']))
            );
            $tpl->SetVariable('title', _t('FORUMS_POSTS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopSimpleResponse('DeletePost')) {
                $tpl->SetBlock('post/response');
                $tpl->SetVariable('msg', $response);
                $tpl->ParseBlock('post/response');
            }

            // date format
            $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $post['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('createtime', $objDate->Format($post['createtime'], $date_format));
            $tpl->SetVariable('createtime_iso', $objDate->ToISO($post['createtime']));

            // message
            $tpl->SetVariable('message', $post['message']);

            $tpl->SetVariable('btn_submit_title', _t('FORUMS_POSTS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));
            $tpl->ParseBlock('post');
            return $tpl->Get();
        }
    }

}