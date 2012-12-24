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
        $params = array();
        $params['id']       = $id;
        $params['language'] = $language;

        $sql = '
            SELECT
                sp.[page_id], sp.[group_id], spt.[translation_id], spt.[language], spt.[title],
                sp.[fast_url], spt.[published], sp.[show_title], spt.[content], spt.[user],
                spt.[meta_keywords], spt.[meta_description], spt.[updated]
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
                       'boolean', 'text', 'integer', 'text', 'text', 'timestamp');
        $row = $GLOBALS['db']->queryRow($sql, $params, $types);
        return $row;
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
        $sql = '
            SELECT
                [translation_id], [base_id], [title], [content], [language], 
                [meta_keywords], [meta_description], [user], [published], [updated]
            FROM [[static_pages_translation]]
            WHERE [translation_id] = {id}';

        $types = array('integer', 'integer', 'text', 'text', 'text', 
                       'text', 'text', 'integer', 'boolean', 'timestamp');
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
     * Gets the default page
     *
     * @access  public
     * @return  mixed   Array of the page information or Jaws_Error on failure
     */
    function GetDefaultPage()
    {
        $defaultPage = $this->gadget->GetRegistry('default_page');

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
     * Gets the translation by page ID and language code
     *
     * @access  public
     * @param   int     $page_id    ID of the page we are translating
     * @param   string  $language   The language we are using
     * @return  mixed   Array of translation information or Jaws_Error on failure
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
     * Returns all available languages a page has been translated to
     *
     * @access  public
     * @param   int     $page           Page ID
     * @param   bool    $onlyPublished  Publish status of the page
     * @return  mixed   Array of language codes / False if no code are found
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
     * Gets pages with given conditions
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $limit      The number of pages to return (null = all pages)
     * @param   int     $sortColumn The coulmn which the result must be sorted by
     * @param   int     $sortDir    Either STATICPAGES_ASC or STATICPAGES_DESC to set the sort direction
     * @param   int     $offset     Starting offset
     * @return  array   List of pages
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
     * Checks for existance of a page translation
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $language   The translation we are looking for
     * @return  bool    True if exists and false if not
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
     * Gets properties of a group
     *
     * @access  public
     * @param   int     $id  Group ID
     * @return  mixed   Array of group info or Jaws_Error
     */
    function GetGroup($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = '
            SELECT [id], [title], [fast_url], [meta_keywords], [meta_description], [visible]
            FROM [[static_pages_groups]]
            WHERE ';

        if (is_numeric($id)) {
            $sql .= '[id] = {id}';
        } else {
            $sql .= '[fast_url] = {id}';
        }

        $types = array('integer', 'text', 'text', 'text', 'text', 'boolean');
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
     * @param   bool    $visible    Visibility status of groups
     * @param   bool    $limit      Number of groups to retrieve
     * @param   bool    $offset     Start offset of result boundaries 
     * @return  mixed   Array of groups or Jaws_Error
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