<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2021 Jaws Development Group
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
        $rqst = $this->gadget->request->fetch(array('fid', 'page', 'status'), 'get');
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

        $published = is_null($rqst['status']) ? null : ($rqst['status'] == 'published');
        if ($this->gadget->GetPermission('ForumManage', $forum['id'])) {
            $uid = null;
            $private = null;
        } else {
            $private = $forum['private'];
            $uid = (int)$this->app->session->user->id;
            // anonymous users
            $published = empty($uid) ? true : $published;

            // forum is private
            if ($private && empty($uid)) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $topics = $tModel->GetTopics($forum['id'], $published, $private, $uid, $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = Jaws_Date::getInstance();
        $tpl = $this->gadget->template->load('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('findex_title', $this::t('FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('title', $forum['title']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('lbl_topics', $this::t('TOPICS'));
        $tpl->SetVariable('lbl_replies', $this::t('REPLIES'));
        $tpl->SetVariable('lbl_views', $this::t('VIEWS'));
        $tpl->SetVariable('lbl_lastpost', $this::t('LASTPOST'));

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
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('username', $topic['first_username']);
            $tpl->SetVariable('nickname', $topic['first_nickname']);
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $topic['first_username']))
            );
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

        // Pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $limit,
            $forum['topics'],
            'Topics',
            array('fid' => $forum['id']),
            $this::t('TOPICS_COUNT', $forum['topics'])
        );

        if ($this->app->session->user->logged && $this->gadget->GetPermission('AddTopic')) {
            $tpl->SetBlock('topics/action');
            $tpl->SetVariable('action_lbl', $this::t('TOPICS_NEW'));
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = $this->gadget->request->fetch(
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

            $title = $this::t('TOPICS_EDIT_TITLE');
            $btn_title = $this::t('TOPICS_EDIT_BUTTON');
        } else {
            $topic = array();
            $topic['id'] = 0;
            $topic['fid'] = $forum['id'];
            $topic['forum_title'] = $forum['title'];
            $topic['subject'] = '';
            $topic['message'] = '';
            $topic['first_post_id'] = 0;
            $topic['update_reason'] = '';
            $title = $this::t('TOPICS_NEW_TITLE');
            $btn_title = $this::t('TOPICS_NEW_BUTTON');
        }

        if (!$this->gadget->GetPermission('ForumPublic', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('EditTopic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('findex_title', $this::t('FORUMS'));
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
            $tpl->SetVariable('lbl_preview', Jaws::t('PREVIEW'));
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
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $topic['username']))
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
            $tpl->SetVariable('lbl_target', $this::t('TOPICS_MOVEDTO'));
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
        if ($response = $this->gadget->session->pop('UpdateTopic')) {
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
        $tpl->SetVariable('lbl_subject', $this::t('TOPICS_SUBJECT'));
        $tpl->ParseBlock('topic/subject');

        // message
        $tpl->SetVariable('lbl_message', $this::t('POSTS_MESSAGE'));
        $message = $this->gadget->action->load('Editor')->load('message', Jaws_XSS::defilter($topic['message']));
        $message->setId('message');
        $message->TextArea->SetRows(8);
        $tpl->SetVariable('message', $message->Get());

        // status (published or draft)
        if ($this->gadget->GetPermission('PublishTopic')) {
            $tpl->SetBlock('topic/status');
            $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
            $tpl->SetVariable('lbl_draft', Jaws::t('DRAFT'));
            $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
            $tpl->ParseBlock('topic/status');
        }

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment')
        ) {
            Jaws_Gadget::getInstance('Files')->action->load('Files')->loadReferenceFiles(
                $tpl,
                array(
                    'gadget' => $this->gadget->name,
                    'action' => 'Post',
                    'reference' => $topic['first_post_id']
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

        // update reason
        if (!empty($topic['id'])) {
            $tpl->SetBlock('topic/update_reason');
            $tpl->SetVariable('lbl_update_reason', $this::t('POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $topic['update_reason']);
            $tpl->ParseBlock('topic/update_reason');
        }

        // notification
        if ($this->gadget->GetPermission('ForumManage', $topic['fid'])) {
            $tpl->SetBlock('topic/notification');
            $tpl->SetVariable('lbl_send_notification', $this::t('NOTIFICATION_MESSAGE'));
            if ((bool)$rqst['notification']) {
                $tpl->SetBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification/checked');
            }
            $tpl->ParseBlock('topic/notification');
        }

        // check captcha only in new topic action
        if (empty($topic['id'])) {
            $htmlPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $htmlPolicy->loadCaptcha($tpl);
        }

        // buttons
        $tpl->SetVariable('btn_update_title', $btn_title);
        $tpl->SetVariable('btn_preview_title', Jaws::t('PREVIEW'));
        $tpl->SetVariable('btn_cancel_title', Jaws::t('CANCEL'));

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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $topic = $this->gadget->request->fetch(
            array('fid', 'tid', 'target', 'subject', 'message', 'update_reason', 'notification', 'status'),
            'post'
        );

        if (empty($topic['fid']) || !$this->gadget->GetPermission('ForumPublic', $topic['fid'])) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($topic['subject']) ||  empty($topic['message'])) {
            $this->gadget->session->push(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                RESPONSE_ERROR,
                'UpdateTopic',
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
                $this->gadget->session->push(
                    $resCheck->getMessage(),
                    RESPONSE_ERROR,
                    'UpdateTopic',
                    $topic
                );
                Jaws_Header::Referrer();
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
                    $this->app->session->user->id,
                    $topic['fid'],
                    $topic['subject'],
                    $topic['message'],
                    $published
                );
            }

            if (!Jaws_Error::IsError($result)) {
                $oldTopic = $tModel->GetTopic($result, $topic['fid']);
                if (Jaws_Error::IsError($oldTopic) || empty($oldTopic)) {
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }
            }

            $event_type = 'new';
            $error_message = $this::t('TOPICS_NEW_ERROR');
        } else {
            $oldTopic = $tModel->GetTopic($topic['tid'], $topic['fid']);
            if (Jaws_Error::IsError($oldTopic) || empty($oldTopic)) {
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            // check permission for edit topic
            $forumManage = $this->gadget->GetPermission('ForumManage', $topic['fid']);
            $update_uid = (int)$this->app->session->user->id;
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

            $error_message = $this::t('TOPICS_EDIT_ERROR');
        }

        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $error_message,
                RESPONSE_ERROR,
                'UpdateTopic',
                $topic
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
                    'reference' => $oldTopic['first_post_id'],
                    'input_reference' => ($event_type == 'new')? 0 : $oldTopic['first_post_id']
                )
            );
        }

        $topic['tid'] = $result;
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['tid']),
            array('absolute' => true)
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
        return Jaws_Header::Location($topic_link);
    }

    /**
     * Delete a topic
     *
     * @access  public
     */
    function DeleteTopic()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'delete_reason', 'notification', 'confirm'));
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
                    ($topic['first_post_uid'] != (int)$this->app->session->user->id &&
                     !$forumManage) ||
                    ((time() - $topic['first_post_time']) > $delete_limit_time && !$forumManage)
                ) {
                    return Jaws_HTTPError::Get(403);
                }

                $result = $tModel->DeleteTopic($topic['id'], $topic['fid']);
                if (Jaws_Error::IsError($result)) {
                    $this->gadget->session->push(
                        $this::t('TOPICS_DELETE_ERROR'),
                        RESPONSE_NOTICE,
                        'DeleteTopic'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $forum_link = $this->gadget->urlMap(
                    'Topics',
                    array('fid' => $topic['fid']),
                    array('absolute' => true)
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
                return Jaws_Header::Location($forum_link);
            }

            // redirect to topic posts list
            return Jaws_Header::Location(
                $this->gadget->urlMap('Posts', array('fid'=> $topic['fid'],'tid' => $topic['id']))
            );
        } else {
            $tpl = $this->gadget->template->load('DeleteTopic.html');
            $tpl->SetBlock('topic');

            $tpl->SetVariable('fid', $topic['fid']);
            $tpl->SetVariable('tid', $topic['id']);
            $tpl->SetVariable('findex_title', $this::t('FORUMS'));
            $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
            $tpl->SetVariable('forum_title', $topic['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->gadget->urlMap('Topics', array('fid'=> $topic['fid']))
            );
            $tpl->SetVariable('title', $this::t('TOPICS_DELETE_TITLE'));

            // error response
            if ($response = $this->gadget->session->pop('DeleteTopic')) {
                $tpl->SetVariable('response_type', $response['type']);
                $tpl->SetVariable('response_text', $response['text']);
            }

            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',$this::t('POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $this->app->map->GetMappedURL('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = Jaws_Date::getInstance();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // message
            $tpl->SetVariable('message', $this->gadget->plugin->parseAdmin($topic['message']));

            // delete reason
            $tpl->SetVariable('lbl_delete_reason', $this::t('POSTS_DELETE_REASON'));

            // notification
            if ($this->gadget->GetPermission('ForumManage', $topic['fid'])) {
                $tpl->SetBlock('topic/notification');
                $tpl->SetVariable('lbl_send_notification', $this::t('NOTIFICATION_MESSAGE'));
                $tpl->SetBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification/checked');
                $tpl->ParseBlock('topic/notification');
            }

            $tpl->SetVariable('btn_submit_title', $this::t('TOPICS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', Jaws::t('CANCEL'));
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'notification'), 'get');
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
            array('absolute' => true)
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('PublishTopic');

        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'notification'), 'get');
        $tModel = $this->gadget->model->load('Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check user permissions
        $logged_user = (int)$this->app->session->user->id;
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
            array('absolute' => true)
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