<?php
/**
 * LinkDump - Search gadget hook
 *
 * @category   GadgetHook
 * @package    LinkDump
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   search fields array
     */
    function GetOptions() {
        return array(
                    array('[title]', 'url', '[description]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql  Prepared search (WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
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

        $date = Jaws_Date::getInstance();
        $links = array();
        foreach ($result as $r) {
            $link = array();
            $link['title']   = $r['title'];
            $link['url']     = $this->gadget->urlMap('Link', array('id' => $r['id']));
            $link['outer']   = true;
            $link['image']   = 'gadgets/LinkDump/Resources/images/logo.png';
            $link['snippet'] = $r['description'];
            $link['date']    = $date->ToISO($r['updatetime']);
            $links[] = $link;
        }

        return $links;
    }

}