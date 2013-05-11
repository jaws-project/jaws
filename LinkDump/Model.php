<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LinkDump_Model extends Jaws_Gadget_Model
{
    /**
     * Get information about a link
     *
     * @access  public
     * @param   int     $id     The links id
     * @return  mixed   An array contains link information and Jaws_Error on error
     */
    function GetLink($id)
    {
        $objORM = Jaws_ORM::getInstance()->table('linkdump_links');
        $objORM->select(
            'id:integer','gid:integer', 'title', 'description', 'url', 'fast_url', 'createtime', 'updatetime',
            'clicks:integer', 'rank:integer'
        );

        $objORM->where(is_numeric($id)? 'id' : 'fast_url', $id);
        $link = $objORM->getRow();
        if (Jaws_Error::IsError($link) || !array_key_exists('id', $link)) {
            return new Jaws_Error(Jaws_Error::IsError($link)? $link->getMessage() : _t('LINKDUMP_LINKS_NOT_EXISTS'),
                                  'LINKDUMP_NAME');
        }

        $objORM->select('tag')->table('linkdump_links_tags');
        $objORM->join('linkdump_tags', 'linkdump_tags.id', 'linkdump_links_tags.tag_id');
        $tags = $objORM->where('link_id', $link['id'])->getCol();
        if (Jaws_Error::IsError($tags)) {
            return $tags;
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
        $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_tags');
        $res = $ltagsTable->select('id:integer')->where('tag', $tag)->getRow();
        if (!Jaws_Error::IsError($res) && !empty($res)) {
            $tag_id = $res['id'];

            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $linksTable->select(
                'id:integer', 'title', 'description', 'url', 'fast_url',
                'createtime', 'updatetime', 'clicks:integer'
            );
            $linksTable->join('linkdump_links_tags', 'linkdump_links_tags.link_id', 'linkdump_links.id');
            $res = $linksTable->where('tag_id', $tag_id)->orderBy('id ASC')->getAll();
        }

        return $res;
    }

    /**
     * Increase the link's clicks by one
     *
     * @access  public
     * @param   int     $id     Link's id
     * @return  mixed   True on Success and Jaws_Error otherwise
     */
    function Click($id)
    {
        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        return $linksTable->update(array('clicks' => $linksTable->expr('clicks + ?', 1)))->where('id', $id)->exec();
    }

    /**
     * Generates a TagCloud
     *
     * @access  public
     * @return  mixed   TagCloud data or Jaws_Error on error
     */
    function CreateTagCloud()
    {
        $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_links_tags');
        $ltagsTable->select('tag_id:integer', 'tag', 'count(tag_id) as howmany:integer');
        $ltagsTable->join('linkdump_tags', 'linkdump_tags.id', 'linkdump_links_tags.tag_id');
        return $ltagsTable->groupBy('tag_id', 'tag')->orderBy('tag')->getAll();
    }

    /**
     * Returns a group information
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   Array of group information and Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $lgroupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $lgroupsTable->select(
            'id:integer', 'title', 'fast_url', 'limit_count:integer', 'link_type:integer', 'order_type:integer');
        $lgroupsTable->where(is_numeric($gid)? 'id' : 'fast_url', $gid);
        return $lgroupsTable->getRow();
    }

    /**
     * Returns a list with all the menus
     *
     * @access  public
     * @return  mixed  Array with all the available menus and Jaws_Error on error
     */
    function GetGroups()
    {
        $lgroupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $lgroupsTable->select('id:integer', 'title', 'fast_url', 'limit_count:integer', 'link_type:integer');
        return $lgroupsTable->orderBy('id ASC')->getAll();
    }

    /**
     * Retrive all links
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   bool    $limit      Limit of data to retrieve (false by default, returns all)
     * @param   string  $orderBy    order by
     * @return  mixed   An array contains all links and info. and Jaws_Error on error
     */
    function GetGroupLinks($gid = null, $limit = false, $orderBy = 'rank')
    {
        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        $linksTable->select(
            'id:integer','gid:integer', 'title', 'description', 'url', 'fast_url', 'createtime', 'updatetime',
            'clicks:integer', 'rank:integer'
        );

        if (empty($gid)) {
            $linksTable->orderBy('gid', 'rank', 'id ASC');
        } else {
            $linksTable->where('gid', $gid);
            switch ($orderBy) {
                case 1:
                    $linksTable->orderBy('id ASC');
                    break;
                case 2:
                    $linksTable->orderBy('title ASC');
                    break;
                case 3:
                    $linksTable->orderBy('clicks DESC');
                    break;
                default:
                    $linksTable->orderBy('rank', 'id ASC');
            }
        }

        return $linksTable->limit($limit)->getAll();
    }

}