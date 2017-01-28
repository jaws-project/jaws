<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Topics extends Jaws_Gadget_Action
{
    /**
     * Display forum topics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topics()
    {
        $rqst = jaws()->request->fetch(array('fid', 'page', 'status'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $fModel = $this->gadget->model->load('Forums');
        $forum  = $fModel->GetForum($rqst['fid']);
        if (empty($forum)) {
            return false;
        }

        if (Jaws_Error::IsError($forum)) {
            return Jaws_HTTPError::Get($forum->getCode());
        }

        $limit = (int)$this->gadget->registry->fetch('topics_limit');
        $tModel = $this->gadget->model->load('Topics');

        $published = is_null($rqst['status'])? null : ($rqst['status'] == 'published');
        if ($this->gadget->GetPermission('ForumManage', $forum['id'])) {
            $uid = null;
        } else {
            $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
            // anonymous users
            $published = empty($uid)? true : $published;
        }

        $topics = $tModel->GetTopics($forum['id'], $published, $uid, $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = Jaws_Date::getInstance();
        $tpl = $this->gadget->template->load('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('title', $forum['title']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('lbl_topics', _t('FORUMS_TOPICS'));
        $tpl->SetVariable('lbl_replies', _t('FORUMS_REPLIES'));
        $tpl->SetVariable('lbl_views', _t('FORUMS_VIEWS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));

        // display subscription if installed
        if (Jaws_Gadget::IsGadgetInstalled('Subscription')) {
            $sHTML = Jaws_Gadget::getInstance('Subscription')->action->load('Subscription');
            $tpl->SetVariable('subscription', $sHTML->ShowSubscription('Forums', 'Forum', $rqst['fid']));
        }

        // date format
        $date_format = $this->gadget->registry->fetch('date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit) ? 10 : (int)$posts_limit;
        foreach ($topics as $topic) {
            $tpl->SetBlock('topics/topic');
            $tpl->SetVariable('status', (int)$topic['locked']);
            $published_status = ((int)$topic['published'] === 1) ? 'published' : 'draft';
            $tpl->SetVariable('published_status', $published_status);
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable(
                'url',
                $this->gadget->urlMap('Posts', array('fid' => $forum['id'], 'tid' => $topic['id']))
            );
            $tpl->SetVariable('replies', $topic['replies']);
            $tpl->SetVariable('views', $topic['views']);
            // first post
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['first_username']);
            $tpl->SetVariable('nickname', $topic['first_nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['first_username']))
            );
            $tpl->SetVariable('firstpost_date', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('firstpost_date_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // last post
            if (!empty($topic['last_post_id'])) {
                $tpl->SetBlock('topics/topic/lastpost');
                $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
                $tpl->SetVariable('username', $topic['last_username']);
                $tpl->SetVariable('nickname', $topic['last_nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['last_username']))
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUMS_LASTPOST'));
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

        // Pagination
        $this->gadget->action->load('Navigation')->pagination(
            $tpl,
            $page,
            $limit,
            $forum['topics'],
            'Topics',
            array('fid' => $forum['id']),
            _t('FORUMS_TOPICS_COUNT', $forum['topics'])
        );

        if ($GLOBALS['app']->Session->Logged() && $this->gadget->GetPermission('AddTopic')) {
            $tpl->SetBlock('topics/action');
            $tpl->SetVariable('action_lbl', _t('FORUMS_TOPICS_NEW'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('NewTopic', array('fid' => $forum['id'])));
            $tpl->ParseBlock('topics/action');
        }

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
        return $this->EditTopic();
    }

    /**
     * Show edit topic form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(
            array('fid', 'tid', 'target', 'subject', 'message', 'update_reason', 'notification')
        );
        if (empty($rqst['fid'])) {
            return false;
        }

        $fModel = $this->gadget->model->load('Forums');
        $forum = $fModel->GetForum($rqst['fid']);
        if (empty($forum)) {
            return false;
        }
        if (Jaws_Error::IsError($forum)) {
            return Jaws_HTTPError::Get($forum->getCode());
        }

        if (!empty($rqst['tid'])) {
            $tModel = $this->gadget->model->load('Topics');
            $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($topic) || empty($topic)) {
                return false;
            }

            $title = _t('FORUMS_TOPICS_EDIT_TITLE');
            $btn_title = _t('FORUMS_TOPICS_EDIT_BUTTON');
        } else {
            $topic = array();
            $topic['id'] = 0;
            $topic['fid'] = $forum['id'];
            $topic['forum_title'] = $forum['title'];
            $topic['subject'] = '';
            $topic['message'] = '';
            $topic['update_reason'] = '';
            $title = _t('FORUMS_TOPICS_NEW_TITLE');
            $btn_title = _t('FORUMS_TOPICS_NEW_BUTTON');
        }

        if (!$this->gadget->GetPermission('ForumPublic', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('EditTopic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable(
            'forum_url',
            $this->gadget->urlMap('Topics', array('fid' => $topic['fid']))
        );
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $rqst['fid']);
        $tpl->SetVariable('tid', $topic['id']);


        // preview
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $topic['target']  = $rqst['target'];
            $topic['subject'] = $rqst['subject'];
            $topic['message'] = $rqst['message'];
            $topic['update_reason'] = $rqst['update_reason'];
            $tpl->SetBlock('topic/preview');
            $tpl->SetVariable('lbl_preview', _t('GLOBAL_PREVIEW'));
            $tpl->SetVariable('message', $this->gadget->plugin->parse($topic['message']));
            $tpl->ParseBlock('topic/preview');
        }

        // first post meta
        if (!empty($topic['id'])) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetBlock('topic/post_meta');
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));
            $tpl->ParseBlock('topic/post_meta');
        }

        // move topic
        if (!empty($topic['id']) && $this->gadget->GetPermission('ForumManage', $forum['id'])) {
            $tpl->SetBlock('topic/target');
            $topic['target'] = isset($topic['target'])? $topic['target'] : $topic['fid'];
            $tpl->SetVariable('lbl_target', _t('FORUMS_TOPICS_MOVEDTO'));
            $forums = $fModel->GetForums(false, true);
            foreach ($forums as $forum) {
                $tpl->SetBlock('topic/target/item');
                $tpl->SetVariable('fid', $forum['id']);
                $tpl->SetVariable('title', $forum['title']);
                if ($forum['id'] == $topic['target']) {
                    $tpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $tpl->SetVariable('selected', '');
                }
                $tpl->ParseBlock('topic/target/item');
            }
            $tpl->ParseBlock('topic/target');
        }

        $rqst['notification'] = true;
        // response
        if ($response = $GLOBALS['app']->Session->PopResponse('UpdateTopic')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
            $topic['subject'] = $response['data']['subject'];
            $topic['message'] = $response['data']['message'];
            $topic['update_reason'] =  $response['data']['update_reason'];
            $rqst['notification'] = $response['data']['notification'];
        }

        // subject
        $tpl->SetBlock('topic/subject');
        $tpl->SetVariable('subject', $topic['subject']);
        $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->ParseBlock('topic/subject');

        // message
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));
        $message =& $GLOBALS['app']->LoadEditor('Forums', 'message', Jaws_XSS::defilter($topic['message']), false);
        $message->setId('message');
        $message->TextArea->SetRows(8);
        $tpl->SetVariable('message', $message->Get());

        // status (published or draft)
        if ($this->gadget->GetPermission('PublishTopic')) {
            $tpl->SetBlock('topic/status');
            $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
            $tpl->SetVariable('lbl_draft', _t('GLOBAL_DRAFT'));
            $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
            $tpl->ParseBlock('topic/status');
        }

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $tpl->SetBlock('topic/attachment');
            $tpl->SetVariable('lbl_attachment',_t('FORUMS_POSTS_ATTACHMENT'));
            $tpl->SetVariable('lbl_extra_attachment', _t('FORUMS_POSTS_EXTRA_ATTACHMENT'));
            $tpl->SetVariable('lbl_remove_attachment',_t('FORUMS_POSTS_ATTACHMENT_REMOVE'));

            if (!empty($topic['first_post_id'])) {
                $aModel = $this->gadget->model->load('Attachments');
                $attachments = $aModel->GetAttachments($topic['first_post_id']);

                foreach ($attachments as $attachment) {
                    $tpl->SetBlock('topic/attachment/current_attachment');
                    $tpl->SetVariable('aid', $attachment['id']);
                    $tpl->SetVariable('lbl_filename', $attachment['title']);
                    $tpl->SetVariable('lbl_remove_attachment', _t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
                    $tpl->ParseBlock('topic/attachment/current_attachment');
                }
            }
            $tpl->ParseBlock('topic/attachment');
        }

        // update reason
        if (!empty($topic['id'])) {
            $tpl->SetBlock('topic/update_reason');
            $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $topic['update_reason']);
            $tpl->ParseBlock('topic/update_reason');
        }

        // notification
        if ($this->gadget->GetPermission('ForumManage', $topic['fid'])) {
            $tpl->SetBlock('topic/notification');
            $tpl->SetVariable('lbl_send_notification', _t('FORUMS_NOTIFICATION_MESSAGE'));
            if ((bool)$rqst['notification']) {
                $tpl->SetBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification/checked');
            }
            $tpl->ParseBlock('topic/notification');
        }

        // check captcha only in new topic action
        if (empty($topic['id'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $htmlPolicy->loadCaptcha($tpl, 'topic');
        }

        // buttons
        $tpl->SetVariable('btn_update_title', $btn_title);
        $tpl->SetVariable('btn_preview_title', _t('GLOBAL_PREVIEW'));
        $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('topic');
        return $tpl->Get();
    }

    /**
     * Add/Edit a topic
     *
     * @access  public
     */
    function UpdateTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $topic = jaws()->request->fetch(
            array('fid', 'tid', 'target', 'subject', 'message', 'update_reason', 'notification', 'status'),
            'post'
        );

        if (empty($topic['fid']) || !$this->gadget->GetPermission('ForumPublic', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($topic['subject']) ||  empty($topic['message'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                'UpdateTopic',
                RESPONSE_ERROR,
                $topic
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check captcha only in new topic action
        if (empty($topic['tid'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $resCheck = $htmlPolicy->checkCaptcha();
            if (Jaws_Error::IsError($resCheck)) {
                $GLOBALS['app']->Session->PushResponse(
                    $resCheck->getMessage(),
                    'UpdateTopic',
                    RESPONSE_ERROR,
                    $topic
                );
                Jaws_Header::Referrer();
            }
        }

        // attachment
        $topic['attachments'] = null;
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $res = Jaws_Utils::UploadFiles(
                $_FILES,
                JAWS_DATA. 'forums',
                '',
                null
            );

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushResponse(
                    $res->getMessage(),
                    'UpdateTopic',
                    RESPONSE_ERROR,
                    $topic
                );
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            if (!empty($res)) {
                $topic['attachments'] = $res['attachment'];
            }
        }

        $send_notification =
            $this->gadget->GetPermission('ForumManage', $topic['fid'])? (bool)$topic['notification'] : true;
        // edit min/max limit time
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');

        $topic['forum_title'] = '';
        $tModel = $this->gadget->model->load('Topics');
        if (empty($topic['tid'])) {
            $fModel = $this->gadget->model->load('Forums');
            $result = $fModel->GetForum($topic['fid']);
            if (!Jaws_Error::IsError($result) && !empty($result)) {

                // check topic publish permission
                $status = $topic['status'];
                $published = false;
                if ($this->gadget->GetPermission('PublishTopic') && $status == 'published') {
                    $published = true;
                }

                $topic['forum_title'] = $result['title'];
                $result = $tModel->InsertTopic(
                    $GLOBALS['app']->Session->GetAttribute('user'),
                    $topic['fid'],
                    $topic['subject'],
                    $topic['message'],
                    $topic['attachments'],
                    $published
                );
            }
            $event_type = 'new';
            $error_message = _t('FORUMS_TOPICS_NEW_ERROR');
        } else {
            $oldTopic = $tModel->GetTopic($topic['tid'], $topic['fid']);
            if (Jaws_Error::IsError($oldTopic) || empty($oldTopic)) {
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            // check permission for edit topic
            $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
            $update_uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ((!$this->gadget->GetPermission('EditTopic')) ||
                ($oldTopic['first_post_uid'] != $update_uid && !$forumManage) ||
                ($oldTopic['locked'] && !$forumManage) ||
                ((time() - $oldTopic['first_post_time']) > $edit_max_limit_time && !$forumManage)
            ) {
                return Jaws_HTTPError::Get(403);
            }

            if ((time() - $oldTopic['first_post_time']) <= $edit_min_limit_time) {
                $update_uid = 0;
                $send_notification = false;
                $topic['update_reason'] = '';
            }

            // set target topic for move
            if (!$forumManage || empty($topic['target'])) {
                $topic['target'] = $topic['fid'];
            }

            // Update Attachments
            $remainAttachments = jaws()->request->fetch('current_attachments:array');
            $aModel = $this->gadget->model->load('Attachments');
            $oldAttachments = $aModel->GetAttachments($oldTopic['first_post_id']);
            if (count($remainAttachments) == 0) {
                $aModel->DeletePostAttachments($oldTopic['first_post_id']);
            } else {
                foreach ($oldAttachments as $oldAttachment) {
                    if (!in_array($oldAttachment['id'], $remainAttachments)) {
                        $aModel->DeleteAttachment($oldAttachment['id']);
                    }
                }
            }

            $topic['forum_title'] = $oldTopic['forum_title'];
            $topic['published'] = ($topic['status'] == 'published');
            $result = $tModel->UpdateTopic(
                $topic['target'],
                $topic['fid'],
                $topic['tid'],
                $oldTopic['first_post_id'],
                $update_uid,
                $topic['subject'],
                $topic['message'],
                $topic['attachments'],
                $topic['published'],
                $topic['update_reason']
            );

            // fill forum id with target forum id
            if ($topic['fid'] != $topic['target']) {
                $topic['fid'] = $topic['target'];
                $event_type = 'move';
            } else {
                $event_type = 'edit';
            }

            $error_message = _t('FORUMS_TOPICS_EDIT_ERROR');
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $error_message,
                'UpdateTopic',
                RESPONSE_ERROR,
                $topic
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $topic['tid'] = $result;
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['tid']),
            true
        );

        if ($send_notification) {
            $result = $tModel->TopicNotification(
                $event_type,
                $topic['forum_title'],
                $topic_link,
                $topic['subject'],
                $this->gadget->plugin->parse($topic['message'])
            );
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
        }

        // redirect to topic posts page
        Jaws_Header::Location($topic_link);
    }

    /**
     * Delete a topic
     *
     * @access  public
     */
    function DeleteTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'delete_reason', 'notification', 'confirm'));
        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if (!is_null($rqst['confirm'])) {
                // delete min limit time
                $delete_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

                // check delete permissions
                $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
                if ((!$this->gadget->GetPermission('DeleteTopic')) ||
                    ($topic['first_post_uid'] != (int)$GLOBALS['app']->Session->GetAttribute('user') &&
                     !$forumManage) ||
                    ((time() - $topic['first_post_time']) > $delete_limit_time && !$forumManage)
                ) {
                    return Jaws_HTTPError::Get(403);
                }

                $result = $tModel->DeleteTopic($topic['id'], $topic['fid']);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushResponse(
                        _t('FORUMS_TOPICS_DELETE_ERROR'),
                        'Forums.DeleteTopic'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $forum_link = $this->gadget->urlMap(
                    'Topics',
                    array('fid' => $topic['fid']),
                    true
                );

                $send_notification =
                    $this->gadget->GetPermission('ForumManage', $topic['fid'])? (bool)$rqst['notification'] : true;
                // send delete notification
                if ($send_notification) {
                    $result = $tModel->TopicNotification(
                        'delete',   // event_type
                        $topic['forum_title'],
                        $forum_link,
                        $topic['subject'],
                        $this->gadget->plugin->parse($topic['message']),
                        $this->gadget->plugin->parse($rqst['delete_reason'])
                    );
                    if (Jaws_Error::IsError($result)) {
                        // do nothing
                    }
                }

                // redirect to topics list
                Jaws_Header::Location($forum_link);
            }

            // redirect to topic posts list
            Jaws_Header::Location(
                $this->gadget->urlMap('Posts', array('fid'=> $topic['fid'],'tid' => $topic['id']))
            );
        } else {
            $tpl = $this->gadget->template->load('DeleteTopic.html');
            $tpl->SetBlock('topic');

            $tpl->SetVariable('fid', $topic['fid']);
            $tpl->SetVariable('tid', $topic['id']);
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
            $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
            $tpl->SetVariable('forum_title', $topic['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->gadget->urlMap('Topics', array('fid'=> $topic['fid']))
            );
            $tpl->SetVariable('title', _t('FORUMS_TOPICS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopResponse('Forums.DeleteTopic')) {
                $tpl->SetVariable('response_type', $response['type']);
                $tpl->SetVariable('response_text', $response['text']);
            }

            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // message
            $tpl->SetVariable('message', $this->gadget->plugin->parseAdmin($topic['message']));

            // delete reason
            $tpl->SetVariable('lbl_delete_reason', _t('FORUMS_POSTS_DELETE_REASON'));

            // notification
            if ($this->gadget->GetPermission('ForumManage', $topic['fid'])) {
                $tpl->SetBlock('topic/notification');
                $tpl->SetVariable('lbl_send_notification', _t('FORUMS_NOTIFICATION_MESSAGE'));
                $tpl->SetBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification');
            }

            $tpl->SetVariable('btn_submit_title', _t('FORUMS_TOPICS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));
            $tpl->ParseBlock('topic');
            return $tpl->Get();
        }
    }

    /**
     * Locked a topic
     *
     * @access  public
     */
    function LockTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'notification'), 'get');
        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        if (!($this->gadget->GetPermission('ForumManage', $topic['fid']))) {
            return Jaws_HTTPError::Get(403);
        }

        $result = $tModel->LockTopic($topic['id'], !$topic['locked']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        $event_type = $topic['locked']? 'unlock' : 'lock';
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            true
        );
        $result = $tModel->TopicNotification(
            $event_type,
            $topic['forum_title'],
            $topic_link,
            $topic['subject'],
            $this->gadget->plugin->parse($topic['message'])
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

    /**
     * Publish/Draft a topic
     *
     * @access  public
     */
    function PublishTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('PublishTopic');

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'notification'), 'get');
        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check user permissions
        $logged_user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        if($logged_user != $topic['first_post_uid'] && !$this->gadget->GetPermission('ForumManage', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
        }
        
        $result = $tModel->PublishTopic($topic['id'], $topic['fid'], !$topic['published']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        $event_type = $topic['published']? 'published' : 'draft';
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            true
        );
        $result = $tModel->TopicNotification(
            $event_type,
            $topic['forum_title'],
            $topic_link,
            $topic['subject'],
            $this->gadget->plugin->parse($topic['message'])
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

}