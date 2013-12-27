<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Topics extends Jaws_Gadget_Model
{
    /**
     * Get topic info
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @param   int     $fid    Forum ID
     * @return  mixed   Array of topic info or Jaws_Error on failure
     */
    function GetTopic($tid, $fid = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $table->select(
                'forums_topics.id:integer', 'fid:integer', 'subject', 'views:integer', 'replies:integer',
                'first_post_id:integer', 'first_post_uid:integer', 'first_post_time:integer', 'last_post_id:integer',
                'last_post_time:integer','forums_topics.published:boolean', 'forums_topics.locked:boolean',
                'forums.title as forum_title', 'forums.fast_url as forum_fast_url',
                'forums.last_topic_id as forum_last_topic_id:integer',
                'forums_posts.message', 'update_reason',
                'users.username', 'users.nickname', 'users.email'
        );
        $table->join('forums', 'forums_topics.fid', 'forums.id', 'left');
        $table->join('forums_posts', 'forums_topics.first_post_id', 'forums_posts.id', 'left');
        $table->join('users', 'forums_posts.uid', 'users.id', 'left');
        $table->where('forums_topics.id', $tid);

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
     * Get topics of forum
     *
     * @access  public
     * @param   int     $fid        Forum ID
     * @param   int     $published  Is Published ?
     * @param   int     $uid        User id
     * @param   int     $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $published = null, $uid = null, $limit = 0, $offset = null)
    {
        $perm = $this->gadget->GetPermission('ForumAccess', $fid);
        if(is_null($perm)) {
            return Jaws_Error::raiseError(_t('GLOBAL_HTTP_ERROR_CONTENT_404'), 404);
        }
        if (!$perm) {
            return Jaws_Error::raiseError(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $table->select(
                'forums_topics.id:integer', 'fid:integer', 'subject', 'views:integer', 'replies:integer',
                'first_post_id:integer', 'first_post_uid:integer', 'first_post_time:integer',
                'last_post_id:integer', 'last_post_uid:integer', 'last_post_time:integer',
                'fuser.username as first_username', 'fuser.nickname as first_nickname',
                'luser.username as last_username', 'luser.nickname as last_nickname',
                'locked:boolean', 'published:boolean'
        );
        $table->join('users as fuser', 'forums_topics.first_post_uid', 'fuser.id', 'left');
        $table->join('users as luser', 'forums_topics.last_post_uid', 'luser.id', 'left');
        $table->where('fid', $fid)->orderBy('last_post_time desc')->limit($limit, $offset);

        if (empty($uid)) {
            if ($published !== null) {
                $table->and()->where('published', $published);
            }
        } else {
            if ($published === null) {
                $table->and()->openWhere('published', true)->or()->closeWhere('first_post_uid', $uid);
            } else if ($published === true){
                $table->and()->where('published', $published);
            } else {
                $table->and()->where('published', $published)->and('first_post_uid', $uid);
            }
        }

        $result = $table->fetchAll();
        return $result;
    }

    /**
     * Get user's topics
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   User's topics array or Jaws_Error on failure
     */
    function GetUserTopics($uid, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $table->select(
                'forums_topics.id:integer', 'fid:integer', 'subject',
                'forums_topics.views:integer', 'forums_topics.replies:integer',
                'first_post_id:integer', 'first_post_uid:integer', 'first_post_time:integer',
                'last_post_id:integer', 'last_post_uid:integer', 'last_post_time:integer',
                'luser.username as last_username', 'luser.nickname as last_nickname',
                'forums_topics.locked:boolean', 'forums_topics.published:boolean',
                'forums.title', 'forums.fast_url as forum_fast_url'
        );
        $table->join('forums', 'forums_topics.fid', 'forums.id', 'left');
        $table->join('users as luser', 'forums_topics.last_post_uid', 'luser.id', 'left');
        $table->where('first_post_uid', $uid)->orderBy('forums_topics.last_post_time desc');
        $result = $table->limit($limit, $offset)->fetchAll();
        return $result;
    }

    /**
     * Get topic count of user
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @return  mixed   Count of user's posts or Jaws_Error on failure
     */
    function GetUserTopicCount($uid)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $count = $table->select('count(id)')->where('first_post_uid', $uid)->fetchOne();
        return $count;
    }

    /**
     * Get recent updated topics
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   int     $limit  Count of topics to be returned
     * @return  mixed   Recent topics array or Jaws_Error on failure
     */
    function GetRecentTopics($gid = '', $limit = 0)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $table->select(
                'forums_topics.id:integer', 'fid:integer', 'subject', 'forums_posts.message',
                'replies', 'last_post_id:integer', 'last_post_uid:integer', 'last_post_time:integer',
                'users.username', 'users.nickname'
        );
        $table->join('forums_posts', 'forums_topics.last_post_id', 'forums_posts.id', 'left');
        $table->join('forums', 'forums_topics.fid', 'forums.id', 'left');
        $table->join('users', 'forums_topics.last_post_uid', 'users.id', 'left');

        if (!empty($gid)) {
            $table->where('forums.gid', $gid);
        }
        $result = $table->orderBy('forums_topics.last_post_time desc')->limit($limit, 0)->fetchAll();

        $topics = array();
        foreach ($result as $topic) {
            if ($this->gadget->GetPermission('ForumAccess', $topic['fid'])) {
                $topics[] = $topic;
            }
        }

        return $topics;
    }

    /**
     * Insert new topic
     *
     * @access  public
     * @param   int     $uid        User's ID
     * @param   int     $fid        Forum ID
     * @param   string  $subject    Topic subject
     * @param   string  $message    Topic first post content
     * @param   mixed   $attachment Topic first post attachment
     * @param   bool    $published  Must be published?
     * @return  mixed   Topic ID on successfully or Jaws_Error on failure
     */
    function InsertTopic($uid, $fid, $subject, $message, $attachment = null, $published = true)
    {
        if (!$this->gadget->GetPermission('ForumAccess', $fid)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $data['fid']              = (int)$fid;
        $data['subject']          = $subject;
        $data['first_post_uid']   = $uid;
        $data['last_post_uid']    = $uid;
        $data['published']        = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('forums_topics');
        //Start Transaction
        $table->beginTransaction();

        $tid = $table->insert($data)->exec();
        if (Jaws_Error::IsError($tid)) {
            return $tid;
        }

        $pid = 0;
        $pModel = $this->gadget->model->load('Posts');
        if (!Jaws_Error::IsError($pModel)) {
            $pid = $pModel->InsertPost($uid, $tid, $data['fid'], $message, $attachment, true);
            if (Jaws_Error::IsError($pid)) {
                return $pid;
            }
        }

        //Commit Transaction
        $table->commit();

        return $tid;
    }

    /**
     * Update topic
     *
     * @access  public
     * @param   int     $target         Forum ID
     * @param   int     $fid            Old forum ID
     * @param   int     $tid            Topic ID
     * @param   int     $pid            Topic first post ID
     * @param   int     $uid            User's ID
     * @param   string  $subject        Topic subject
     * @param   string  $message        First post content
     * @param   mixed   $attachment     First post attachment
     * @param   string  $old_attachment First post old attachment
     * @param   bool    $published      Topic publish status
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopic($target, $fid, $tid, $pid, $uid, $subject, $message, $attachment = null,
        $published = null, $update_reason = '')
    {
        if (!$this->gadget->GetPermission('ForumAccess', $fid)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $data['fid']    = (int)$target;
        $data['subject']   = $subject;
        $data['published'] = $published;

        $table = Jaws_ORM::getInstance()->table('forums_topics');
        //Start Transaction
        $table->beginTransaction();

        $result = $table->update($data)->where('id', $tid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $pModel = $this->gadget->model->load('Posts');
        $result = $pModel->UpdatePost($pid, $uid, $message, $attachment, $update_reason);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //Commit Transaction
        $table->commit();

        // update forums statistics if topic moved
        if ($target != $fid) {
            $fModel = $this->gadget->model->load('Forums');
            // old forum
            $result = $fModel->UpdateForumStatistics($fid);
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // new forum
            $result = $fModel->UpdateForumStatistics($target);
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
        }

        return  $tid;
    }

    /**
     * Delete topic
     *
     * @access  public
     * @param   int     $tid        Topic ID
     * @param   int     $fid        Forum ID
     * @param   mixed   $attachment Topic first post attachment
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeleteTopic($tid, $fid)
    {
        if (!$this->gadget->GetPermission('ForumAccess', $fid)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_ACCESS_DENIED'), 403);
        }

        $aModel = $this->gadget->model->load('Attachments');
        $topicAttachments = $aModel->GetTopicAttachments($tid);
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        //Start Transaction
        $table->beginTransaction();
        $result = $table->delete()->where('tid', $tid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $result = $table->delete()->where('id', $tid)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //Commit Transaction
        $table->commit();

        // remove attachment file
        foreach ($topicAttachments as $attachment) {
            if (!empty($attachment['filename'])) {
                $aModel->DeleteAttachmentWithFName($attachment['id'], $attachment['filename']);
            }
        }

        $fModel = $this->gadget->model->load('Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($fid);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Update last_post_id, last_post_uid, last_post_time and count of replies
     *
     * @access  public
     * @param   int     $tid Topic ID       Topic ID
     * @param   int     $first_post_id      First post ID
     * @param   integer $first_post_time    First post time
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopicStatistics($tid, $first_post_id = 0, $first_post_time = null)
    {
        $table = Jaws_ORM::getInstance()->table('forums_posts');
        $table->select('id:integer', 'uid:integer', 'insert_time:integer');
        $last_post = $table->where('tid', $tid)->orderBy('id desc')->fetchRow();
        if (Jaws_Error::IsError($last_post)) {
            return $last_post;
        }

        if (empty($last_post)) {
            $last_post = array(
                'id' => 0,
                'uid' => 0,
                'insert_time' => null
            );
        }

        if (!empty($first_post_id)) {
            $data['first_post_id']   = (int)$first_post_id;
            $data['first_post_time'] = $first_post_time;
        }

        $data['last_post_id']   = $last_post['id'];
        $data['last_post_time'] = $last_post['insert_time'];
        $data['last_post_uid']  = $last_post['uid'];

        $data['replies'] = Jaws_ORM::getInstance()->table('forums_posts')->select('count(id)')->where('tid', $tid);
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $result = $table->update($data)->where('id', $tid)->exec();
        return $result;
    }

    /**
     * Lock/UnLock topic
     *
     * @access  public
     * @param   int     $tid        Topic ID
     * @param   bool    $locked     True: Locked, False: UnLocked
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function LockTopic($tid, $locked)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $result = $table->update(array('locked' => $locked))->where('id', $tid)->exec();
        return $result;
    }

    /**
     * Publish/Draft topic
     *
     * @access  public
     * @param   int     $tid        Topic ID
     * @param   bool    $published  True: Published, False: Draft
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function PublishTopic($tid, $published)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $result = $table->update(array('published' => $published))->where('id', $tid)->exec();
        return $result;
    }

    /**
     * Update topic views
     *
     * @access  public
     * @param   int     $tid    Topic ID
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdateTopicViews($tid)
    {
        $table = Jaws_ORM::getInstance()->table('forums_topics');
        $result = $table->update(
            array(
                'views' => $table->expr('views + ?', 1)
            )
        )->where('id', $tid)->exec();
        return $result;
    }

    /**
     * Mails add/edit topic notification to the admins
     *
     * @access  public
     * @param   string  $event_type     Event type
     * @param   string  $forum_title    Forum title
     * @param   string  $topic_link     Link of the topic
     * @param   string  $topic_subject  Topic subject
     * @param   string  $topic_message  Post message content
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function TopicNotification($event_type, $forum_title, $topic_link, $topic_subject, $topic_message)
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

        $event_subject = _t("FORUMS_TOPICS_{$event_type}_NOTIFICATION_SUBJECT", $forum_title);
        $event_message = _t("FORUMS_TOPICS_{$event_type}_NOTIFICATION_MESSAGE", $lnkProfile->Get());

        $tpl = $this->gadget->template->load('TopicNotification.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('notification', $event_message);
        $tpl->SetVariable('lbl_subject',  _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->SetVariable('subject',      $topic_subject);
        $tpl->SetVariable('lbl_message',  _t('FORUMS_POSTS_MESSAGE'));
        $tpl->SetVariable('message',      $topic_message);
        $tpl->SetVariable('lbl_url',      _t('FORUMS_TOPIC'));
        $tpl->SetVariable('url',          $topic_link);
        $tpl->SetVariable('site_name',    $site_name);
        $tpl->SetVariable('site_url',     $site_url);
        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        $ObjMail->AddRecipient('', 'to');
        $ObjMail->SetSubject($event_subject);
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

}