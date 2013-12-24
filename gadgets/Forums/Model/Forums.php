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
class Forums_Model_Forums extends Jaws_Gadget_Model
{
    /**
     * Returns array of forum properties
     *
     * @access  public
     * @param   int     $fid    forum ID
     * @return  mixed   Array of forum properties or Jaws_Error on error
     */
    function GetForum($fid)
    {
        $table = Jaws_ORM::getInstance()->table('forums');
        $table->select('id:integer', 'gid:integer', 'title', 'description', 'fast_url', 'topics:integer',
                       'posts:integer', 'order:integer', 'locked:boolean', 'published:boolean');
        if (is_numeric($fid)) {
            $table->where('id', $fid);
        } else {
            $table->where('fast_url', $fid);
        }

        return $table->fetchRow();
    }

    /**
     * Returns a list of  forums at a request level
     *
     * @access  public
     * @param   int     $gid                Group ID
     * @param   bool    $onlyPublished
     * @param   bool    $last_topic_detail
     * @return  mixed   Array with all the available forums or Jaws_Error on error
     */
    function GetForums($gid = false, $onlyAccessible = true, $onlyPublished = false, $last_topic_detail = false)
    {
        $table = Jaws_ORM::getInstance()->table('forums');
        if ($last_topic_detail) {

            $table->select('forums.id:integer', 'forums.title', 'forums.description',
                    'forums.fast_url', 'topics:integer', 'posts:integer', 'last_topic_id:integer',
                    'forums_topics.last_post_time', 'forums_topics.replies:integer',
                    'users.username', 'users.nickname', 'forums.locked:boolean', 'forums.published:boolean');
            $table->join('forums_topics', 'forums.last_topic_id', 'forums_topics.id', 'left');
            $table->join('users', 'forums_topics.last_post_uid', 'users.id', 'left');

        } else {
            $table->select('id:integer', 'title', 'description', 'fast_url', 'topics:integer',
                           'posts:integer', 'locked:boolean', 'published:boolean', 'gid:integer');
        }

        if (!empty($gid)) {
            $table->and()->where('gid', $gid);
        }

        if ($onlyPublished) {
            $table->and()->where('forums.published', true);
        }
        $result = $table->orderBy('forums.order asc')->fetchAll();
        return $result;
    }

    /**
     * Update last_topic_id and count of topics/posts
     *
     * @access  public
     * @param   int     $fid    Forum ID
     * @return  mixed   Returns True if successful or Jaws_Error on failure
     */
    function UpdateForumStatistics($fid)
    {
        $table = Jaws_ORM::getInstance()->table('forums');
        $ftmTable = Jaws_ORM::getInstance()->table('forums_topics')->select('max(id)')->where('fid', $fid);
        $ftcTable = Jaws_ORM::getInstance()->table('forums_topics')->select('count(id)')->where('fid', $fid);
        $ftsTable = Jaws_ORM::getInstance()->table('forums_topics')->select('sum(id)')->where('fid', $fid);
        $result = $table->update(array(
                'last_topic_id' => $ftmTable,
                'topics' => $ftcTable,
                'posts' => $ftsTable,
            ))->where('id', $fid)->exec();

        return $result;
    }

}