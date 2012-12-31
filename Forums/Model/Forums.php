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
        $params = array();
        $params['fid'] = $fid;

        $sql = '
            SELECT
                [id], [gid], [title], [description], [fast_url], [topics], [posts],
                [order], [locked], [published]
            FROM [[forums]]
            WHERE [id] = {fid}';

        $types = array(
            'integer', 'integer', 'text', 'text', 'text', 'integer', 'integer',
            'integer', 'boolean', 'boolean'
        );

        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        return $result;
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
    function GetForums($gid = false, $onlyPublished = false, $last_topic_detail = false)
    {
        $params = array();
        $params['gid']  = $gid;
        $params['true'] = true;
        $params['published'] = true;

        if ($last_topic_detail) {
            $sql = '
                SELECT
                    [[forums]].[id], [[forums]].[title], [[forums]].[description],
                    [[forums]].[fast_url], [topics], [posts], [last_topic_id],
                    [[forums_topics]].[last_post_time], [[forums_topics]].[replies],
                    [[users]].[username], [[users]].[nickname], [[forums]].[locked], [[forums]].[published]
                FROM [[forums]]
                LEFT JOIN [[forums_topics]] ON [[forums]].[last_topic_id] = [[forums_topics]].[id]
                LEFT JOIN [[users]] ON [[forums_topics]].[last_post_uid] = [[users]].[id]
                WHERE {true} = {true}';

            $types = array(
                'integer', 'text', 'text',
                'text', 'integer', 'integer', 'integer',
                'timestamp', 'integer',
                'text', 'text', 'boolean', 'boolean'
            );
        } else {
            $sql = '
                SELECT
                    [id], [title], [description], [fast_url], [topics], [posts],
                    [locked], [published]
                FROM [[forums]]
                WHERE {true} = {true}';

            $types = array(
                'integer', 'text', 'text', 'text', 'integer', 'integer',
                'boolean', 'boolean'
            );
        }

        if (!empty($gid)) {
            $sql .= ' AND [gid] = {gid}';
        }

        if ($onlyPublished) {
            $sql .= ' AND [[forums]].[published] = {published}';
        }
        $sql.= ' ORDER BY [[forums]].[order] ASC';

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
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
        $params = array();
        $params['fid'] = (int)$fid;

        $sql = "
            UPDATE [[forums]] SET
                [last_topic_id] = (
                    SELECT MAX([[forums_topics]].[id])
                    FROM [[forums_topics]]
                    WHERE [[forums_topics]].[fid] = {fid}
                ),
                [topics] = (
                    SELECT COUNT([[forums_topics]].[id])
                    FROM [[forums_topics]]
                    WHERE [[forums_topics]].[fid] = {fid}
                ),
                [posts] = (
                    SELECT SUM([replies])
                    FROM [[forums_topics]]
                    WHERE [[forums_topics]].[fid] = {fid}
                )
            WHERE
                [id] = {fid}";

        $result = $GLOBALS['db']->query($sql, $params);
        return $result;
    }

}