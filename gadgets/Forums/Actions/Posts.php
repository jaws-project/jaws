<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Posts extends Forums_Actions_Default
{
    /**
     * Display topic posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Posts()
    {
        $rqst = jaws()->request->fetch(array('fid', 'tid', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        if (!$this->gadget->GetPermission('ForumAccess', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
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

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable('forum_url', $this->gadget->urlMap('Topics', array('fid' => $topic['fid'])));
        $tpl->SetVariable('title', $topic['subject']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Posts', array('fid' => $rqst['fid'], 'tid' => $rqst['tid'])));

        // date format
        $date_format = $this->gadget->registry->fetch('date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        // edit max/min limit time
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

        $objDate = Jaws_Date::getInstance();
        $usrModel = new Jaws_User;
        $startPostNumber = $limit * ($page - 1);
        $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
        foreach ($posts as $pnum => $post) {
            $tpl->SetBlock('posts/post');
            $startPostNumber ++;
            $tpl->SetVariable('post_number', $startPostNumber);
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('posts_count_lbl',_t('FORUMS_USERS_POSTS_COUNT'));
            $tpl->SetVariable('registered_date_lbl',_t('FORUMS_USERS_REGISTERED_DATE'));
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('posts_count', $pModel->GetUserPostsCount($post['uid']));
            $tpl->SetVariable('user_posts', $this->gadget->urlMap(
                              'UserPosts',
                              array('uid' => $post['uid'])));
            $tpl->SetVariable('registered_date', $objDate->Format($post['user_registered_date'], 'd MN Y'));
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            $tpl->SetVariable('post_id',  $post['id']);
            $tpl->SetVariable('message',  $this->gadget->ParseText($post['message'], 'Forums', 'index'));
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

            // attachment
            if ($post['attachments'] > 0) {
                $aModel = $this->gadget->model->load('Attachments');
                $attachments = $aModel->GetAttachments($post['id']);

                foreach ($attachments as $attachment) {
                    $tpl->SetBlock('posts/post/attachment');
                    $tpl->SetVariable('user_fname', $attachment['title']);
                    $tpl->SetVariable('lbl_attachment', _t('FORUMS_POSTS_ATTACHMENT'));
                    $tpl->SetVariable(
                        'hits_count',
                        _t('FORUMS_POSTS_ATTACHMENT_HITS', $attachment['hits_count'])
                    );
                    $tpl->SetVariable('url_attachment',
                            $this->gadget->urlMap(
                            'Attachment',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'], 'attach' => $attachment['id'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/attachment');
                }
            }

            // update information
            if ($post['update_uid'] != 0) {
                $tpl->SetBlock('posts/post/update');
                $tpl->SetVariable('updatedby_lbl', _t('FORUMS_POSTS_UPDATEDBY'));
                $tpl->SetVariable('username', $post['updater_username']);
                $tpl->SetVariable('nickname', $post['updater_nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor(
                        'Users',
                        'Profile',
                        array('user' => $post['updater_username'])
                    )
                );
                $tpl->SetVariable('update_time', $objDate->Format($post['update_time'], $date_format));
                $tpl->SetVariable('update_time_iso', $objDate->ToISO($post['update_time']));
                if (!empty($post['update_reason'])) {
                    $tpl->SetBlock('posts/post/update/reason');
                    $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
                    $tpl->SetVariable('update_reason', $post['update_reason']);
                    $tpl->ParseBlock('posts/post/update/reason');
                }
                $tpl->ParseBlock('posts/post/update');
            }

            // reply: check permission for add post
            if ($this->gadget->GetPermission('AddPost') &&
                (!$topic['locked'] || 
                ($forumManage && 
                $this->gadget->GetPermission('AddPostToLockedTopic')))
            ) {
                $tpl->SetBlock('posts/post/action');
                $tpl->SetVariable('action_lbl',_t('FORUMS_POSTS_REPLY'));
                $tpl->SetVariable('action_title',_t('FORUMS_POSTS_REPLY_TITLE'));
                $tpl->SetVariable(
                    'action_url',
                    $this->gadget->urlMap(
                        'ReplyPost',
                        array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
                    )
                );
                $tpl->ParseBlock('posts/post/action');
            }

            if ($topic['first_post_id'] == $post['id']) {
                // check permission for edit topic
                if ($this->gadget->GetPermission('EditTopic') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     ($this->gadget->GetPermission('EditOthersTopic') && $forumManage)) &&
                    (!$topic['locked'] || ($this->gadget->GetPermission('EditLockedTopic') && $forumManage)) &&
                    ((time() - $post['insert_time']) <= $edit_max_limit_time ||
                     ($this->gadget->GetPermission('EditOutdatedTopic') && $forumManage))
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_TOPICS_EDIT'));
                    $tpl->SetVariable('action_title',_t('FORUMS_TOPICS_EDIT_TITLE'));
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
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     ($this->gadget->GetPermission('DeleteOthersTopic') && $forumManage)) &&
                    ((time() - $post['insert_time']) <= $edit_min_limit_time ||
                     ($this->gadget->GetPermission('DeleteOutdatedTopic') && $forumManage))
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_TOPICS_DELETE'));
                    $tpl->SetVariable('action_title',_t('FORUMS_TOPICS_DELETE_TITLE'));
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
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     ($this->gadget->GetPermission('EditOthersPost') && $forumManage)) &&
                    (!$topic['locked'] || ($this->gadget->GetPermission('EditPostInLockedTopic') && $forumManage)) &&
                    ((time() - $post['insert_time']) <= $edit_max_limit_time ||
                     ($this->gadget->GetPermission('EditOutdatedPost') && $forumManage))
                ) {
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_POSTS_EDIT'));
                    $tpl->SetVariable('action_title',_t('FORUMS_POSTS_EDIT_TITLE'));
                    $tpl->SetVariable(
                        'action_url',
                        $this->gadget->urlMap(
                            'EditPost',
                            array('fid' => $rqst['fid'], 'tid' => $rqst['tid'], 'pid' => $post['id'])
                        )
                    );
                    $tpl->ParseBlock('posts/post/action');
                }

                // check permission for delete post
                if ($this->gadget->GetPermission('DeletePost') &&
                    ($post['uid'] == (int)$GLOBALS['app']->Session->GetAttribute('user') ||
                     ($this->gadget->GetPermission('DeleteOthersPost') && $forumManage)) &&
                    (!$topic['locked'] || ($this->gadget->GetPermission('DeletePostInLockedTopic') && $forumManage)) &&
                    ((time() - $post['insert_time']) <= $edit_min_limit_time ||
                     ($this->gadget->GetPermission('DeleteOutdatedPost') && $forumManage))
                ){
                    $tpl->SetBlock('posts/post/action');
                    $tpl->SetVariable('action_lbl',_t('FORUMS_POSTS_DELETE'));
                    $tpl->SetVariable('action_title',_t('FORUMS_POSTS_DELETE_TITLE'));
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
        if ($this->gadget->GetPermission('AddPost') &&
            (!$topic['locked'] || 
            ($forumManage && 
            $this->gadget->GetPermission('AddPostToLockedTopic')))
        ){
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable('action_lbl', _t('FORUMS_POSTS_NEW'));
            $tpl->SetVariable(
                'action_url',
                $this->gadget->urlMap('NewPost', array('fid' => $rqst['fid'], 'tid' => $rqst['tid']))
            );
            $tpl->ParseBlock('posts/action');
        }

        // check permission to lock/unlock topic
        if ($this->gadget->GetPermission('LockTopic') && $forumManage){
            $tpl->SetBlock('posts/action');
            $tpl->SetVariable(
                'action_lbl',
                $topic['locked']? _t('FORUMS_TOPICS_UNLOCK') : _t('FORUMS_TOPICS_LOCK')
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
                $topic['published']? _t('FORUMS_TOPICS_DRAFT') : _t('FORUMS_TOPICS_PUBLISH')
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
     * Reply a post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReplyPost()
    {
        $reply_to_message = '';
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $rqst = jaws()->request->fetch(array('fid', 'tid', 'pid', 'message', 'update_reason'));
            if (empty($rqst['fid']) || empty($rqst['tid'])) {
                return false;
            }

            $pModel = $this->gadget->model->load('Posts');
            $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($post) || empty($post)) {
                return false;
            }

            $reply_to_message = "[quote={$post['nickname']}]\n".$post['message']."\n[/quote]\n";
        }

        return $this->EditPost(true, $reply_to_message);
    }

    /**
     * Show edit post form
     *
     * @access  public
     * @param   bool    $reply              Reply mode
     * @param   string  $reply_to_message   Reply to message content
     * @return  string  XHTML template content
     */
    function EditPost($reply = false, $reply_to_message = '')
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'pid', 'message', 'update_reason'));
        if (empty($rqst['fid']) || empty($rqst['tid'])) {
            return false;
        }

        if (!$this->gadget->GetPermission('ForumAccess', $rqst['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        if ($reply || empty($rqst['pid'])) {
            $tModel = $this->gadget->model->load('Topics');
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
            $post['message'] = $reply_to_message;
            $post['update_reason'] = '';
            $title = _t('FORUMS_POSTS_NEW_TITLE');
            $btn_title = _t('FORUMS_POSTS_NEW_BUTTON');
        } else {
            $pModel = $this->gadget->model->load('Posts');
            $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($post) || empty($post)) {
                return false;
            }

            $title = _t('FORUMS_POSTS_EDIT_TITLE');
            $btn_title = _t('FORUMS_POSTS_EDIT_BUTTON');
        }

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('EditPost.html');
        $tpl->SetBlock('post');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('forum_title', $post['forum_title']);
        $tpl->SetVariable(
            'forum_url',
            $this->gadget->urlMap('Topics', array('fid'=> $post['fid']))
        );
        $tpl->SetVariable('topic_title', $post['subject']);
        $tpl->SetVariable(
            'topic_url',
            $this->gadget->urlMap('Posts', array('fid' => $post['fid'], 'tid' => $post['tid']))
        );
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $post['fid']);
        $tpl->SetVariable('tid', $post['tid']);
        $tpl->SetVariable('pid', $post['id']);

        // preview
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $post['message'] = $rqst['message'];
            $post['update_reason'] = $rqst['update_reason'];
            $tpl->SetBlock('post/preview');
            $tpl->SetVariable('lbl_preview', _t('GLOBAL_PREVIEW'));
            $tpl->SetVariable('message', $this->gadget->ParseText($post['message'], 'Forums', 'index'));
            $tpl->ParseBlock('post/preview');
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('UpdatePost')) {
            $tpl->SetBlock('post/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('post/response');
        }

        if (!empty($post['id'])) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
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
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            $tpl->ParseBlock('post/post_meta');
        }

        // message
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));
        $message =& $GLOBALS['app']->LoadEditor('Forums', 'message', Jaws_XSS::defilter($post['message']), false);
        $message->setId('message');
        $message->TextArea->SetRows(8);
        $tpl->SetVariable('message', $message->Get());

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $tpl->SetBlock('post/attachment');
            $tpl->SetVariable('lbl_attachment', _t('FORUMS_POSTS_ATTACHMENT'));
            $tpl->SetVariable('lbl_extra_attachment', _t('FORUMS_POSTS_EXTRA_ATTACHMENT'));
            $tpl->SetVariable('lbl_remove_attachment', _t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
            if ($post['id'] != 0) {
                $aModel = $this->gadget->model->load('Attachments');
                $attachments = $aModel->GetAttachments($post['id']);

                foreach ($attachments as $attachment) {
                    $tpl->SetBlock('post/attachment/current_attachment');
                    $tpl->SetVariable('aid', $attachment['id']);
                    $tpl->SetVariable('lbl_filename', $attachment['title']);
                    $tpl->SetVariable('lbl_remove_attachment', _t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
                    $tpl->ParseBlock('post/attachment/current_attachment');
                }
            }
            $tpl->ParseBlock('post/attachment');
        }

        // update reason
        if (!empty($post['id'])) {
            $tpl->SetBlock('post/update_reason');
            $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $post['update_reason']);
            $tpl->ParseBlock('post/update_reason');
        }

        // chack captcha only in new post action
        if (empty($rqst['pid'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $htmlPolicy->loadCaptcha($tpl, 'post');
        }

        // buttons
        $tpl->SetVariable('btn_update_title', $btn_title);
        $tpl->SetVariable('btn_preview_title', _t('GLOBAL_PREVIEW'));
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
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(
            array('fid', 'tid', 'pid', 'subject', 'message', 'update_reason'),
            'post'
        );

        if (empty($post['fid']) || !$this->gadget->GetPermission('ForumAccess', $post['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($post['message'])) {
            $GLOBALS['app']->Session->PushSimpleResponse(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                'UpdatePost'
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // chack captcha only in new post action
        if (empty($post['pid'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha();
            if (Jaws_Error::IsError($resCheck)) {
                $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'UpdatePost');
                Jaws_Header::Referrer();
            }
        }

        $tModel = $this->gadget->model->load('Topics');
        $topic  = $tModel->GetTopic($post['tid'], $post['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // attachment
        $post['attachments'] = null;
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $res = Jaws_Utils::UploadFiles(
                $_FILES,
                JAWS_DATA. 'forums',
                '',
                'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                null
            );
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'UpdatePost');
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            if (!empty($res)) {
                $post['attachments'] = $res['attachment'];
            }
        }

        $send_notification = true;
        // edit min/max limit time
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;

        $pModel = $this->gadget->model->load('Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost(
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['tid'],
                $post['fid'],
                $post['message'],
                $post['attachments']
            );
            $event_type = 'new';
            $error_message = _t('FORUMS_POSTS_NEW_ERROR');
            $last_post_page = floor($topic['replies']/$posts_limit) + 1;
        } else {
            $oldPost = $pModel->GetPost($post['pid'], $post['tid'], $post['fid']);
            if (Jaws_Error::IsError($oldPost) || empty($oldPost)) {
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            // check edit permissions
            $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
            $update_uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ((!$this->gadget->GetPermission('EditPost')) ||
                ($oldPost['uid'] != $update_uid && !($this->gadget->GetPermission('EditOthersPost') && $forumManage)) ||
                ($topic['locked'] && !($this->gadget->GetPermission('EditPostInLockedTopic') && $forumManage)) ||
                ((time() - $oldPost['insert_time']) > $edit_max_limit_time &&
                 !($this->gadget->GetPermission('EditOutdatedPost') && $forumManage))
            ) {
                return Jaws_HTTPError::Get(403);
            }

            if ((time() - $oldPost['insert_time']) <= $edit_min_limit_time) {
                $update_uid = 0;
                $send_notification = false;
                $post['update_reason'] = '';
            }

            // Update Attachments
            $remainAttachments = jaws()->request->fetch('current_attachments:array');
            $aModel = $this->gadget->model->load('Attachments');
            $oldAttachments = $aModel->GetAttachments($oldPost['id']);
            if (count($remainAttachments) == 0) {
                $aModel->DeletePostAttachments($oldPost['id']);
            } else {
                foreach ($oldAttachments as $oldAttachment) {
                    if (!in_array($oldAttachment['id'], $remainAttachments)) {
                        $aModel->DeleteAttachment($oldAttachment['id']);
                    }
                }
            }

            $result = $pModel->UpdatePost(
                $post['pid'],
                $update_uid,
                $post['message'],
                $post['attachments'],
                $post['update_reason']
            );
            $event_type = 'edit';
            // no notification for topic creator
            $topic['email'] = '';
            $error_message = _t('FORUMS_POSTS_EDIT_ERROR');
            $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($error_message, 'UpdatePost');
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $post['pid'] = $result;
        $url_params = array('fid' => $post['fid'], 'tid' => $post['tid']);
        if ($last_post_page > 1) {
            $url_params['page'] = $last_post_page;
        }
        $post_link = $this->gadget->urlMap('Posts', $url_params, true);

        // send email notification
        if ($send_notification) {
            $result = $pModel->PostNotification(
                $topic['email'],
                $event_type,
                $topic['forum_title'],
                $post_link,
                $topic['subject'],
                $this->gadget->ParseText($post['message'], 'Forums', 'index')
            );
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
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
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'pid', 'confirm', 'delete_reason'));

        $pModel = $this->gadget->model->load('Posts');
        $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($post) || empty($post) || $post['id'] == $post['topic_first_post_id']) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $topic_link = $this->gadget->urlMap(
                'Posts',
                array('fid' => $post['fid'], 'tid' => $post['tid']),
                true
            );

            if (!is_null($rqst['confirm'])) {
                // delete min limit time
                $delete_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

                // check delete permissions
                $forumManage = $this->gadget->GetPermission('ForumManage', $post['fid']);
                if ((!$this->gadget->GetPermission('DeletePost')) ||
                    ($post['uid'] != (int)$GLOBALS['app']->Session->GetAttribute('user') &&
                     !($this->gadget->GetPermission('DeleteOthersPost') && $forumManage)) ||
                    ($post['topic_locked'] && !($this->gadget->GetPermission('DeletePostInLockedTopic') && $forumManage)) ||
                    ((time() - $post['insert_time']) > $delete_limit_time &&
                     !($this->gadget->GetPermission('DeleteOutdatedPost') && $forumManage))
                ) {
                    return Jaws_HTTPError::Get(403);
                }

                $result = $pModel->DeletePost(
                    $post['id'],
                    $post['tid'],
                    $post['fid']
                );
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushSimpleResponse(
                        _t('FORUMS_POSTS_DELETE_ERROR'),
                        'DeletePost'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $event_type = 'delete';
                $result = $pModel->PostNotification(
                    '',
                    $event_type,
                    $post['forum_title'],
                    $topic_link,
                    $post['subject'],
                    $this->gadget->ParseText($post['message'], 'Forums', 'index', 'index'),
                    $this->gadget->ParseText($rqst['delete_reason'], 'Forums', 'index')
                );
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }
            }

            // redirect to topic posts list
            Jaws_Header::Location($topic_link);
        } else {
            $tpl = $this->gadget->template->load('DeletePost.html');
            $tpl->SetBlock('post');

            $tpl->SetVariable('fid', $post['fid']);
            $tpl->SetVariable('tid', $post['tid']);
            $tpl->SetVariable('pid', $post['id']);
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
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
            $tpl->SetVariable('title', _t('FORUMS_POSTS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopSimpleResponse('DeletePost')) {
                $tpl->SetBlock('post/response');
                $tpl->SetVariable('msg', $response);
                $tpl->ParseBlock('post/response');
            }

            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $post['username']);
            $tpl->SetVariable('nickname', $post['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $post['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($post['insert_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$post['insert_time']));
            
            $tpl->SetVariable('lbl_delete_reason', _t('FORUMS_POSTS_DELETE_REASON'));

            // message
            $tpl->SetVariable('message', $post['message']);

            $tpl->SetVariable('btn_submit_title', _t('FORUMS_POSTS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));
            $tpl->ParseBlock('post');
            return $tpl->Get();
        }
    }

}