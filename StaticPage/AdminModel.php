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
require_once JAWS_PATH . 'gadgets/StaticPage/Model.php';

class StaticPageAdminModel extends StaticPageModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $variables = array();
        $variables['timestamp'] = $GLOBALS['db']->Date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry('hide_title', 'true');
        $this->gadget->AddRegistry('default_page', '1');
        $this->gadget->AddRegistry('multilanguage', 'yes');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UninstallGadget()
    {
        $tables = array('static_pages_groups',
                        'static_pages_translation',
                        'static_pages');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('STATICPAGE_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->DelRegistry('hide_title');
        $this->gadget->DelRegistry('default_page');
        $this->gadget->DelRegistry('multilanguage');

        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.1', '<')) {
            $result = $this->installSchema('0.8.1.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.0', '<')) {
            $sql = '
                SELECT [page_id], [title], [fast_url], [published], [show_title], [content]
                FROM [[static_page]]';
            $pages = $GLOBALS['db']->queryAll($sql);
            if (Jaws_Error::IsError($pages)) {
                return $pages;
            }

            $site_language = $this->gadget->GetRegistry('site_language', 'Settings');
            foreach ($pages as $page) {
                $result = $this->AddPage($page['title'], 0, $page['fast_url'], $page['show_title'],
                                         $page['content'], $site_language, $page['published']);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            $result = $GLOBALS['db']->dropTable('static_page');
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/Properties', 'true');

            // Registry keys
            $this->gadget->AddRegistry('multilanguage', 'yes');

            $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        }

        if (version_compare($old, '0.8.3', '<')) {
            $result = $this->installSchema('0.8.3.xml', '', "0.8.1.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->InsertGroup('General', 'general', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ManageGroups',  'false');

            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('StaticPage', 'Display', 'PagesList');
            }
        }

        if (version_compare($old, '0.8.4', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.3.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/PublishPages',         'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ManagePublishedPages', 'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ModifyOthersPages',    'false');
        }

        return true;
    }

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
        $published = $GLOBALS['app']->Session->GetPermission('StaticPage', 'PublishPages')? $published : false;

        $sql = '
            INSERT INTO [[static_pages_translation]]
                ([base_id], [title], [content], [language], [user], [meta_keywords], [meta_description], [published], [updated])
            VALUES
                ({base}, {title}, {content}, {language}, {user}, {meta_keys}, {meta_desc}, {published}, {now})';

        //We already have a translation of this page?
        $params              = array();
        $params['base']      = $page_id;
        $params['title']     = $title;
        $params['content']   = str_replace("\r\n", "\n", $content);
        $params['language']  = $language;
        $params['meta_keys'] = $meta_keys;
        $params['meta_desc'] = $meta_desc;
        $params['published'] = (bool)$published;
        $params['user']      = $GLOBALS['app']->Session->GetAttribute('user');
        $params['now']       = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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
        if (!$GLOBALS['app']->Session->GetPermission('StaticPage', 'ModifyOthersPages') &&
            ($GLOBALS['app']->Session->GetAttribute('user') != $translation['user']))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        // check modify published pages ACL
        if ($translation['published'] &&
            !$GLOBALS['app']->Session->GetPermission('StaticPage', 'ManagePublishedPages'))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        if ($GLOBALS['app']->Session->GetPermission('StaticPage', 'PublishPages')) {
            $sql = '
                UPDATE [[static_pages_translation]] SET
                  [title]            = {title},
                  [content]          = {content},
                  [language]         = {language},
                  [meta_keywords]    = {meta_keys},
                  [meta_description] = {meta_desc},
                  [published]        = {published},
                  [updated]          = {now}
                WHERE [translation_id] = {id}';
        } else {
            $sql = '
                UPDATE [[static_pages_translation]] SET
                  [title]            = {title},
                  [content]          = {content},
                  [language]         = {language},
                  [meta_keywords]    = {meta_keys},
                  [meta_description] = {meta_desc},
                  [published]        = {published},
                  [updated]          = {now}
                WHERE [translation_id] = {id}';
        }

        // Lets update it
        $params              = array();
        $params['id']        = $id;
        $params['title']     = $title;
        $params['content']   = str_replace("\r\n", "\n", $content);
        $params['language']  = $language;
        $params['meta_keys'] = $meta_keys;
        $params['meta_desc'] = $meta_desc;
        $params['published'] = (bool)$published;
        $params['now']       = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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

        if (!$GLOBALS['app']->Session->GetPermission('StaticPage', 'ModifyOthersPages')) {
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

        $sql = '
            DELETE FROM [[static_pages_translation]]
            WHERE [translation_id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
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

        $sql = "
            INSERT INTO [[static_pages]]
                ([group_id], [base_language], [fast_url], [show_title], [updated])
            VALUES
                ({group}, {language}, {fast_url}, {show_title}, {now})";

        $params = array();
        $params['group']      = (int)$group;
        $params['language']   = $language;
        $params['fast_url']   = $fast_url;
        $params['show_title'] = (bool)$show_title;
        $params['now']        = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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

        $sql = '
            UPDATE [[static_pages]] SET
                [group_id]      = {group},
                [base_language] = {language},
                [fast_url]      = {fast_url},
                [show_title]    = {show_title},
                [updated]       = {now}
            WHERE [page_id] = {id}';

        $params               = array();
        $params['id']         = (int)$id;
        $params['group']      = (int)$group;
        $params['language']   = $language;
        $params['fast_url']   = $fast_url;
        $params['show_title'] = (bool)$show_title;
        $params['now']        = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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
        if (!$GLOBALS['app']->Session->GetPermission('StaticPage', 'ModifyOthersPages')) {
            $params = array();
            $params['id']   = (int)$id;
            $params['user'] = $GLOBALS['app']->Session->GetAttribute('user');

            $sql = '
                SELECT COUNT([base_id])
                FROM [[static_pages_translation]]
                WHERE [base_id] = {id} AND [user] <> {user}';

            $result = $GLOBALS['db']->queryOne($sql, $params);
            if (Jaws_Error::IsError($result) || ($result > 0)) {
                // FIXME: need new language statement
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGE_NOT_DELETED'), _t('STATICPAGE_NAME'));
            }
        }

        $sql = 'DELETE FROM [[static_pages_translation]] WHERE [base_id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        $sql = 'DELETE FROM [[static_pages]] WHERE [page_id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
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

        $params = array();
        $params['title']     = $title;
        $params['fast_url']  = $fast_url;
        $params['meta_keys'] = $meta_keys;
        $params['meta_desc'] = $meta_desc;
        $params['visible']   = (bool)$visible;

        $sql = '
          INSERT INTO [[static_pages_groups]]
              ([title], [fast_url], [meta_keywords], [meta_description], [visible])
          VALUES
              ({title}, {fast_url}, {meta_keys}, {meta_desc}, {visible})';

        $res = $GLOBALS['db']->query($sql, $params);
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

        $params = array();
        $params['gid']       = (int)$gid;
        $params['title']     = $title;
        $params['fast_url']  = $fast_url;
        $params['meta_keys'] = $meta_keys;
        $params['meta_desc'] = $meta_desc;
        $params['visible']   = (bool)$visible;

        $sql = '
            UPDATE [[static_pages_groups]] SET
                [title]            = {title},
                [fast_url]         = {fast_url},
                [meta_keywords]    = {meta_keys},
                [meta_description] = {meta_desc},
                [visible]          = {visible}
            WHERE [id] = {gid}';

        $res = $GLOBALS['db']->query($sql, $params);
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
        $sql = '
            SELECT COUNT([id])
            FROM [[static_pages_groups]]';

        $count = $GLOBALS['db']->queryOne($sql);
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

        $sql = 'DELETE FROM [[static_pages_groups]] WHERE [id] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
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
        $res[0] = $this->SetRegistry('default_page', $defaultPage);
        $res[1] = $this->SetRegistry('multilanguage', $multiLang);
        
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
     * @param   int     $offset     Data limit
     * @return  array   List of pages
     */
    function SearchPages($group, $status, $search, $offset = null)
    {
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

        $sql = '
            SELECT
                sp.[page_id], sp.[group_id], spg.[title] as gtitle, spt.[title], sp.[fast_url],
                sp.[base_language], spt.[published], spt.[updated]
            FROM [[static_pages]] sp
            LEFT JOIN [[static_pages_groups]] spg ON sp.[group_id] = spg.[id]
            LEFT JOIN [[static_pages_translation]] spt ON sp.[page_id] = spt.[base_id]
            WHERE sp.[base_language] = spt.[language]';

        if (trim($search) != '') {
            $searchdata = explode(' ', $search);
            /**
             * This query needs more work, not use $v straight, should be
             * like rest of the param stuff.
             */
            $i = 0;
            foreach ($searchdata as $v) {
                $v = trim($v);
                $sql .= " AND (spt.[title] LIKE {textLike_".$i."} OR spt.[content] LIKE {textLike_".$i."})";
                $params['textLike_'.$i] = '%'.$v.'%';
                $i++;
            }
        }

        if (trim($status) != '') {
            $sql .= ' AND spt.[published] = {status}';
        }

        if (!empty($group)) {
            $sql .= ' AND sp.[group_id] = {group}';
        }

        if (is_numeric($offset)) {
            $limit = 10;
            $result = $GLOBALS['db']->setLimit(10, $offset);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'), _t('STATICPAGE_NAME'));
            }
        }

        $sql.= ' ORDER BY [page_id] ASC';
        $types = array('integer', 'integer', 'text', 'text', 'text', 'text', 'boolean', 'timestamp');
        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_PAGES_NOT_RETRIEVED'), _t('STATICPAGE_NAME'));
        }

        //limit, sort, sortDirection, offset..
        return $result;
    }

}