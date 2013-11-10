<?php
/**
 * StaticPage - Search gadget hook
 *
 * @category   GadgetHook
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets search fields of the gadget
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
                    array('spt.[content]', 'spt.[title]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql   Prepared search(WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
        $params = array();
        $params['visible']   = true;
        $params['published'] = true;

        $sql = '
            SELECT
               sp.[page_id], sp.[group_id], sp.[fast_url], spg.[fast_url] as spg_fast_url,
               spt.[title], spt.[content], spt.[language], spt.[updated]
            FROM [[static_pages]] sp
            LEFT JOIN [[static_pages_translation]] spt ON sp.[page_id] = spt.[base_id]
            LEFT JOIN [[static_pages_groups]] spg ON sp.[group_id] = spg.[id]
            WHERE
                spg.[visible]   = {visible}
              AND
                spt.[published] = {published}
            ';

        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY sp.[page_id] desc';

        $types = array('integer', 'integer', 'text', 'text', 'text', 'text', 'text', 'timestamp');
        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date  = Jaws_Date::getInstance();
        $pages = array();
        foreach ($result as $p) {
            $page = array();
            $page['title'] = $p['title'];
            $url = $GLOBALS['app']->Map->GetURLFor(
                                            'StaticPage',
                                            'Pages',
                                            array('gid' => empty($p['spg_fast_url'])?
                                                                 $p['group_id'] : $p['spg_fast_url'],
                                                  'pid' => empty($p['fast_url'])?
                                                                 $p['page_id'] : $p['fast_url'],
                                                  'language'  => $p['language']));
            $page['url']     = $url;
            $page['image']   = 'gadgets/StaticPage/Resources/images/logo.png';
            $page['snippet'] = $p['content'];
            $page['date']    = $date->ToISO($p['updated']);

            $stamp           = str_replace(array('-', ':', ' '), '', $p['updated']);
            $pages[$stamp]   = $page;
        }

        return $pages;
    }

}