<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Model_Links extends Jaws_Gadget_Model
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
        $link = $objORM->fetchRow();
        if (Jaws_Error::IsError($link)) {
            return $link;
        }

        if (!empty($link)) {
            $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
            $tags = $model->GetItemTags(array('gadget'=>'LinkDump', 'action'=>'link', 'reference'=>$id), true);
            $link['tags'] = array_filter($tags);
        }

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
        $res = $ltagsTable->select('id:integer')->where('tag', $tag)->fetchRow();
        if (!Jaws_Error::IsError($res) && !empty($res)) {
            $tag_id = $res['id'];

            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $linksTable->select(
                'id:integer', 'title', 'description', 'url', 'fast_url',
                'createtime', 'updatetime', 'clicks:integer'
            );
            $linksTable->join('linkdump_links_tags', 'linkdump_links_tags.link_id', 'linkdump_links.id');
            $res = $linksTable->where('tag_id', $tag_id)->orderBy('id asc')->fetchAll();
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
        return $ltagsTable->groupBy('tag_id', 'tag')->orderBy('tag')->fetchAll();
    }
}