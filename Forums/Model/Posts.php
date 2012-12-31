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
        $params = array();
        $params['fid'] = (int)$fid;
        $params['tid'] = (int)$tid;
        $params['pid'] = (int)$pid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[uid], [tid], [message], [[forums_posts]].[insert_time],
                [attachment_host_fname], [attachment_user_fname], [attachment_hits_count],
                [update_uid], [update_reason], [update_time], [[forums_posts]].[status],
                [[forums_topics]].[fid], [[forums_topics]].[subject], [[forums_topics]].[locked] as topic_locked,
                [first_post_id] as topic_first_post_id, [first_post_time] as topic_first_post_time,
                [last_post_id] as topic_last_post_id, [last_post_time] as topic_last_post_time,
                [[forums]].[title] as forum_title, [[forums]].[fast_url] as forum_fast_url,
                [[forums]].[last_topic_id] as forum_last_topic_id,
                [[users]].[username], [[users]].[nickname], [[users]].[registered_date] as user_registered_date,
                [[users]].[email], [[users]].[avatar], [[users]].[last_update] as user_last_update
            FROM
                [[forums_posts]]
            LEFT JOIN
                [[forums_topics]] ON [[forums_posts]].[tid] = [[forums_topics]].[id]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [[forums_posts]].[id] = {pid}';

        if (!empty($tid)) {
            $sql .= ' AND [tid] = {tid}';
        }
        if (!empty($fid)) {
            $sql .= ' AND [fid] = {fid}';
        }

        $types = array(
            'integer', 'integer', 'integer', 'text', 'integer',
            'text', 'text', 'integer',
            'integer', 'text', 'integer', 'integer',
            'integer', 'text', 'boolean',
            'integer', 'integer',
            'integer', 'integer',
            'text', 'text',
            'integer',
            'text', 'text', 'integer',
            'text', 'text', 'integer',
        );

        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
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
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [uid], [message],
                [attachment_host_fname], [attachment_user_fname], [attachment_hits_count], [update_uid],
                [update_reason], [update_time], [[forums_posts]].[insert_time], [[forums_posts]].[status],
                cuser.[username], cuser.[nickname], cuser.[registered_date] as user_registered_date,
                cuser.[email], cuser.[avatar], cuser.[last_update] as user_last_update,
                uuser.[username] as updater_username, uuser.[nickname] as updater_nickname
            FROM
                [[forums_posts]]
            LEFT JOIN
                [[users]] as cuser ON [[forums_posts]].[uid] = cuser.[id]
            LEFT JOIN
                [[users]] as uuser ON [[forums_posts]].[update_uid] = uuser.[id]
            WHERE
                [tid] = {tid}
            ORDER BY
                [insert_time] ASC';

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
        return $result;
    }

    /**
     * Get recent posts
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   int     $limit  Count of posts to be returned
     * @return  mixed   Recent posts array or Jaws_Error on failure
     */
    function GetRecentPosts($gid = '', $limit = 0)
    {
        $params = array();
        $params['gid'] = $gid;

        $sql = '
            SELECT
                [[forums_posts]].[id], [[forums_posts]].[tid], [uid], [message], [[forums_posts]].[insert_time],
                [[forums_topics]].[fid], [[forums_topics]].[subject], [[forums_topics]].[replies] as topic_replies,
                [[users]].[username], [[users]].[nickname]
            FROM
                [[forums_posts]]
            LEFT JOIN
                [[forums_topics]] ON [[forums_posts]].[tid] = [[forums_topics]].[id]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            ';

        if (!empty($gid)) {
            $sql.= 'WHERE [[forums]].[gid] = {gid}';
        }
        $sql.= ' ORDER BY [[forums_posts]].[insert_time] DESC';

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, 0);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
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
        $params = array();
        $params['uid'] = (int)$uid;

        $sql = '
            SELECT COUNT([id])
            FROM [[forums_posts]]
            WHERE [uid] = {uid}';

        $count = $GLOBALS['db']->queryOne($sql, $params);
        return $count;
    }

    /**
     * Insert new post
     *
     * @access  public
     * @param   int     $uid        User's ID
     * @param   int     $tid        Topic ID
     * @param   int     $fid        Forum ID
     * @param   string  $message    Post content
     * @param   mixed   $attachment Post attachment
     * @param   bool    $new_topic  Is this first post of topic?
     * @return  mixed   Post ID on successfully or Jaws_Error on failure
     */
    function InsertPost($uid, $tid, $fid, $message, $attachment = null, $new_topic = false)
    {
        $params = array();
        $params['uid'] = (int)$uid;
        $params['tid'] = (int)$tid;
        $params['now'] = time();
        $params['ip']  = $_SERVER['REMOTE_ADDR'];
        $params['message'] = $message;
        if (empty($attachment)) {
            $params['attachment_host_fname'] = '';
            $params['attachment_user_fname'] = '';
        } else {
            $params['attachment_host_fname'] = $attachment['host_fname'];
            $params['attachment_user_fname'] = $attachment['user_fname'];
        }

        $sql = '
            INSERT INTO [[forums_posts]]
                ([tid], [uid], [message], [attachment_host_fname], [attachment_user_fname], [ip], [insert_time])
            VALUES
                ({tid}, {uid}, {message}, {attachment_host_fname}, {attachment_user_fname}, {ip}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $pid = $GLOBALS['db']->lastInsertID('forums_posts', 'id');
        if (Jaws_Error::IsError($pid)) {
            return $pid;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        if (!Jaws_Error::IsError($tModel)) {
            $result = $tModel->UpdateTopicStatistics($params['tid'], $new_topic? $pid : 0, $params['now']);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!Jaws_Error::IsError($fModel)) {
            $result = $fModel->UpdateForumStatistics($fid);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
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
     * @param   mixed   $attachment     Post attachment
     * @param   string  $old_attachment Post old attachment
     * @param   string  $update_reason  Update reason text
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function UpdatePost($pid, $uid, $message, $attachment = null, $old_attachment = '', $update_reason = '')
    {
        $params = array();
        $params['uid'] = (int)$uid;
        $params['pid'] = (int)$pid;
        $params['now'] = time();
        $params['message'] = $message;
        $params['update_reason'] = $update_reason;
        if (is_null($attachment)) {
            $attachment_host_fname = '[attachment_host_fname]';
            $attachment_user_fname = '[attachment_user_fname]';
            $attachment_hits_count = '[attachment_hits_count]';
        } else {
            $attachment_host_fname = '{attachment_host_fname}';
            $attachment_user_fname = '{attachment_user_fname}';
            $attachment_hits_count = '[attachment_hits_count]';
            if (empty($attachment)) {
                $attachment_hits_count = '{attachment_hits_count}';
                $params['attachment_hits_count'] = 0;
                $params['attachment_host_fname'] = '';
                $params['attachment_user_fname'] = '';
            } else {
                $params['attachment_host_fname'] = $attachment['host_fname'];
                $params['attachment_user_fname'] = $attachment['user_fname'];
            }

            // remove old attachment file
            if (!empty($old_attachment)) {
                Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $old_attachment);
            }
        }

        $sql = "
            UPDATE [[forums_posts]] SET
                [message]            = {message},
                [update_uid]    = {uid},
                [attachment_host_fname] = $attachment_host_fname,
                [attachment_user_fname] = $attachment_user_fname,
                [attachment_hits_count] = $attachment_hits_count,
                [update_reason] = {update_reason},
                [update_time]   = {now}
            WHERE
                [id] = {pid}";

        $result = $GLOBALS['db']->query($sql, $params);
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
     * @param   string  $attachment Post attachment
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function DeletePost($pid, $tid, $fid, $attachment = '')
    {
        $params = array();
        $params['pid'] = (int)$pid;

        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [id] = {pid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // remove attachment file
        if (!empty($attachment)) {
            Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $attachment);
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $result = $tModel->UpdateTopicStatistics($tid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $result = $fModel->UpdateForumStatistics($fid);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Increment attachment download hits
     *
     * @access  public
     * @param   int     $pid    Post ID
     * @return  mixed   True if hits increased successfully or Jaws_Error on error
     */
    function HitAttachmentDownload($pid)
    {
        $sql = '
            UPDATE [[forums_posts]] SET
                [attachment_hits_count] = [attachment_hits_count] + 1
            WHERE
                [id] = {pid}';
        $result = $GLOBALS['db']->query($sql, array('pid' => $pid));
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
    function PostNotification($email, $event_type, $forum_title, $post_link, $topic_subject, $post_message)
    {
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->GetRegistry('site_name', 'Settings');
        $event_type = strtoupper($event_type);

        // user profile link
        $lnkProfile =& Piwi::CreateWidget(
            'Link',
            $GLOBALS['app']->Session->GetAttribute('nickname'),
            $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'Profile',
                array('user' => $GLOBALS['app']->Session->GetAttribute('username')),
                true,
                'site_url'
            )
        );

        $event_subject = _t("FORUMS_POSTS_{$event_type}_NOTIFICATION_SUBJECT", $forum_title);
        $event_message = _t("FORUMS_POSTS_{$event_type}_NOTIFICATION_MESSAGE", $lnkProfile->Get());

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('PostNotification.html');
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
        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        require_once JAWS_PATH . '/include/Jaws/Mail.php';
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

}