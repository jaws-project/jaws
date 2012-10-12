<?php
/**
 * Forum Gadget
 *
 * @category   GadgetModel
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forum_Model_Topics extends Jaws_Model
{
    /**
     * Get topics of forum
     *
     * @access  public
     * @param   int     $fid        Forum's ID
     * @param   bool    $limit      Count of topics to be returned
     * @param   int     $offset     Offset of data array
     * @return  array   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $limit = false, $offset = null)
    {
        $sql = '
            SELECT
                [[forums_topics]].[id], [[forums_topics]].[subject], [views], [[forums_topics]].[published],
                [[forums_topics]].[createtime], [replies], [[forums_topics]].[locked], [last_post_time],
                [[users]].[username], [[users]].[nickname],
                [[forums_topics]].[first_post_id], [[forums_topics]].[last_post_id],
                [[forums_topics]].[uid]
            FROM [[forums_topics]] 
                LEFT JOIN [[forums_posts]] ON [[forums_topics]].[last_post_id] = [[forums_posts]].[id]
                LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id] ';
        $sql.= (empty($fid)? '' : 'WHERE [fid] = {fid} ') . 'ORDER BY [last_post_time] DESC';

        $params = array();
        $params['fid'] = $fid;

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_TOPICS'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Get topic info
     *
     * @access  public
     * @param   int     $tid        Topic's ID
     * @return  array   Array of topic info or Jaws_Error on failure
     */
    function GetTopicInfo($tid)
    {
        $sql = '
            SELECT
                [[forums_topics]].[id], [[forums_topics]].[subject], [views], [[forums_topics]].[published],
                [[forums_topics]].[createtime], [replies], [[forums_topics]].[locked], [last_post_time],
                [[users]].[username], [[users]].[nickname],
                [[forums_topics]].[first_post_id], [[forums_topics]].[last_post_id],
                [[forums_topics]].[uid]
            FROM [[forums_topics]] 
                LEFT JOIN [[forums_posts]] ON [[forums_topics]].[last_post_id] = [[forums_posts]].[id]
                LEFT JOIN [[users]] ON [[forums_posts]].[uid] = [[users]].[id] 
                WHERE [tid] = {tid} ';

        $params = array();
        $params['tid'] = $tid;

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('FORUM_ERROR_GET_TOPIC_INFO'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Insert new topic
     *
     * @access  public
     * @param   int     $uid
     * @param   int     $fid
     * @param   string  $subject
     * @param   string  $fast_url
     * @param   string  $message
     * @param   bool    $published
     */
    function InsertTopic($uid, $fid, $subject, $fast_url, $message, $published = true)
    {
        //uid, fid, subject, first_post_id, last_post_id, last_post_time, views, replies, createtime, locked, published
        $fast_url = empty($fast_url)? $subject : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_topics');

        $sql = "
            INSERT INTO [[forums_topics]]
                ([uid], [fid], [subject], [fast_url], [last_post_time], [createtime], [published])
            VALUES
                ({uid}, {fid}, {subject}, {fast_url}, {now}, {now}, {published})";

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['uid']        = $uid;
        $params['fid']        = (int)$fid;
        $params['subject']    = $xss->filter($subject);
        $params['fast_url']   = $xss->filter($fast_url);
        $params['now']        = $GLOBALS['db']->Date();
        $params['published']  = (int)$published;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        $topic_id = $GLOBALS['db']->lastInsertID('forums_topics', 'id');

        $sql = "
            INSERT INTO [[forums_posts]]
                ([tid], [uid], [message], [ip], [createtime])
            VALUES
                ({tid}, {uid}, {message}, {ip}, {now})";

        $params['tid']       = $topic_id;
        $params['message']   = $xss->filter($message);
        $params['ip']        = $_SERVER['REMOTE_ADDR'];
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        $post_id = $GLOBALS['db']->lastInsertID('forums_posts', 'id');

        $sql = 'UPDATE [[forums_topics]] SET
                [first_post_id]       = {first_post_id},
                [last_post_id]        = {last_post_id},
                [last_post_time]      = {now}
            WHERE [id] = {tid}';

        $params['first_post_id']  = $post_id;
        $params['last_post_id']   = $post_id;
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        return $topic_id;
    }

}