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
        $model = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topics = $model->GetTopics($forum['id'], $limit, ($page - 1) * $limit);
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
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time']));
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

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $forum  = $fModel->GetForum($rqst['fid']);
        if (Jaws_Error::IsError($forum) || empty($forum)) {
            return false;
        }

        if (!empty($rqst['tid'])) {
            $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
            $topic = $tModel->GetTopic($rqst['tid']);
            if (Jaws_Error::IsError($topic) || empty($topic)) {
                return false;
            }
            $title = _t('FORUMS_TOPICS_EDIT_TITLE');
            $btn_title = _t('FORUMS_TOPICS_EDIT_BUTTON');
        } else {
            $topic = array();
            $topic['id'] = 0;
            $topic['subject'] = '';
            $topic['message'] = '';
            $topic['last_update_reason'] = '';
            $title = _t('FORUMS_TOPICS_NEW_TITLE');
            $btn_title = _t('FORUMS_TOPICS_NEW_BUTTON');
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditTopic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('forum_title', $forum['title']);
        $tpl->SetVariable('forum_url', $this->GetURLFor('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $rqst['fid']);
        $tpl->SetVariable('tid', $topic['id']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('UpdateTopic')) {
            $tpl->SetBlock('topic/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('topic/response');
        }

        // subject
        $tpl->SetBlock('topic/subject');
        $tpl->SetVariable('subject', $topic['subject']);
        $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->ParseBlock('topic/subject');

        // message
        $tpl->SetVariable('message', $topic['message']);
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));

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
            array('fid', 'tid', 'subject', 'message', 'update_reason', 'published'),
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
            $topic['message']
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
            if (!empty($rqst['confirm'])) {
                $result = $tModel->DeleteTopic($topic['id'], $topic['fid']);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushSimpleResponse(
                        _t('FORUMS_TOPICS_DELETE_ERROR'),
                        'DeleteTopic'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                // redirect to topics list
                Jaws_Header::Location($this->GetURLFor('Topics', array('fid'=> $topic['fid'])), true);
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
            $tpl->SetVariable('forum_title', $topic['forum_title']);
            $tpl->SetVariable('forum_url', $this->GetURLFor('Topics', array('fid'=> $topic['fid'])));
            $tpl->SetVariable('title', _t('FORUMS_TOPICS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopSimpleResponse('DeleteTopic')) {
                $tpl->SetBlock('topic/response');
                $tpl->SetVariable('msg', $response);
                $tpl->ParseBlock('topic/response');
            }

            $tpl->SetVariable('message', $topic['message']);
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('createtime', $objDate->Format($topic['first_post_time']));

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

        $model = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $model->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $result = $model->LockTopic($topic['id'], !$topic['locked']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

}