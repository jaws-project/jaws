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
class StaticPage_Model extends Jaws_Gadget_Model
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
            'sp.fast_url', 'spt.published:boolean', 'sp.show_title', 'spt.content', 'spt.user:integer',
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

        return  $spTable->fetchRow();
    }

    /**
     * Gets the translation(by translation ID) of a page
     *
     * @access  public
     * @param   int     $id  Translation ID
     * @return  mixed   Array translation information or Jaws_Error on failure
     */
    function GetPageTranslation($id)
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $sptTable->select(
            'translation_id:integer', 'base_id:integer', 'title', 'content', 'language',
            'meta_keywords', 'meta_description', 'user:integer', 'published:boolean', 'updated'
        )->where('translation_id', $id);

        $row = $sptTable->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            return $row;
        }

        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
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
     * Gets the translation by page ID and language code
     *
     * @access  public
     * @param   int     $page_id    ID of the page we are translating
     * @param   string  $language   The language we are using
     * @return  mixed   Array of translation information or Jaws_Error on failure
     */
    function GetPageTranslationByPage($page_id, $language) 
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $row = $sptTable->select(
            'translation_id:integer', 'base_id:integer', 'title', 'content', 'language',
            'user:integer', 'published:boolean', 'updated'
        )->where('base_id', $page_id)->and()->where('language', $language)->fetchRow();

        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            return $row;
        }
        
        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
    }

    /**
     * Returns all available languages a page has been translated to
     *
     * @access  public
     * @param   int     $page           Page ID
     * @param   bool    $onlyPublished  Publish status of the page
     * @return  mixed   Array of language codes / False if no code are found
     */
    function GetTranslationsOfPage($page, $onlyPublished = false)
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $sptTable->select('translation_id:integer', 'language')->where('base_id', $page);

        if ($onlyPublished) {
            $sptTable->and()->where('published', true);
        }

        $result = $sptTable->fetchAll();
        if (Jaws_Error::isError($result)) {
            return false;
        }

        return (count($result) > 0) ? $result : false;
    }

    /**
     * Gets pages with given conditions
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $limit      The number of pages to return (null = all pages)
     * @param   int     $orderBy    The coulmn which the result must be sorted by
     * @param   int     $offset     Starting offset
     * @return  array   List of pages
     */
    function GetPages($gid = null, $limit = null, $orderBy = 1, $offset = false)
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
        $spTable->orderBy('spt.'.$orderBy);
        $result = $spTable->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'), _t('STATICPAGE_NAME'));
        }

        return $result;
    }
    
    /**
     * Checks for existance of a page translation
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $language   The translation we are looking for
     * @return  bool    True if exists and false if not
     */
    function TranslationExists($page_id, $language)
    {
        $spTable = Jaws_ORM::getInstance()->table('static_pages_translation as spt');
        $spTable->select('count(translation_id) as total');
        $spTable->join('static_pages as sp',  'sp.page_id',  'spt.base_id');

        if (is_numeric($page_id)) {
            $spTable->where('sp.page_id', $page_id);
        } else {
            $spTable->where('sp.fast_url', $page_id);
        }
        $total = $spTable->and()->where('spt.language', $language)->fetchOne();
        return ($total == '0') ? false : true;
    }

    /**
     * Gets properties of a group
     *
     * @access  public
     * @param   int     $id  Group ID
     * @return  mixed   Array of group info or Jaws_Error
     */
    function GetGroup($id)
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $spgTable->select('id:integer', 'title', 'fast_url', 'meta_keywords', 'meta_description', 'visible:boolean');

        if (is_numeric($id)) {
            $spgTable->where('id', $id);
        } else {
            $spgTable->where('fast_url', $id);
        }

        $group = $spgTable->fetchRow();
        if (Jaws_Error::IsError($group)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $group;
    }

    /**
     * Returns list of groups
     *
     * @access  public
     * @param   bool    $visible    Visibility status of groups
     * @param   bool    $limit      Number of groups to retrieve
     * @param   bool    $offset     Start offset of result boundaries 
     * @return  mixed   Array of groups or Jaws_Error
     */
    function GetGroups($visible = null, $limit = null, $offset = null)
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $spgTable->select('id:integer', 'title', 'fast_url', 'visible:boolean')->limit($limit, $offset);

        if ($visible != null) {
            $spgTable->where('visible', (bool)$visible);
        }
        $groups = $spgTable->fetchAll();
        if (Jaws_Error::IsError($groups)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $groups;
    }

}