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
class Forums_Actions_Topics extends ForumsHTML
{
    /**
     * Display forum topics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topics()
    {
        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $forum  = $fModel->GetForum($rqst['fid']);
        if (Jaws_Error::IsError($forum) || empty($forum)) {
            return false;
        }

        $limit = (int)$GLOBALS['app']->Registry->Get('/gadgets/Forums/topics_limit');
        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topics = $tModel->GetTopics($forum['id'], $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
        $tpl->SetVariable('title', $forum['title']);
        $tpl->SetVariable('url', $this->GetURLFor('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('lbl_topics', _t('FORUMS_TOPICS'));
        $tpl->SetVariable('lbl_replies', _t('FORUMS_REPLIES'));
        $tpl->SetVariable('lbl_views', _t('FORUMS_VIEWS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));

        // date format
        $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        // posts per page
        $posts_limit = $GLOBALS['app']->Registry->Get('/gadgets/Forums/posts_limit');
        $posts_limit = empty($posts_limit)? 10 : (int)$posts_limit;
        foreach ($topics as $topic) {
            $tpl->SetBlock('topics/topic');
            $tpl->SetVariable('status', (int)$topic['locked']);
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable(
                'url',
                $this->GetURLFor('Posts', array('fid' => $forum['id'], 'tid' => $topic['id']))
            );
            $tpl->SetVariable('replies', $topic['replies']);
            $tpl->SetVariable('views', $topic['views']);

            // last post
            if (!empty($topic['last_post_id'])) {
                $tpl->SetBlock('topics/topic/lastpost');
                $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));

                $tpl->SetVariable('username', $topic['username']);
                $tpl->SetVariable('nickname', $topic['nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUMS_LASTPOST'));
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time'], $date_format));
                $url_params = array('fid' => $topic['fid'], 'tid'=> $topic['id']);
                $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('lastpost_url', $this->GetURLFor('Posts', $url_params));
                $tpl->ParseBlock('topics/topic/lastpost');
            }

            $tpl->ParseBlock('topics/topic');
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'topics',
            $page,
            $limit,
            $forum['topics'],
            _t('FORUMS_TOPICS_COUNT', $forum['topics']),
            'Topics',
            array('fid' => $forum['id'])
        );

        if ($GLOBALS['app']->Session->Logged() && $this->GetPermission('AddTopic')) {
            $tpl->SetBlock('topics/action');
            $tpl->SetVariable('action_lbl', _t('FORUMS_TOPICS_NEW'));
            $tpl->SetVariable('action_url', $this->GetURLFor('NewTopic', array('fid' => $forum['id'])));
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
        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid'));
        if (empty($rqst['fid'])) {
            return false;
        }

        if (!empty($rqst['tid'])) {
            $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
            $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($topic) || empty($topic)) {
                return false;
            }

            $title = _t('FORUMS_TOPICS_EDIT_TITLE');
            $btn_title = _t('FORUMS_TOPICS_EDIT_BUTTON');
        } else {
            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
            $forum  = $fModel->GetForum($rqst['fid']);
            if (Jaws_Error::IsError($forum) || empty($forum)) {
                return false;
            }

            $topic = array();
            $topic['id'] = 0;
            $topic['fid'] = $forum['id'];
            $topic['forum_title'] = $forum['title'];
            $topic['subject'] = '';
            $topic['message'] = '';
            $topic['last_update_reason'] = '';
            $title = _t('FORUMS_TOPICS_NEW_TITLE');
            $btn_title = _t('FORUMS_TOPICS_NEW_BUTTON');
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditTopic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable(
            'forum_url',
            $this->GetURLFor('Topics', array('fid' => $topic['fid']))
        );
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $rqst['fid']);
        $tpl->SetVariable('tid', $topic['id']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('UpdateTopic')) {
            $tpl->SetBlock('topic/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('topic/response');
        }

        if (!empty($topic['id'])) {
            // date format
            $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
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
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));
            $tpl->ParseBlock('topic/post_meta');
        }

        // subject
        $tpl->SetBlock('topic/subject');
        $tpl->SetVariable('subject', $topic['subject']);
        $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->ParseBlock('topic/subject');

        // message
        $tpl->SetVariable('message', $topic['message']);
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));

        // attachment
        if ($GLOBALS['app']->Registry->Get('/gadgets/Forums/enable_attachment') == 'true' &&
            $this->GetPermission('AddPostAttachment'))
        {
            $tpl->SetBlock('topic/attachment');
            $tpl->SetVariable('lbl_attachment',_t('FORUMS_POSTS_ATTACHMENT'));
            $tpl->SetVariable('lbl_remove_attachment',_t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
            $tpl->ParseBlock('topic/attachment');
        }

        // update reason
        if (!empty($topic['id'])) {
            $tpl->SetBlock('topic/update_reason');
            $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $topic['last_update_reason']);
            $tpl->ParseBlock('topic/update_reason');
        }

        // buttons
        $tpl->SetVariable('btn_submit_title', $btn_title);
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $topic = $request->get(
            array('fid', 'tid', 'subject', 'message', 'remove_attachment', 'update_reason', 'published'),
            'post'
        );
        $topic['forum_title'] = '';

        if (empty($topic['subject']) ||  empty($topic['message'])) {
            $GLOBALS['app']->Session->PushSimpleResponse(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                'UpdateTopic'
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // attachment
        $topic['attachment'] = is_null($topic['remove_attachment'])? null : false;
        if (is_null($topic['attachment']) &&
            $GLOBALS['app']->Registry->Get('/gadgets/Forums/enable_attachment') == 'true' &&
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
                $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'UpdateTopic');
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            if (!empty($res)) {
                $topic['attachment']['host_fname'] = $res['attachment'][0]['host_filename'];
                $topic['attachment']['user_fname'] = $res['attachment'][0]['user_filename'];
            }
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        if (empty($topic['tid'])) {
            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
            $result = $fModel->GetForum($topic['fid']);
            if (!Jaws_Error::IsError($result) && !empty($result)) {
                $topic['forum_title'] = $result['title'];
                $result = $tModel->InsertTopic(
                    $GLOBALS['app']->Session->GetAttribute('user'),
                    $topic['fid'],
                    $topic['subject'],
                    $topic['message'],
                    $topic['attachment'],
                    $topic['published']
                );
            }
            $event_subject = _t('FORUMS_TOPICS_NEW_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_TOPICS_NEW_NOTIFICATION_MESSAGE');
            $error_message = _t('FORUMS_TOPICS_NEW_ERROR');
        } else {
            $result = $tModel->GetTopic($topic['tid'], $topic['fid']);
            if (!Jaws_Error::IsError($result) && !empty($result)) {
                $topic['forum_title'] = $result['forum_title'];
                $result = $tModel->UpdateTopic(
                    $topic['fid'],
                    $topic['tid'],
                    $result['first_post_id'],
                    $GLOBALS['app']->Session->GetAttribute('user'),
                    $topic['subject'],
                    $topic['message'],
                    $topic['attachment'],
                    $result['attachment_host_fname'],
                    $topic['published'],
                    $topic['update_reason']
                );
            }
            $event_subject = _t('FORUMS_TOPICS_EDIT_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_TOPICS_EDIT_NOTIFICATION_MESSAGE');
            $error_message = _t('FORUMS_TOPICS_EDIT_ERROR');
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($error_message, 'UpdateTopic');
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $topic['tid'] = $result;
        $topic_link = $this->GetURLFor(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['tid']),
            true,
            'site_url'
        );
        $result = $tModel->TopicNotification(
            $event_subject,
            $event_message,
            $topic_link,
            $topic['subject'],
            $this->ParseText($topic['message'])
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid', 'confirm'));

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if (!is_null($rqst['confirm'])) {
                $result = $tModel->DeleteTopic($topic['id'], $topic['fid'], $topic['attachment_host_fname']);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushSimpleResponse(
                        _t('FORUMS_TOPICS_DELETE_ERROR'),
                        'DeleteTopic'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $event_subject = _t('FORUMS_TOPICS_DELETE_NOTIFICATION_SUBJECT', $topic['forum_title']);
                $event_message = _t('FORUMS_TOPICS_DELETE_NOTIFICATION_MESSAGE');
                $forum_link = $this->GetURLFor(
                    'Topics',
                    array('fid' => $topic['fid']),
                    true,
                    'site_url'
                );
                $result = $tModel->TopicNotification(
                    $event_subject,
                    $event_message,
                    $forum_link,
                    $topic['subject'],
                    $this->ParseText($topic['message'])
                );
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }

                // redirect to topics list
                Jaws_Header::Location($forum_link);
            }

            // redirect to topic posts list
            Jaws_Header::Location(
                $this->GetURLFor('Posts', array('fid'=> $topic['fid'],'tid' => $topic['id'])),
                true
            );
        } else {
            $tpl = new Jaws_Template('gadgets/Forums/templates/');
            $tpl->Load('DeleteTopic.html');
            $tpl->SetBlock('topic');

            $tpl->SetVariable('fid', $topic['fid']);
            $tpl->SetVariable('tid', $topic['id']);
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
            $tpl->SetVariable('findex_url', $this->GetURLFor('Forums'));
            $tpl->SetVariable('forum_title', $topic['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->GetURLFor('Topics', array('fid'=> $topic['fid']))
            );
            $tpl->SetVariable('title', _t('FORUMS_TOPICS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopSimpleResponse('DeleteTopic')) {
                $tpl->SetBlock('topic/response');
                $tpl->SetVariable('msg', $response);
                $tpl->ParseBlock('topic/response');
            }

            // date format
            $date_format = $GLOBALS['app']->Registry->Get('/gadgets/Forums/date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // message
            $tpl->SetVariable('message', $topic['message']);

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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('fid', 'tid'), 'get');

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $result = $tModel->LockTopic($topic['id'], !$topic['locked']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        if ($topic['locked']) {
            $event_subject = _t('FORUMS_TOPICS_UNLOCK_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_TOPICS_UNLOCK_NOTIFICATION_MESSAGE');
        } else {
            $event_subject = _t('FORUMS_TOPICS_LOCK_NOTIFICATION_SUBJECT', $topic['forum_title']);
            $event_message = _t('FORUMS_TOPICS_LOCK_NOTIFICATION_MESSAGE');
        }

        $topic_link = $this->GetURLFor(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            true,
            'site_url'
        );
        $result = $tModel->TopicNotification(
            $event_subject,
            $event_message,
            $topic_link,
            $topic['subject'],
            $this->ParseText($topic['message'])
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

}