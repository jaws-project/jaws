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
     * Display topic's posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Posts()
    {
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('tid', 'page'), 'get');

        $model = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $model->GetTopic($get['tid']);
        if (Jaws_Error::IsError($topic)) {
            return false;
        }
        $model->UpdateTopicViews($topic['id']);

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('Topic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('title', $topic['subject']);

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
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
            $tpl->SetVariable('posts_lbl',_t('FORUMS_USER_POST_COUNT'));
            $tpl->SetVariable('joined_lbl',_t('FORUMS_USER_JOINED_TIME'));
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTED_BY'));
            //
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable('message', $post['message']);
            if ($post['last_update_uid'] != 0) {
                $userInfo = $jUser->GetUser((int)$post['last_update_uid']);
                $tpl->SetBlock('topic/post/update');
                $tpl->SetVariable('updatedby_lbl', _t('FORUMS_POST_UPDATEDBY'));
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
            $tpl->SetVariable('url_editpost', $this->GetURLFor('EditPost', array('pid' => $post['id'])));
            $tpl->SetVariable('lbl_deletepost',_t('GLOBAL_DELETE'));
            $tpl->SetVariable('url_deletepost', $this->GetURLFor('DeletePost', array('pid' => $post['id'])));
            $tpl->ParseBlock('topic/post/actions');

            $tpl->ParseBlock('topic/post');
        }

        $tpl->SetBlock('topic/actions');
        $tpl->SetVariable('lbl_newpost', _t('FORUMS_NEWPOST'));
        $tpl->SetVariable('url_newpost',
                          $this->GetURLFor('NewPost', array('tid' => $get['tid']))
        );
        if ($topic['locked']) {
            $tpl->SetVariable('lbl_lock_topic', _t('FORUMS_UNLOCK_TOPIC'));
        } else {
            $tpl->SetVariable('lbl_lock_topic', _t('FORUMS_LOCK_TOPIC'));
        }
        $tpl->SetVariable('url_lock_topic',
                          $this->GetURLFor('LockTopic', array('tid' => $get['tid']))
        );
        $tpl->ParseBlock('topic/actions');

        $tpl->ParseBlock('topic');
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
        $request =& Jaws_Request::getInstance();
        $req = $request->get(array('message', 'tid'));
        if (empty($req['tid'])) {
            return false;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($req['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditPost.html');
        $tpl->SetBlock('editpost');

        $tpl->SetVariable('lbl_topic', $topic['subject']);
        $tpl->SetVariable('url_topic',
                          $this->GetURLFor('Topic', array('tid' => $topic['id']))
        );
        $tpl->SetVariable('title', _t('FORUMS_POST_ADD_TITLE'));
        $tpl->SetVariable('separator', _t('FORUMS_SEPARATOR'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('tid', $req['tid']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('editpost/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('editpost/response');
        }

        $tpl->SetVariable('lbl_message', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('editpost', _t('FORUMS_POST_ADD_BUTTON'));
        $tpl->SetVariable('message', '');

        $tpl->ParseBlock('editpost');
        return $tpl->Get();
    }

    /**
     * Show edit post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditPost()
    {
        $request =& Jaws_Request::getInstance();
        $req = $request->get(array('message', 'pid'));
        if (empty($req['pid'])) {
            return false;
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $post = $pModel->GetPost($req['pid']);
        if (Jaws_Error::IsError($post) || empty($post)) {
            return false;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($post['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('EditPost.html');
        $tpl->SetBlock('editpost');

        $tpl->SetVariable('lbl_topic', $topic['subject']);
        $tpl->SetVariable('url_topic',
                          $this->GetURLFor('Topic', array('tid' => $topic['id']))
        );
        $tpl->SetVariable('title', _t('FORUMS_POST_ADD_TITLE'));
        $tpl->SetVariable('separator', _t('FORUMS_SEPARATOR'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('tid', $post['tid']);
        $tpl->SetVariable('pid', $post['id']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('editpost/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('editpost/response');
        }

        if ($topic['first_post_id'] == (int)$req['pid']) {
            $tpl->SetBlock('editpost/subject');
            $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPIC_SUBJECT'));
            $tpl->SetVariable('subject', $topic['subject']);
            $tpl->ParseBlock('editpost/subject');
        }
        $tpl->SetVariable('editpost', _t('FORUMS_POST_EDIT_BUTTON'));
        $tpl->SetVariable('lbl_message', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('message', $post['message']);

        $tpl->SetBlock('editpost/update_reason');
        $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POST_UPDATE_REASON'));
        $tpl->SetVariable('update_reason', '');
        $tpl->ParseBlock('editpost/update_reason');

        $tpl->ParseBlock('editpost');
        return $tpl->Get();
    }

    /**
     * Add/Edit a post
     *
     * @access  public
     */
    function UpdatePost()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('tid', 'pid', 'subject', 'message', 'update_reason'), 'post');

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost($GLOBALS['app']->Session->GetAttribute('user'), $post['tid'], $post['message']);
        } else {
            $result = $pModel->UpdatePost(
                $post['pid'],
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['subject'],
                $post['message'],
                $post['update_reason']
            );
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($apid->getMessage(),
                                                         'Topic');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUMS_POST_TOPIC_UPDATED'),
                                                         'Topic');
        }

        Jaws_Header::Location($this->GetURLFor('Topic', array('tid' => $post['tid'])), true);
    }

    /**
     * Delete a post or topic
     *
     * @access  public
     */
    function DeletePost()
    {
        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('DeletePost.html', true);
        if (!$GLOBALS['app']->Session->Logged()) {
            //Add lang
            $tpl->SetBlock('not_allow');
            $tpl->SetVariable('msg', _t('FORUMS_NOT_PERMISON_PLEASE_LOGIN'));
            $tpl->ParseBlock('not_allow');
            return $tpl->Get();
        }

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('pid', 'tid', 'step'));
        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');

        $postInfo = $pModel->GetPost($post['pid']);
        if (Jaws_Error::IsError($postInfo) || empty($postInfo)) {
            return false;
        }
        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topicInfo = $tModel->GetTopic($postInfo['tid']);
        if (!is_null($post['step']) && $post['step'] == 'delete') {
            if ($postInfo['id'] == $topicInfo['first_post_id']) {
                // Delete Topic And All Posts In this
                $tModel->DeleteTopic($topicInfo['id']);
                Jaws_Header::Location($this->GetURLFor('Topics', array('id' => $topicInfo['fid'])));
            } else {
                // Delete Post
                $pModel->DeletePost($postInfo['id']);
                Jaws_Header::Location($this->GetURLFor('Topic', array('tid' => $postInfo['tid'])));
            }
        } else if (!is_null($post['step']) && $post['step'] == 'cancel') {
            Jaws_Header::Location($this->GetURLFor('Topic', array('tid' => $postInfo['tid'])));
        }

        $tpl->SetBlock('deletepost');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('pid',  $postInfo['id']);
        $tpl->SetVariable('tid', $topicInfo['id']);
        $tpl->SetVariable('subject', $topicInfo['subject']);
        $tpl->SetVariable('url', $this->GetURLFor('Topic', array('tid' => $topicInfo['id'])));
        $tpl->SetVariable('title', _t('FORUMS_DELETE_POST'));
        $tpl->SetVariable('separator', _t('FORUMS_SEPARATOR'));

        // Message
        $tpl->SetVariable('delete_message', _t('FORUMS_DELETE_POST_CONFIRM'));

        $date = $GLOBALS['app']->loadDate();
        $tpl->SetVariable('psted_date',   $date->Format($date->ToISO($postInfo['createtime'])));
        $tpl->SetVariable('posted_by',    _t('FORUMS_POSTED_BY'));
        $tpl->SetVariable('user_name',    $postInfo['username']);
        $tpl->SetVariable('lbl_message',  _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('message',      $postInfo['message']);

        $tpl->SetVariable('delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('cancel', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('deletepost');
        return $tpl->Get();
    }

}