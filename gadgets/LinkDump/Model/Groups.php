<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Model_Groups extends Jaws_Gadget_Model
{
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
        return $lgroupsTable->orderBy('id asc')->fetchAll();
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
        return $lgroupsTable->fetchRow();
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
            $linksTable->orderBy('gid', 'rank', 'id asc');
        } else {
            $linksTable->where('gid', $gid);
            switch ($orderBy) {
                case 1:
                    $linksTable->orderBy('id asc');
                    break;
                case 2:
                    $linksTable->orderBy('title asc');
                    break;
                case 3:
                    $linksTable->orderBy('clicks desc');
                    break;
                default:
                    $linksTable->orderBy('rank', 'id asc');
            }
        }

        return $linksTable->limit($limit)->fetchAll();
    }
}