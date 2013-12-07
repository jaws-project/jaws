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
class StaticPage_Model_Admin_Page extends StaticPage_Model_Page
{

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
     * @param   string  $tags       Tags
     * @param   bool    $published  Whether the page is published or not
     * @param   bool    $auto       Whether its an auto saved page or not
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function AddPage($title, $group, $show_title, $content, $language, $fast_url,
                     $meta_keys, $meta_desc, $tags, $published, $auto = false)
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
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'));
        }

        $base_id = $GLOBALS['db']->lastInsertID('static_pages', 'page_id');
        $tModel = $this->gadget->model->loadAdmin('Translation');
        $tid = $tModel->AddTranslation($base_id, $title, $content, $language, $meta_keys, $meta_desc, $tags, $published);
        if (Jaws_Error::IsError($tid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_ADDED'));
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
     * @param   string  $tags       Tags
     * @param   bool    $published  Whether the page is published or not
     * @param   bool    $auto       Whether its an auto saved page or not
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdatePage($id, $group, $show_title, $title, $content, $language,
                        $fast_url, $meta_keys, $meta_desc, $tags, $published, $auto = false)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages', false);

        $page = $this->GetPage($id);
        if (Jaws_Error::isError($page)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_FOUND'));
        }

        $params['group_id']      = (int)$group;
        $params['base_language'] = $language;
        $params['fast_url']      = $fast_url;
        $params['show_title']    = (bool)$show_title;
        $params['updated']       = $GLOBALS['db']->Date();

        $spTable = Jaws_ORM::getInstance()->table('static_pages');
        $result = $spTable->update($params)->where('page_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'));
        }

        $tModel = $this->gadget->model->loadAdmin('Translation');
        $result = $tModel->UpdateTranslation($page['translation_id'], $title, $content, $language,
                                             $meta_keys, $meta_desc, $tags, $published);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_UPDATED'));
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
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'));
            }
        }

        // Delete Page Translation Tags
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $tIds = $sptTable->select('translation_id:integer')->where('base_id', $id)->fetchColumn();
        $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
        $res = $model->DeleteItemTags('StaticPage', 'page', $tIds);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TAG_NOT_DELETED'), RESPONSE_ERROR);
            return $res;
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->delete()->where('base_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
        }

        $spTable = Jaws_ORM::getInstance()->table('static_pages');
        $result = $spTable->delete()->where('page_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'));
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
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'));
        }

        foreach ($pages as $page) {
            $res = $this->DeletePage($page);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED'));
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_PAGE_MASSIVE_DELETED'), RESPONSE_NOTICE);
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
            'base_id desc',
            'title',
            'title desc',
            'updated',
            'updated desc',
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
                $v = '%'.trim($v).'%';
                $spgTable->and()->openWhere();
                $spgTable->where('spt.title', $v, 'like')->or()->where('spt.content', $v, 'like');
                $spgTable->closeWhere();
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
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'));
        }

        //limit, sort, sortDirection, offset..
        return $result;
    }
}