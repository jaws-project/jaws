<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Page extends Jaws_Gadget_Model
{
    /**
     * Gets a single page
     *
     * @access  public
     * @param   mixed   $id         ID or fast_url of the page (int/string)
     * @param   string  $language   Page language
     * @return  mixed   Array of the page information or Jaws_Error on failure
     */
    function GetPage($id, $language = '')
    {
        $spTable = Jaws_ORM::getInstance()->table('static_pages as sp');
        $spTable->select(
            'sp.page_id:integer', 'sp.group_id:integer', 'spt.translation_id:integer', 'spt.language', 'spt.title',
            'sp.fast_url', 'spt.published:boolean', 'sp.show_title:boolean', 'spt.content', 'spt.user:integer',
            'spt.meta_keywords', 'spt.meta_description', 'spt.updated'
        );
        $spTable->join('static_pages_translation as spt',  'sp.page_id',  'spt.base_id');

        if (empty($language)) {
            $spTable->where('spt.language', array('sp.base_language', 'expr'));
        } else {
            $spTable->where('spt.language', $language);
        }

        if (is_numeric($id)) {
            $spTable->and()->where('sp.page_id', $id);
        } else {
            $spTable->and()->where('sp.fast_url', $id);
        }

        $page = $spTable->fetchRow();
        if (!empty($page)) {
            $page['tags'] = '';
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tags = $model->GetItemTags(
                    array('gadget' => 'StaticPage', 'action' => 'page', 'reference' => $page['translation_id']),
                    true);
                $page['tags'] = implode(',', array_filter($tags));
            }
        }
        return $page;
    }


    /**
     * Gets the default page
     *
     * @access  public
     * @return  mixed   Array of the page information or Jaws_Error on failure
     */
    function GetDefaultPage()
    {
        $defaultPage = $this->gadget->registry->fetch('default_page');

        $res = $this->GetPage($defaultPage);
        if (Jaws_Error::IsError($res) || !isset($res['page_id']) || $res['published'] === false) {

            $spTable = Jaws_ORM::getInstance()->table('static_pages');
            $max = $spTable->select('max(page_id)')->where('published', true)->fetchOne();
            if (Jaws_Error::IsError($max)) {
                return array();
            }

            $res = $this->GetPage($max);
            if (Jaws_Error::IsError($res)) {
                return array();
            }
        }
        return $res;
    }


    /**
     * Gets pages with given conditions
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $limit      The number of pages to return (null = all pages)
     * @param   int     $orderBy    The coulmn which the result must be sorted by
     * @param   int     $offset     Starting offset
     * @param   int     $published  Published?
     * @return  array   List of pages
     */
    function GetPages($gid = null, $limit = null, $orderBy = 1, $offset = false, $published = null)
    {
        $orders = array(
            'base_id',
            'base_id desc',
            'title',
            'title desc',
            'updated',
            'updated desc',
        );
        $orderBy = (int)$orderBy;
        $orderBy = $orders[($orderBy > 5)? 1 : $orderBy];

        $spTable = Jaws_ORM::getInstance()->table('static_pages as sp');
        $spTable->select(
            'spt.base_id:integer', 'sp.group_id:integer', 'sp.fast_url', 'sp.show_title:boolean', 'spt.title',
            'spt.content', 'spt.language', 'spt.published:boolean', 'spt.updated'
        );
        $spTable->join('static_pages_translation as spt',  'sp.page_id',  'spt.base_id');
        $spTable->where('sp.base_language', array('spt.language', 'expr'));

        if (!is_null($gid)) {
            $spTable->and()->where('sp.group_id', $gid);
        }

        if (!is_null($published)) {
            $spTable->and()->where('published', (bool)$published);
        }

        $spTable->orderBy('spt.'.$orderBy);
        $result = $spTable->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'));
        }

        return $result;
    }

}