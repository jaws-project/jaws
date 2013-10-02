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

        return 'LinkDump' . $tag;

        $sql = '
            SELECT
                [id], [title], [url], [description], [updatetime]
            FROM [[linkdump_links]]
            ';

        $sql .= ' WHERE ' . $pSql;
        $sql .= ' ORDER BY [createtime] desc';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $links = array();
        foreach ($result as $r) {
            $link = array();
            $link['title']   = $r['title'];
            $link['url']     = $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Link', array('id' => $r['id']));
            $link['outer']   = true;
            $link['image']   = 'gadgets/LinkDump/images/logo.png';
            $link['snippet'] = $r['description'];
            $link['date']    = $date->ToISO($r['updatetime']);
            $links[] = $link;
        }

        return $links;
    }

}