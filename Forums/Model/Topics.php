<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
  * @author     Hamid Reza Aboutalebi <abt_am@yahoo.com>
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
        $params = array();
        $params['tid'] = (int)$tid;
        $params['fid'] = (int)$fid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [fid], [subject], [views], [replies],
                [first_post_id], [first_post_uid], [first_post_time], [last_post_id], [last_post_time],
                [[forums_topics]].[published], [[forums_topics]].[locked],
                [[forums]].[title] as forum_title, [[forums]].[fast_url] as forum_fast_url,
                [[forums]].[last_topic_id] as forum_last_topic_id,
                [[forums_posts]].[message], [attachment_host_fname], [update_reason],
                [[users]].[username], [[users]].[nickname], [[users]].[email]
            FROM
                [[forums_topics]]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[forums_posts]] ON [[forums_topics]].[first_post_id] = [[forums_posts]].[id]
            LEFT JOIN
                [[users]] ON [[forums_posts]].[uid] = [[users]].[id]
            WHERE
                [[forums_topics]].[id] = {tid}';

        if (!empty($fid)) {
            $sql .= ' AND [fid] = {fid}';
        }

        $types = array(
            'integer', 'integer', 'text', 'integer', 'integer',
            'integer', 'integer', 'integer', 'integer', 'integer',
            'boolean', 'boolean',
            'text', 'text',
            'integer',
            'text', 'text', 'text',
            'text', 'text', 'text',
        );

        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        return $result;
    }

    /**
     * Get topics of forum
     *
     * @access  public
     * @param   int     $fid        Forum ID
     * @param   int     $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  mixed   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $limit = 0, $offset = null)
    {
        $params = array();
        $params['fid'] = $fid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [fid], [subject], [views], [replies],
                [first_post_id], [first_post_uid], [first_post_time],
                [last_post_id], [last_post_uid], [last_post_time],
                fuser.[username] as first_username, fuser.[nickname] as first_nickname,
                luser.[username] as last_username, luser.[nickname] as last_nickname,
                [locked], [published]
            FROM
                [[forums_topics]]
            LEFT JOIN 
                [[users]] as fuser ON [[forums_topics]].[first_post_uid] = fuser.[id]
            LEFT JOIN 
                [[users]] as luser ON [[forums_topics]].[last_post_uid] = luser.[id]
            WHERE
                [fid] = {fid}
            ORDER BY
                [last_post_time] DESC';

        $types = array(
            'integer', 'integer', 'text', 'integer', 'integer',
            'integer', 'integer', 'integer',
            'integer', 'integer', 'integer',
            'text', 'text',
            'text', 'text',
            'boolean', 'boolean',
        );

        if (!empty($limit)) {
            $result = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        return $result;
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
        $params = array();
        $params['gid'] = $gid;

        $sql = '
            SELECT
                [[forums_topics]].[id], [fid], [subject], [[forums_posts]].[message],
                [replies], [last_post_id], [last_post_uid], [last_post_time],
                [[users]].[username], [[users]].[nickname]
            FROM
                [[forums_topics]]
            LEFT JOIN
                [[forums_posts]] ON [[forums_topics]].[last_post_id] = [[forums_posts]].[id]
            LEFT JOIN
                [[forums]] ON [[forums_topics]].[fid] = [[forums]].[id]
            LEFT JOIN
                [[users]] ON [[forums_topics]].[last_post_uid] = [[users]].[id]
            ';

        if (!empty($gid)) {
            $sql.= 'WHERE [[forums]].[gid] = {gid}';
        }
        $sql.= ' ORDER BY [[forums_topics]].[last_post_time] DESC';

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
        $params = array();
        $params['uid']       = $uid;
        $params['fid']       = (int)$fid;
        $params['subject']   = $subject;
        $params['published'] = (bool)$published;

        $sql = '
            INSERT INTO [[forums_topics]]
                ([fid], [subject], [first_post_uid], [last_post_uid], [published])
            VALUES
                ({fid}, {subject}, {uid}, {uid}, {published})';

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $tid = $GLOBALS['db']->lastInsertID('forums_topics', 'id');
        if (Jaws_Error::IsError($tid)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $tid;
        }

        $pid = 0;
        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        if (!Jaws_Error::IsError($pModel)) {
            $pid = $pModel->InsertPost($params['uid'], $tid, $params['fid'], $message, $attachment, true);
            if (Jaws_Error::IsError($pid)) {
                //Rollback Transaction
                $GLOBALS['db']->dbc->rollback();
                return $pid;
            }
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

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
    function UpdateTopic($target, $fid, $tid, $pid, $uid, $subject, $message, $attachment = null, $old_attachment = '',
        $published = null, $update_reason = '')
    {
        $params = array();
        $params['target']    = (int)$target;
        $params['tid']       = (int)$tid;
        $params['subject']   = $subject;
        $params['published'] = true;

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $sql = '
            UPDATE [[forums_topics]] SET
                [fid]       = {target},
                [subject]   = {subject},
                [published] = {published}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Posts');
        $result = $pModel->UpdatePost($pid, $uid, $message, $attachment, $old_attachment, $update_reason);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

        // update forums statistics if topic moved
        if ($target != $fid) {
            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
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
    function DeleteTopic($tid, $fid, $attachment = '')
    {
        $params = array();
        $params['tid'] = $tid;

        $sql = '
            DELETE FROM [[forums_posts]]
            WHERE
                [tid] = {tid}';

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        $sql = '
            DELETE FROM [[forums_topics]]
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();

        // remove attachment file
        if (!empty($attachment)) {
            Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $attachment);
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
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
        $params = array();
        $params['tid'] = (int)$tid;
        $params['first_post_id']   = $first_post_id;
        $params['first_post_time'] = $first_post_time;
        if (empty($first_post_id)) {
            $first_post_id   = '[first_post_id]';
            $first_post_time = '[first_post_time]';
        } else {
            $first_post_id   = '{first_post_id}';
            $first_post_time = '{first_post_time}';
        }

        $sql = '
            SELECT
                [id], [uid], [insert_time]
            FROM
                [[forums_posts]]
            WHERE
                [[forums_posts]].[tid] = {tid}
            ORDER BY
                [id] DESC';

        $types = array('integer', 'integer' , 'integer');
        $last_post = $GLOBALS['db']->queryRow($sql, $params, $types);
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

        $params['last_post_id']   = $last_post['id'];
        $params['last_post_time'] = $last_post['insert_time'];
        $params['last_post_uid']  = $last_post['uid'];

        $sql = "
            UPDATE [[forums_topics]] SET
                [first_post_id]   = $first_post_id,
                [first_post_time] = $first_post_time,
                [last_post_id]   = {last_post_id},
                [last_post_time] = {last_post_time},
                [last_post_uid]  = {last_post_uid},
                [replies]        = (
                    SELECT
                        COUNT([[forums_posts]].[id])
                    FROM
                        [[forums_posts]]
                    WHERE
                        [[forums_posts]].[tid] = {tid}
                )
            WHERE
                [id] = {tid}";

        $result = $GLOBALS['db']->query($sql, $params);
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
        $params = array();
        $params['tid']    = (int)$tid;
        $params['locked'] = $locked;

        $sql = '
            UPDATE [[forums_topics]] SET
                [locked]   = {locked}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $params = array();
        $params['tid'] = (int)$tid;
        $params['one'] = 1;

        $sql = '
            UPDATE [[forums_topics]] SET
                [views] = [views] + {one}
            WHERE
                [id] = {tid}';

        $result = $GLOBALS['db']->query($sql, $params);
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
                true
            )
        );

        $event_subject = _t("FORUMS_TOPICS_{$event_type}_NOTIFICATION_SUBJECT", $forum_title);
        $event_message = _t("FORUMS_TOPICS_{$event_type}_NOTIFICATION_MESSAGE", $lnkProfile->Get());

        $tpl = new Jaws_Template('gadgets/Forums/templates/');
        $tpl->Load('TopicNotification.html');
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

        require_once JAWS_PATH . '/include/Jaws/Mail.php';
        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        $ObjMail->AddRecipient('', 'to');
        $ObjMail->SetSubject($event_subject);
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

}