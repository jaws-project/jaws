<?php
/**
 * LinkDump - Tags gadget hook
 *
 * @category    GadgetHook
 * @package     LinkDump
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Hooks_Tags extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a tag content
     *
     * @access  public
     * @param   string  $tag  Tag name
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($tag)
    {

        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select(
            'linkdump_links.id as links_id:integer', 'linkdump_links.title',
            'linkdump_links.description', 'linkdump_links.updatetime'
        );

        $table->join('tags_items', 'tags_items.tag', 'tags.id');
        $table->join('linkdump_links', 'linkdump_links.id', 'tags_items.reference');
        $table->where('tags.name', $tag)->and()->where('tags_items.gadget', 'LinkDump');
        $table->and()->where('tags_items.action', 'link');

        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $links = array();
        foreach ($result as $r) {
            $link = array();
            $link['title']   = $r['title'];
            $link['url']     = $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Link', array('id' => $r['links_id']));
            $link['outer']   = true;
            $link['image']   = 'gadgets/LinkDump/images/logo.png';
            $link['snippet'] = $r['description'];
            $link['date']    = $date->ToISO($r['updatetime']);
            $links[] = $link;
        }

        return $links;
    }

}