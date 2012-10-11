<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPageModel extends Jaws_Model
{
    /**
     * Gets a single page by ID.
     *
     * @access  public
     * @param   int     $id     The ID or fast_url of the page to get.
     * @return  array   An array containing the page information, or false if no page could be loaded.
     */
    function GetPage($id, $language = '')
    {
        $sql = '
            SELECT
                sp.[page_id], sp.[group_id], spt.[translation_id], spt.[language], spt.[title],
                sp.[fast_url], spt.[published], sp.[show_title], spt.[content], spt.[user],
                spt.[updated]
            FROM [[static_pages]] sp
            INNER JOIN [[static_pages_translation]] spt ON sp.[page_id] = spt.[base_id]';
        if (empty($language)) {
            $sql .= ' WHERE spt.[language] = sp.[base_language]';
        } else {
            $sql .= " WHERE spt.[language] = {language}";
        }

        if (is_numeric($id)) {
            $sql .= ' AND sp.[page_id] = {id}';
        } else {
            $sql .= ' AND sp.[fast_url] = {id}';
        }

        $types = array('integer', 'integer', 'integer', 'text', 'text', 'text', 'boolean',
                       'boolean', 'text', 'integer', 'timestamp');

        $params = array();
        $params['id']       = $id;
        $params['language'] = $language;

        $row = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['page_id'])) {
            return $row;
        }

        return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'), _t('STATICPAGE_NAME'));
    }

    /**
     * Gets the translation (by the translation ID) of a page
     *
     * @access  public
     * @param   int     $id     The translation ID
     * @return  array   An array containing the page translation information, or false if no translation could be loaded.
     */
    function GetPageTranslation($id)
    {
        $sql = '
            SELECT
                [translation_id], [base_id], [title], [content], [language], [user], [published], [updated]
            FROM [[static_pages_translation]]
            WHERE [translation_id] = {id}';

        $types = array('integer', 'integer', 'text', 'text', 'text', 'integer', 'boolean', 'timestamp');
        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id), $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            return $row;
        }

        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
    }

    /**
     * Gets the default page.
     *
     * @access  public
     * @return  array   An array containing the page information, or false if no page could be loaded.
     */
    function GetDefaultPage()
    {
        $defaultPage = $GLOBALS['app']->Registry->Get('/gadgets/StaticPage/default_page');

        $res = $this->GetPage($defaultPage);
        if (Jaws_Error::IsError($res) || !isset($res['page_id']) || $res['published'] === false) {
            $params              = array();
            $params['published'] = true;
            $sql = 'SELECT MAX([page_id]) FROM [[static_pages]] WHERE [published] = {published}';

            $max = $GLOBALS['db']->queryOne($sql, $params, array('integer'));
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
     * Gets the translation based in the page ID and the language code
     *
     * @access  public
     * @param   string  $page_id    ID of page we are translating
     * @param   string  $language   The language we are using
     * @return  array   An array containing the page translation information, or false if no translation could be loaded.
     */
    function GetPageTranslationByPage($page_id, $language) 
    {
        $params = array();
        $params['page']     = $page_id;
        $params['language'] = $language;

        $sql = '
            SELECT
                [translation_id], [base_id], [title], [content], [language], [user], [published], [updated]
            FROM [[static_pages_translation]]
            WHERE [base_id]  = {page} AND [language] = {language}';

        $types = array('integer', 'integer', 'text', 'text', 'text', 'integer', 'boolean', 'timestamp');
        $row = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            return $row;
        }
        
        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
    }

    /**
     * Returns an array with all available languages a page 
     * has been translated. If no languages are found then we return 
     * false
     *
     * @access  public
     * @param   int     $page   Page ID
     * @param   bool    $onlyPublished
     * @return  mixed   List of code languages / False if no code are found
     */
    function GetTranslationsOfPage($page, $onlyPublished = false)
    {
        $params              = array();
        $params['page']      = $page;
        $params['published'] = true;

        $sql = '
            SELECT
                [translation_id], [language]
            FROM [[static_pages_translation]]
            WHERE [base_id] = {page}';
        if ($onlyPublished) {
            $sql .= ' AND [published] = {published}';
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::isError($result)) {
            return false;
        }

        return (count($result) > 0) ? $result : false;
    }

    /**
     * Gets an index of all the pages.
     *
     * @access  public
     * @param   int     $gid        ID of the group
     * @param   int     $limit      The number of pages to return. Set to Null to return all pages.
     * @param   int     $sortType   One of the STATICPAGES_SORT_* constants to set the sort field.
     * @param   int     $sortDir    Either STATICPAGES_ASC or STATICPAGES_DESC to set the sort direction.
     * @param   int     $offset     Starting offset
     *
     * @return  array   An array containing the page information.
     */
    function GetPages($gid = null, $limit = null, $sortColumn = 'title', $sortDir = 'ASC', $offset = false)
    {
        $fields     = array('base_id', 'title', 'updated', 'published');
        $sortColumn = strtolower($sortColumn);
        if (!in_array($sortColumn, $fields)) {
            $GLOBALS['log']->Log(JAWS_LOG_WARNING, _t('STATICPAGE_ERROR_UNKNOWN_COLUMN'));
            $sortColumn = 'title';
        }

        $sortDir = strtoupper($sortDir);
        if ($sortDir == 'DESC') {
            $sortDir = 'DESC';
        } else {
            $sortDir = 'ASC';
        }

        $params = array();
        $params['gid'] = $gid;

        $sql = "
            SELECT
                spt.[base_id], sp.[group_id], sp.[fast_url], sp.[show_title], spt.[title],
                spt.[content], spt.[language], spt.[published], spt.[updated]
            FROM [[static_pages]] sp
            INNER JOIN [[static_pages_translation]] spt ON sp.[page_id] = spt.[base_id]
            WHERE sp.[base_language] = spt.[language]";

        if (!is_null($gid)) {
            $sql .= " AND sp.[group_id] = {gid}";
        }
        $sql .= " ORDER BY spt.[$sortColumn] $sortDir";

        if (!is_null($limit)) {
            if (is_numeric($offset)) {
                $result = $GLOBALS['db']->setLimit($limit, $offset);
            } else {
                $result = $GLOBALS['db']->setLimit($limit);
            }

            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error($result->getMessage(), _t('STATICPAGE_NAME'));
            }
        }

        $types = array('integer', 'integer', 'text', 'boolean', 'text', 'text', 'text',
                       'boolean', 'timestamp');

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'), _t('STATICPAGE_NAME'));
        }

        return $result;
    }
    
    /**
     * Returns true if $page has been translated to $language
     *
     * @access  public
     * @param   int     $page_id   The page ID
     * @param   string  $language  The translation we are looking for
     * @return  bool    Exists / Not exists
     */
    function TranslationExists($page_id, $language)
    {
        $sql = '
            SELECT
                COUNT([translation_id]) AS total
            FROM [[static_pages_translation]] spt
            INNER JOIN [[static_pages]] sp ON spt.[base_id] = sp.[page_id]
            ';

        if (is_numeric($page_id)) {
            $sql .= 'WHERE sp.[page_id] = {id} AND spt.[language] = {language}';
        } else {
            $sql .= 'WHERE sp.[fast_url] = {id} AND spt.[language] = {language}';
        }

        $params             = array();
        $params['id']       = $page_id;
        $params['language'] = $language;

        $total = $GLOBALS['db']->queryOne($sql, $params);
        return ($total == '0') ? false : true;        
    }

    /**
     * Fetches properties of a group
     *
     * @access  public
     * param    int     $id     Group ID
     * @returns mixed   Array of group's info or Jaws_Error
     */
    function GetGroup($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = '
            SELECT [id], [title], [fast_url], [visible]
            FROM [[static_pages_groups]]
            WHERE ';

        if (is_numeric($id)) {
            $sql .= '[id] = {id}';
        } else {
            $sql .= '[fast_url] = {id}';
        }

        $types = array('integer', 'text', 'text', 'boolean');
        $group = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($group)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $group;
    }

    /**
     * Returns list of groups
     *
     * @access  public
     * param    boolean     $visible    Checks the visibility of groups
     * param    boolean     $limit      restricts number of records
     * param    boolean     $offset     start offset of result boundaries 
     * @returns mixed       Array of groups or Jaws_Error
     */
    function GetGroups($visible = null, $limit = null, $offset = null)
    {
        $params = array();
        $params['visible'] = (bool)$visible;

        $sql = '
            SELECT [id], [title], [fast_url], [visible]
            FROM [[static_pages_groups]]';

        if ($visible != null) {
            $sql .= ' WHERE [visible] = {visible}';
        }

        if (!is_null($limit)) {
            if (is_numeric($offset)) {
                $result = $GLOBALS['db']->setLimit($limit, $offset);
            } else {
                $result = $GLOBALS['db']->setLimit($limit);
            }

            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error($result->getMessage(), _t('STATICPAGE_NAME'));
            }
        }

        $types = array('integer', 'text', 'text', 'boolean');
        $groups = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($groups)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $groups;
    }

}