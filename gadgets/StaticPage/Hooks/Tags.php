<?php
/**
 * StaticPage - Tags gadget hook
 *
 * @category    GadgetHook
 * @package     StaticPage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Hooks_Tags extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a tag content
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   array   $references Array of References
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($action, $references)
    {
        if(empty($action) || !is_array($references) ||empty($references)) {
            return false;
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $sptTable->select('page_id:integer', 'group_id', 'title', 'content', 'language', 'fast_url', 'static_pages_translation.updated');
        $sptTable->join('static_pages', 'static_pages.page_id', 'static_pages_translation.base_id');
        $result = $sptTable->where('translation_id', $references, 'in')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $pages = array();
        foreach ($result as $p) {
            if (!$this->gadget->GetPermission('AccessGroup', $p['group_id'])) {
                continue;
            }
            $page = array();
            $page['title'] = $p['title'];
            $url = $this->gadget->urlMap(
                'Page',
                array('pid' => empty($p['fast_url'])?
                      $p['page_id'] : $p['fast_url'],
                     'language'  => $p['language']));
            $page['url']     = $url;
            $page['image']   = 'gadgets/StaticPage/Resources/images/logo.png';
            $page['snippet'] = $p['content'];
            $page['date']    = $date->ToISO($p['updated']);

            $pages[$p['page_id']] = $page;
        }

        return $pages;
    }

}