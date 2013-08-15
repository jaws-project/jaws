<?php
require_once JAWS_PATH . 'gadgets/StaticPage/Model.php';
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
class StaticPage_AdminModel extends StaticPage_Model
{
    /**
     * Creates a translation of the given page
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Publish status of the page
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function AddTranslation($page_id, $title, $content, $language, $meta_keys, $meta_desc, $published)
    {
        // Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        if (!file_exists(JAWS_PATH . "languages/$language/FullName")) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS', $language), _t('STATICPAGE_NAME'));
        }

        if ($this->TranslationExists($page_id, $language)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS', $language), _t('STATICPAGE_NAME'));
        }
        $published = $this->gadget->GetPermission('PublishPages')? $published : false;

        $params['base_id'] = $page_id;
        $params['title'] = $title;
        $params['content'] = str_replace("\r\n", "\n", $content);
        $params['language'] = $language;
        $params['user'] = $GLOBALS['app']->Session->GetAttribute('user');
        $params['meta_keywords'] = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['published'] = (bool)$published;
        $params['updated'] = $GLOBALS['db']->Date();

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_ADDED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_CREATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates a translation
     *
     * @access  public
     * @param   int     $id         Translation ID
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Publish status of the page
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateTranslation($id, $title, $content, $language, $meta_keys, $meta_desc, $published)
    {
        //Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        if (!file_exists(JAWS_PATH . "languages/$language/FullName")) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        //Original language?
        $translation = $this->GetPageTranslation($id);
        if (Jaws_Error::isError($translation)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if ($translation['language'] != $language) {
            if ($this->TranslationExists($translation['base_id'], $language)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS'), _t('STATICPAGE_NAME'));
            }

        }

        // check modify other's pages ACL
        if (!$this->gadget->GetPermission('ModifyOthersPages') &&
            ($GLOBALS['app']->Session->GetAttribute('user') != $translation['user']))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        // check modify published pages ACL
        if ($translation['published'] &&
            !$this->gadget->GetPermission('ManagePublishedPages'))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        // Lets update it
        $params['title']            = $title;
        $params['content']          = str_replace("\r\n", "\n", $content);
        $params['language']         = $language;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['updated']          = $GLOBALS['db']->Date();
        if ($this->gadget->GetPermission('PublishPages')) {
            $params['published'] = (bool)$published;
        } else {
            $params['published'] = false;
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->update($params)->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_UPDATED'), RESPONSE_NOTICE);
        return true;       
    }

    /**
     * Deletes the translation
     *
     * @access  public
     * @param   int     $id Translation ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeleteTranslation($id)
    {
        $params = array();
        $params['id'] = $id;

        if (!$this->gadget->GetPermission('ModifyOthersPages')) {
            $translation = $this->GetPageTranslation($id);
            if (Jaws_Error::isError($translation)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
            }

            if ($GLOBALS['app']->Session->GetAttribute('user') != $translation['user']) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), _t('STATICPAGE_NAME'));
            }
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->delete()->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_DELETED'), RESPONSE_NOTICE);
        return true;       
    }
    

    /**
     * Creates a new page
     *
     * @access  public
     * @param   string  $title      The title of the page
     * @param   int     $group      The group of the page
     * @param   bool    $show_title Whether displays the title or not
     * @param   string  $content    The content of the page
     * @param   string  $language   The language of the page
     * @param   string  $fast_url   The fast URL of the page
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Whether the page is published or not
     * @param   bool    $auto       Whether its an auto saved page or not
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function AddPage($title, $group, $show_title, $content, $language, 
                     $fast_url, $meta_keys, $meta_desc, $published, $auto = false)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages', $auto === false);

        $params['group_id']         = (int)$group;
        $params['base_language']    = $language;
        $params['fast_url']         = $fast_url;
        $params['show_title']       = (bool)$show_title;
        $params['updated']          = $GLOBALS['db']->Date();
        $spTable = Jaws_ORM::getInstance()->table('static_pages');
        $result = $spTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'), _t('STATICPAGE_NAME'));
        }

        $base_id = $GLOBALS['db']->lastInsertID('static_pages', 'page_id');
        $result = $this->AddTranslation($base_id, $title, $content, $language, $meta_keys, $meta_desc, $published);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_PAGE_CREATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the page
     *
     * @access  public
     * @param   int     $id         The ID of the page to update
     * @param   int     $group      The group of the page
     * @param   bool    $show_title Whether displays the title or not
     * @param   string  $title      The title of the page
     * @param   string  $content    The contents of the page
     * @param   string  $language   The language of the page
     * @param   string  $fast_url   The fast URL of the page
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Whether the page is published or not
     * @param   bool    $auto       Whether its an auto saved page or not
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdatePage($id, $group, $show_title, $title, $content, $language, 
                        $fast_url, $meta_keys, $meta_desc, $published, $auto = false)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages', false);

        $page = $this->GetPage($id);
        if (Jaws_Error::isError($page)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'), _t('STATICPAGE_NAME'));
        }

        $params['group_id']      = (int)$group;
        $params['base_language']   = $language;
        $params['fast_url']   = $fast_url;
        $params['show_title'] = (bool)$show_title;
        $params['updated']        = $GLOBALS['db']->Date();

        $spTable = Jaws_ORM::getInstance()->table('static_pages');
        $result = $spTable->update($params)->where('page_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        $result = $this->UpdateTranslation($page['translation_id'], $title, $content, $language, $meta_keys, $meta_desc, $published);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_PAGE_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the page and all of its translations
     *
     * @access  public
     * @param   int     $id  Page ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeletePage($id)
    {
        if (!$this->gadget->GetPermission('ModifyOthersPages')) {
            $user = $GLOBALS['app']->Session->GetAttribute('user');
            $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
            $sptTable->select('count(base_id)')->where('base_id', $id)->and()->where('user', $user, '<>');
            $result = $sptTable->fetchOne();
            if (Jaws_Error::IsError($result) || ($result > 0)) {
                // FIXME: need new language statement
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), _t('STATICPAGE_NAME'));
            }
        }


        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->delete()->where('base_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        $spTable = Jaws_ORM::getInstance()->table('static_pages');
        $result = $spTable->delete()->where('page_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_PAGE_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a group of pages
     *
     * @access  public
     * @param   array   $pages  Array with the page IDs
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function MassiveDelete($pages)
    {
        if (!is_array($pages)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'), _t('STATICPAGE_NAME'));
        }

        foreach ($pages as $page) {
            $res = $this->DeletePage($page);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'), _t('STATICPAGE_NAME'));
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_PAGE_MASSIVE_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Creates a new group
     *
     * @access  public
     * @param   string  $title      Title of the group
     * @param   string  $fast_url   The fast URL of the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $visible    Visibility status of the group
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function InsertGroup($title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages_groups', true);

        $params['title']            = $title;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['visible']          = (bool)$visible;

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->insert($params)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return true;
    }

    /**
     * Updates the group
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   string  $title      Title of the group
     * @param   string  $fast_url   The fast URL of the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $visible    Visibility status of the group
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateGroup($gid, $title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages_groups', false);

        $params['title']            = $title;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['visible']          = (bool)$visible;

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->update($params)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return true;
    }

    /**
     * Gets total number of groups
     *
     * @access  public
     * @return  mixed   Number of groups or Jaws_Error
     */
    function GetGroupsCount()
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $count = $spgTable->select('count(id)')->fetchOne();
        if (Jaws_Error::IsError($count)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $count;
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $gid   Group ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_GROUP_NOT_DELETABLE'), _t('STATICPAGE_NAME'));
        }

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return true;
    }

    /**
     * Updates gadget settings
     *
     * @access  public
     * @param   string  $defaultPage  Default page to be displayed
     * @param   string  $multiLang    Whether uses a multilanguage 'schema'?
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateSettings($defaultPage, $multiLang)
    {
        $res = array();
        $res[0] = $this->gadget->registry->update('default_page', $defaultPage);
        $res[1] = $this->gadget->registry->update('multilanguage', $multiLang);
        
        foreach($res as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_SETTINGS_NOT_SAVED'), _t('STATICPAGE_NAME'));
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Search for pages (and translations) that matches the given criteria
     *
     * @access  public
     * @access  public
     * @param   int     $group      Group ID
     * @param   mixed   $status     Status of the pages we are looking for (1/0 or Y/N)
     * @param   string  $search     The Keywords we are looking for in title/description of the pages
     * @param   int     $orderBy    Order by
     * @param   int     $offset     Data limit
     * @return  array   List of pages
     */
    function SearchPages($group, $status, $search, $orderBy, $offset = null)
    {
        $orders = array(
            'base_id',
            'base_id DESC',
            'title',
            'title DESC',
            'updated',
            'updated DESC',
        );
        $orderBy = (int)$orderBy;
        $orderBy = $orders[($orderBy > 5)? 1 : $orderBy];

        $params = array();
        $params['group'] = (int)$group;

        if (!is_bool($status)) {
            if (is_numeric($status)) {
                $params['status'] = $status == 1 ? true : false;
            } elseif (is_string($status)) {
                $params['status'] = $status == 'Y' ? true : false;
            }
        } else {
            $params['status'] = $status;
        }

        $spgTable = Jaws_ORM::getInstance()->table('static_pages as sp');
        $spgTable->select(
            'spt.base_id:integer', 'sp.page_id:integer', 'sp.group_id:integer', 'spg.title as gtitle', 'spt.title',
            'sp.fast_url', 'sp.base_language', 'spt.published:boolean', 'spt.updated'
        );
        $spgTable->join('static_pages_groups as spg',  'sp.group_id',  'spg.id', 'left');
        $spgTable->join('static_pages_translation as spt',  'sp.page_id',  'spt.base_id', 'left');
        $spgTable->where('sp.base_language', array('spt.language', 'expr'));

        if (trim($search) != '') {
            $searchdata = explode(' ', $search);

            /**
             * This query needs more work, not use $v straight, should be
             * like rest of the param stuff.
             */
            foreach ($searchdata as $v) {
                $v = trim($v);
                $likeStr = '%'.$v.'%';

                $spgTable->and()->where(
                    $spgTable->where('spt.title', $likeStr, 'like')->or()->where('spt.content', $likeStr, 'like')
                );
            }
        }

        if (trim($status) != '') {
            $spgTable->and()->where('spt.published', $status);
        }

        if (!empty($group)) {
            $spgTable->and()->where('sp.group_id', (int)$group);
        }

        if (is_numeric($offset)) {
            $limit = 10;
            $spgTable->limit($limit, $offset);
        }
        $result = $spgTable->orderBy('spt.' . $orderBy)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'), _t('STATICPAGE_NAME'));
        }

        //limit, sort, sortDirection, offset..
        return $result;
    }

}