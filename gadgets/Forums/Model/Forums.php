<?php
/**
 * Forums Gadget
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012-2022 Jaws Development Group
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
        $perm = $this->gadget->GetPermission('ForumPublic', $fid);
        if(is_null($perm)) {
            return Jaws_Error::raiseError(Jaws::t('HTTP_ERROR_CONTENT_404'), 404, JAWS_ERROR_NOTICE);
        }
        if (!$perm) {
            return Jaws_Error::raiseError(Jaws::t('ERROR_ACCESS_DENIED'), 403, JAWS_ERROR_NOTICE);
        }

        $table = Jaws_ORM::getInstance()->table('forums');
        $table->select('id:integer', 'gid:integer', 'title', 'description', 'fast_url', 'topics:integer',
                       'posts:integer', 'order:integer', 'locked:boolean', 'private:boolean', 'published:boolean');
        if (is_numeric($fid)) {
            $table->where('id', $fid);
        } else {
            $table->where('fast_url', $fid);
        }

        return $table->fetchRow();
    }

    /**
     * Returns array of forum properties
     *
     * @access  public
     * @param   int     $fid    forum ID
     * @return  mixed   group Id or Jaws_Error on error
     */
    function GetForumGroup($fid)
    {
        return Jaws_ORM::getInstance()->table('forums')->select('gid:integer')->where('id', $fid)->fetchOne();
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
                    'forums_topics.last_post_time', 'forums_topics.replies:integer', 'forums.private:boolean',
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
        if (Jaws_Error::isError($result)) {
            return array();
        }

        $forums = array();
        foreach ($result as $forum) {
            if ($this->gadget->GetPermission('ForumPublic', $forum['id'])) {
                $forums[] = $forum;
            }
        }

        return $forums;
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
        $ftsTable = Jaws_ORM::getInstance()->table('forums_topics')->select('sum(replies)')->where('fid', $fid);
        $result = $table->update(array(
                'last_topic_id' => $ftmTable,
                'topics' => $ftcTable,
                'posts' => $ftsTable,
            ))->where('id', $fid)->exec();

        return $result;
    }

}