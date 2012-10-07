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
     * @param   int     $fid    Forum's ID
     * @param   int     $limit  Count of topics to be returned
     * @param   int     $offset Offset of data array
     * @return  array   Array of topics or Jaws_Error on failure
     */
    function GetTopics($fid, $limit = false, $offset = null)
    {
        $sql = '
            SELECT
                [[forums_topics]].[id], [[forums_topics]].[subject], [views], [[forums_topics]].[published],
                [[forums_topics]].[createtime], [replies], [[forums_topics]].[locked], [last_post_time],
                [[users]].[nickname],
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
     * Insert new topic
     *
     * @access  public
     * @param   integer $uid
     * @param   integer $fid
     * @param   string  $subject
     * @param   string  $fast_url
     * @param   string  $message
     * @param   boolean $published
     * @return  boolean True on success and Jaws_Error on failure
     */
    function InsertTopic($uid, $fid, $subject, $fast_url, $message, $published = true)
    {
        //uid, fid, subject, first_post_id, last_post_id, last_post_time, views, replies, createtime, locked, published
        $fast_url = empty($fast_url)? $subject : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_topics');


    }

}