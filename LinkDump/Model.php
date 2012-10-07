<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LinkDumpModel extends Jaws_Model
{
    /**
     * Get information about a link
     *
     * @access  public
     * @param   int     $id The links id
     * @return  array   An array contains link information and Jaws_Error on error
     */
    function GetLink($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [id], [gid], [title], [description], [url], [fast_url], [createtime], [updatetime], [clicks], [rank]
            FROM [[linkdump_links]]';
        if (is_numeric($id)) {
            $sql .= '
                WHERE [id] = {id}';
        } else {
            $sql .= '
                WHERE [fast_url] = {id}';
        }

        $link = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($link) || !array_key_exists('id', $link)) {
            return new Jaws_Error(Jaws_Error::IsError($link)? $link->getMessage() : _t('LINKDUMP_LINKS_NOT_EXISTS'),
                                  'LINKDUMP_NAME');
        }

        $sql = '
            SELECT 
                [tag] 
            FROM [[linkdump_links_tags]]
            INNER JOIN [[linkdump_tags]] ON [tag_id] = [id]
            WHERE [link_id] = {id}';
        $params['id'] = $link['id'];
        $tags = $GLOBALS['db']->queryCol($sql, $params);
        if (Jaws_Error::IsError($tags)) {
            return new Jaws_Error($tags->getMessage(), 'SQL');
        }

        $link['tags'] = array_filter($tags);
        return $link;
    }

    /**
     * Retrieve All links tagged by a specific keyword
     *
     * @access  public
     * @param   string  $tag    The keyword (tag)
     * @return  array   An array contains links info
     */
    function GetTagLinks($tag)
    {
        $sql = 'SELECT [id] FROM [[linkdump_tags]] WHERE [tag] = {tag}';
        $res = $GLOBALS['db']->queryRow($sql, array('tag' => $tag));
        if (!Jaws_Error::IsError($res) && !empty($res)) {
            $tag_id = $res['id'];
            $sql = '
                SELECT
                    [id], [title], [description], [url], [fast_url], [createtime], [updatetime], [clicks]
                FROM [[linkdump_links]]
                INNER JOIN [[linkdump_links_tags]] on [link_id] = [id]
                WHERE [tag_id] = {tag_id}
                ORDER BY [id] ASC';
            $res  = $GLOBALS['db']->queryAll($sql, array('tag_id' => $tag_id));
        }

        return $res;
    }

    /**
     * Inrease the link's clicks by one
     *
     * @access  public
     * @param   int $id Link's id
     * @return  boolean True on success and Jaws_Error otherwise
     */
    function Click($id)
    {
        $params       = array();
        $params['id'] = $id;

        $sql = 'UPDATE [[linkdump_links]] SET [clicks] = [clicks] + 1 WHERE [id] = {id}';
        $res  = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
     * Generates a TagCloud
     *
     * @return  TagCloud data or Jaws_Error on error
     */
    function CreateTagCloud()
    {
        $sql = 'SELECT
                    [tag_id], [tag], COUNT([tag_id]) as howmany
                FROM [[linkdump_links_tags]]
                INNER JOIN [[linkdump_tags]] ON [tag_id] = [id]
                GROUP BY [tag_id], [tag]
                ORDER BY [tag]';

        $types = array('integer', 'text', 'integer');
        $res = $GLOBALS['db']->queryAll($sql, array(), $types);
        if (Jaws_Error::isError($res)) {
            return new Jaws_Error(_t('LINKDUMP_ERROR_TAGCLOUD_CREATION_FAILED'), _t('BLOG_NAME'));
        }

        return $res;
    }

    /**
     * Returns a group information
     *
     * @access  public
     * @return  array  Array of group information and Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $sql = '
            SELECT
                [id], [title], [fast_url], [limit_count], [link_type], [order_type]
            FROM [[linkdump_groups]]';
        if (is_numeric($gid)) {
            $sql .= '
                WHERE [id] = {gid}';
        } else {
            $sql .= '
                WHERE [fast_url] = {gid}';
        }

        $params = array();
        $params['gid'] = $gid;

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('LINKDUMP_NAME'));
        }

        return $result;
    }

    /**
     * Returns a list with all the menus
     *
     * @access  public
     * @return  array  Array with all the available menus and Jaws_Error on error
     */
    function GetGroups()
    {
        $sql = '
            SELECT
                [id], [title], [fast_url], [limit_count], [link_type]
            FROM [[linkdump_groups]]
            ORDER BY [id] ASC';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('LINKDUMP_NAME'));
        }

        return $result;
    }

    /**
     * Retrive all links
     *
     * @access  public
     * @param   mixed   $limit  Limit of data to retrieve (false by default, returns all)
     * @return  array   An array contains all links and info. and Jaws_Error on error
     */
    function GetGroupLinks($gid = null, $limit = false, $orderBy = 'rank')
    {
        $params = array();
        $params['gid'] = $gid;

        $sql = '
            SELECT
                [id], [gid], [title], [description], [url], [fast_url], [createtime], [updatetime], [clicks], [rank]
            FROM [[linkdump_links]]
            ';

        if (empty($gid)) {
            $orderSQL = 'ORDER BY [gid], [rank], [id] ASC';
        } else {
            $sql .= 'WHERE [gid] = {gid} ';
            switch ($orderBy) {
                case 1:
                    $orderSQL = 'ORDER BY [id] ASC';
                    break;
                case 2:
                    $orderSQL = 'ORDER BY [title] ASC';
                    break;
                case 3:
                    $orderSQL = 'ORDER BY [clicks] DESC';
                    break;
                default:
                    $orderSQL = 'ORDER BY [rank], [id] ASC';
            }
        }
        $sql .= $orderSQL;

        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($rs->getMessage(), 'SQL');
            }
        }

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }
}