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
     * Display forum's topics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topics()
    {
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('fid', 'page'), 'get');

        $model = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topics = $model->GetTopics($get['fid']);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('title', _t('FORUMS_NAME'));
        $tpl->SetVariable('url', $this->GetURLFor('Forums'));
        $tpl->SetVariable('lbl_topics', _t('FORUMS_TIPICS'));
        $tpl->SetVariable('lbl_replies', _t('FORUMS_REPLIES'));
        $tpl->SetVariable('lbl_views', _t('FORUMS_VIEWS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));

        foreach ($topics as $topic) {
            $tpl->SetBlock('topics/topic');
            $tpl->SetVariable('icon', '');
            if ($topic['locked']) {
                $tpl->SetVariable('status', _t('FORUMS_LOCKED'));
            }
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable(
                'url',
                $this->GetURLFor('Posts', array('fid' => $get['fid'], 'tid' => $topic['id']))
            );
            $tpl->SetVariable('replies', $topic['replies']);
            $tpl->SetVariable('views', $topic['views']);

            // last post
            if (!empty($topic['last_post_id'])) {
                $tpl->SetBlock('topics/topic/lastpost');
                $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));

                $tpl->SetVariable('username', $topic['username']);
                $tpl->SetVariable('nickname', $topic['nickname']);
                $tpl->SetVariable('user_url',
                                  $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUMS_LASTPOSTED'));
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time']));
                $tpl->SetVariable('lastpost_url',
                                  $this->GetURLFor('Topic', array('id' => $topic['id']))
                );
                $tpl->ParseBlock('topics/topic/lastpost');
            }

            $tpl->ParseBlock('topics/topic');
        }

        $tpl->SetBlock('topics/actions');
        $tpl->SetVariable('newtopic_lbl', _t('FORUMS_NEWTOPIC'));
        $tpl->SetVariable('newtopic_url', $this->GetURLFor('NewTopic', array('fid' => $get['fid'])));
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
        $rqst = $request->get(array('fid'));
        if (empty($rqst['fid'])) {
            return false;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $forum  = $fModel->GetForum($rqst['fid']);
        if (Jaws_Error::IsError($forum) || empty($forum)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditTopic.html');
        $tpl->SetBlock('edittopic');

        $tpl->SetVariable('forum_title', $forum['title']);
        $tpl->SetVariable('forum_url', $this->GetURLFor('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('title', _t('FORUMS_TOPIC_ADD_TITLE'));
        $tpl->SetVariable('fid', $rqst['fid']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('edittopic/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('edittopic/response');
        }

        // subject
        $tpl->SetBlock('edittopic/subject');
        $tpl->SetVariable('subject', '');
        $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPIC_SUBJECT'));
        $tpl->ParseBlock('edittopic/subject');

        // message
        $tpl->SetVariable('message', '');
        $tpl->SetVariable('lbl_message', _t('FORUMS_POST_MESSAGE'));

        // button
        $tpl->SetVariable('btn_title', _t('FORUMS_TOPIC_ADD_BUTTON'));

        $tpl->ParseBlock('edittopic');
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
        $topic = $request->get(
            array('fid', 'tid', 'subject', 'fast_url', 'message', 'update_reason', 'published'),
            'post'
        );

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        if (empty($topic['tid'])) {
            $tid = $tModel->InsertTopic(
                $GLOBALS['app']->Session->GetAttribute('user'),
                $topic['fid'],
                $topic['subject'],
                $topic['fast_url'],
                $topic['message'],
                $topic['published']
            );
        } else {
            $tid = $tModel->UpdateTopic(
                $topic['tid'],
                $GLOBALS['app']->Session->GetAttribute('user'),
                $topic['fid'],
                $topic['subject'],
                $topic['fast_url'],
                $topic['message'],
                $topic['published']
            );
        }

        if (Jaws_Error::IsError($tid)) {
            $GLOBALS['app']->Session->PushSimpleResponse($tid->getMessage(),
                                                         'Topic');
        } else {
            $topic['tid'] = $tid;
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUMS_TOPIC_UPDATED'),
                                                         'Topic');
        }

        // Redirect
        Jaws_Header::Location(
            $this->GetURLFor('Topic', array('fid' => $topic['fid'], 'tid' => $topic['tid'])),
            true
        );
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

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topicInfo = $tModel->GetTopic((int)($topic['tid']));
        if (Jaws_Error::IsError($topicInfo) || empty($topicInfo)) {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUMS_TOPIC_NOT_FOUND'), 'Topic');
            Jaws_Header::Location($this->GetURLFor('Topic', array('tid' => $topic['tid'])), true);
        }

        $result = $tModel->LockTopic($topicInfo['id'], !$topicInfo['locked']);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($apid->getMessage(), 'Topic');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUMS_TOPIC_LOCKED'), 'Topic');
        }

        Jaws_Header::Location($this->GetURLFor('Topic', array('tid' => $topicInfo['id'])), true);
    }

}