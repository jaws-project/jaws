<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Posts extends Jaws_Gadget_Model
{
    /**
     * Get post data
     *
     * @access  public
     * @param   int     $pid    Post ID
     * @param   int     $tid    Topic ID
     * @param   int     $fid    Forum ID
     * @return  mixed   Array of post data or Jaws_Error on failure
     */
    function GetPost($pid, $tid = null, $fid = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $table->select('
                forums_posts.id:integer', 'forums_posts.uid:integer', 'tid:integer', 'message', 'forums_posts.insert_time:integer',
                'attachments',
                'update_uid:integer', 'update_reason', 'update_time:integer', 'forums_posts.status:integer',
                'forums_topics.fid:integer', 'forums_topics.subject', 'forums_topics.locked as topic_locked:boolean',
                'first_post_id as topic_first_post_id:integer', 'first_post_time as topic_first_post_time:integer',
                'last_post_id as topic_last_post_id:integer', 'last_post_time as topic_last_post_time:integer',
                'forums.title as forum_title', 'forums.fast_url as forum_fast_url',
                'forums.last_topic_id as forum_last_topic_id:integer',
                'users.username', 'users.nickname', 'users.registered_date as user_registered_date:integer',
                'users.email', 'users.avatar', 'users.last_update as user_last_update:integer'
        );
        $table->join('forums_topics', 'forums_posts.tid', 'forums_topics.id', 'left');
        $table->join('forums', 'forums_topics.fid', 'forums.id', 'left');
        $table->join('users', 'forums_posts.uid', 'users.id', 'left');

        $table->where('forums_posts.id', $pid);

        if (!empty($tid)) {
            $table->and()->where('tid', $tid);
        }
        if (!empty($fid)) {
            $table->and()->where('fid', $fid);
        }

        $result = $table->fetchRow();
        if (!$this->gadget->GetPermission('ForumAccess', $result['fid'])) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        return $result;
    }

    /**
     * Get posts of topic
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetPosts($tid, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $table->select(
            'forums_posts.id', 'uid', 'message',
                'attachments', 'update_uid',
                'update_reason', 'update_time', 'forums_posts.insert_time', 'forums_posts.status',
                'cuser.username', 'cuser.nickname', 'cuser.registered_date as user_registered_date',
                'cuser.email', 'cuser.avatar', 'cuser.last_update as user_last_update',
                'uuser.username as updater_username', 'uuser.nickname as updater_nickname'
        );
        $table->join('users as cuser', 'forums_posts.uid', 'cuser.id', 'left');
        $table->join('users as uuser', 'forums_posts.update_uid', 'uuser.id', 'left');
        $result = $table->where('tid', $tid)->orderBy('insert_time asc')->limit($limit, $offset)->fetchAll();
        return $result;
    }

    /**
     * Get posts count of user
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @return  mixed   Count of user's posts or Jaws_Error on failure
     */
    function GetUserPostsCount($uid)
    {
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $count = $table->select('count(id)')->where('uid', $uid)->fetchOne();
        return $count;
    }

    /**
     * Get user's posts
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   User's posts array or Jaws_Error on failure
     */
    function GetUserPosts($uid, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $table->select('forums_posts.id', 'forums_posts.tid', 'uid', 'message', 'forums_posts.insert_time',
                       'forums_topics.fid', 'forums_topics.subject', 'forums_topics.replies as topic_replies');
        $table->join('forums_topics', 'forums_posts.tid', 'forums_topics.id', 'left');
        $table->join('forums', 'forums_topics.fid', 'forums.id', 'left');
        $table->where('uid', $uid)->orderBy('forums_posts.insert_time asc');
        $result = $table->limit($limit, $offset)->fetchAll();
        return $result;
    }

    /**
     * Insert new post
     *
     * @access  public
     * @param   int     $uid                User's ID
     * @param   int     $tid                Topic ID
     * @param   int     $fid                Forum ID
     * @param   string  $message            Post content
     * @param   mixed   $attachments        Post attachments
     * @param   bool    $new_topic  Is this first post of topic?
     * @return  mixed   Post ID on successfully or Jaws_Error on failure
     */
    function InsertPost($uid, $tid, $fid, $message, $attachments = null, $new_topic = false)
    {
        if (!$this->gadget->GetPermission('ForumAccess', $fid)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $data['uid']            = (int)$uid;
        $data['tid']            = (int)$tid;
        $data['insert_time']    = time();
        $data['ip']             = $_SERVER['REMOTE_ADDR'];
        $data['message']        = $message;
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $pid = $table->insert($data)->exec();
        if (Jaws_Error::IsError($pid)) {
            return $pid;
        }

        $tModel = $this->gadget->model->load('Topics');
        if (!Jaws_Error::IsError($tModel)) {
            $result = $tModel->UpdateTopicStatistics($data['tid'], $new_topic? $pid : 0, $data['insert_time']);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $fModel = $this->gadget->model->load('Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($fid);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (!is_null($attachments)) {
            $aModel = $this->gadget->model->load('Attachments');
            $aModel->InsertAttachments($pid, $attachments);
            $this->UpdatePostAttachCount($pid);
        }

        return $pid;
    }

    /**
     * Update post
     *
     * @access  public
     * @param   int     $pid            Post ID
     * @param   int     $uid            User's ID
     * @param   string  $message        Post content
     * @param   mixed   $attachments     Post attachments
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdatePost($pid, $uid, $message, $attachments = null, $update_reason = '')
    {
        $post = $this->GetPost($pid);
        if (Jaws_Error::IsError($post)) {
            return $post;
        }
        $data['update_uid']     = (int)$uid;
        $data['update_time']    = time();
        $data['message']        = $message;
        $data['update_reason']  = $update_reason;

        if (!is_null($attachments)) {
            $aModel = $this->gadget->model->load('Attachments');
            $aModel->InsertAttachments($pid, $attachments);
        }
        $this->UpdatePostAttachCount($pid);

        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $result = $table->update($data)->where('id', $pid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $pid;
    }

    /**
     * Delete post
     *
     * @access  public
     * @param   int     $pid        Post ID
     * @param   int     $tid        Topic ID
     * @param   int     $fid        Forum ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeletePost($pid, $tid, $fid)
    {
        if (!$this->gadget->GetPermission('ForumAccess', $fid)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $result = $table->delete()->where('id', $pid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // remove attachment file
        $aModel = $this->gadget->model->load('Attachments');
        $aModel->DeletePostAttachments($pid);

        $tModel = $this->gadget->model->load('Topics');
        $result = $tModel->UpdateTopicStatistics($tid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $fModel = $this->gadget->model->load('Forums');
        $result = $fModel->UpdateForumStatistics($fid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Mails add/edit post notification to the admins and topic creator
     *
     * @access  public
     * @param   string  $email          Topic creator's email 
     * @param   string  $event_type     Event type
     * @param   string  $forum_title    Forum title
     * @param   string  $post_link      Link of the post
     * @param   string  $topic_subject  Topic subject
     * @param   string  $post_message   Post message content
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function PostNotification($email, $event_type, $forum_title, $post_link, $topic_subject, $post_message, $reason = null)
    {
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');
        $event_type = strtoupper($event_type);

        // user profile link
        $lnkProfile =& Piwi::CreateWidget(
            'Link',
            $GLOBALS['app']->Session->GetAttribute('nickname'),
            $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'Profile',
                array('user' => $GLOBALS['app']->Session->GetAttribute('username')),
                true
            )
        );

        $event_subject = _t("FORUMS_POSTS_{$event_type}_NOTIFICATION_SUBJECT", $forum_title);
        $event_message = _t("FORUMS_POSTS_{$event_type}_NOTIFICATION_MESSAGE", $lnkProfile->Get());

        $tpl = $this->gadget->template->load('PostNotification.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('notification', $event_message);
        $tpl->SetVariable('lbl_subject',  _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->SetVariable('subject',      $topic_subject);
        $tpl->SetVariable('lbl_message',  _t('FORUMS_POSTS_MESSAGE'));
        $tpl->SetVariable('message',      $post_message);
        $tpl->SetVariable('lbl_url',      _t('FORUMS_TOPIC'));
        $tpl->SetVariable('url',          $post_link);
        $tpl->SetVariable('site_name',    $site_name);
        $tpl->SetVariable('site_url',     $site_url);

        if (!empty($reason)) {
            $tpl->SetBlock('notification/reason');
            $tpl->SetVariable('lbl_reason',  _t('FORUMS_POSTS_REASON'));
            $tpl->SetVariable('lbl_reason',  $reason);
            $tpl->ParseBlock('notification/reason');
        }

        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        if (empty($email)) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($email);
            $ObjMail->AddRecipient('', 'cc');
        }
        $ObjMail->SetSubject($event_subject);
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

    /**
     * Edit count of attachments for one post
     *
     * @access  public
     * @param   int     $pid           Post ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdatePostAttachCount($pid)
    {
        $aModel = $this->gadget->model->load('Attachments');
        $attachCount = $aModel->GetAttachmentsCount($pid);
        $postsTable = Jaws_ORM::getInstance()->table('forums_posts');
        $postsTable->update(array('attachments' => $attachCount))->where('id', $pid);
        return $postsTable->exec();          
    }
}