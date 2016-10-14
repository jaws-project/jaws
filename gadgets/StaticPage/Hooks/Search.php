<?php
/**
 * StaticPage - Search gadget hook
 *
 * @category   GadgetHook
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
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
            'static_pages_translation' => array('static_pages_translation.content', 'static_pages_translation.title'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $objORM->table('static_pages');
        $objORM->select('static_pages.page_id', 'static_pages.group_id', 'static_pages.fast_url',
            'static_pages_groups.fast_url as spg_fast_url', 'static_pages_translation.title',
            'static_pages_translation.content', 'static_pages_translation.language', 'static_pages_translation.updated');
        $objORM->join('static_pages_translation', 'static_pages.page_id', 'static_pages_translation.base_id', 'left');
        $objORM->join('static_pages_groups', 'static_pages.group_id', 'static_pages_groups.id', 'left');
        $objORM->where('static_pages_groups.visible', true);
        $objORM->and()->where('static_pages_translation.published', true);
        $objORM->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('id')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date  = Jaws_Date::getInstance();
        $pages = array();
        foreach ($result as $p) {
            if (!$this->gadget->GetPermission('AccessGroup', $p['group_id'])) {
                continue;
            }
            $page = array();
            $page['title'] = $p['title'];
            $url = $this->gadget->urlMap(
                'Pages',
                array(
                    'gid' => empty($p['spg_fast_url'])? $p['group_id'] : $p['spg_fast_url'],
                    'pid' => empty($p['fast_url'])? $p['page_id'] : $p['fast_url'],
                    'language'  => $p['language']
                )
            );
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