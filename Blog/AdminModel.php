<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Blog/Model.php';

class BlogAdminModel extends BlogModel
{
    /**
     * Install Blog gadget in Jaws
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'xml' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('BLOG_NAME'));
        }

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
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/columns',                   '1');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/default_view',              'last_entries');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/last_entries_limit',        '20');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/popular_limit',             '10');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/xml_limit',                 '10');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/default_category',          '1');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/allow_comments',            'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/comment_status',            'approved');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/last_comments_limit',       '20');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/last_recentcomments_limit', '20');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/generate_xml',              'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/generate_category_xml',     'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/trackback',                 'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/trackback_status',          'approved');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/plugabble',                 'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/use_antispam',              'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blog/pingback',                  'true');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on success and Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $tables = array('blog',
                        'blog_trackback',
                        'blog_category',
                        'blog_entrycat');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('BLOG_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/columns');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/default_view');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/last_entries_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/popular_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/xml_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/default_category');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/allow_comments');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/comment_status');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/last_comments_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/last_recentcomments_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/generate_xml');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/generate_category_xml');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/trackback');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/trackback_status');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/plugabble');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/use_antispam');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blog/pingback');

        // Recent comments
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_Gadget);
        $api->DeleteCommentsOfGadget();

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success, Jaws_Error on Failure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.8', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.4.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.9', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Blog/ModifyPublishedEntries',  'false');
        }

        return true;
    }

    /**
     * Creates a new category
     *
     * @access  public
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords of the category
     * @param   string  $meta_desc      Meta description of the category
     * @return  mixed   True on success, Jaws_Error on failure
     */
    function NewCategory($name, $description, $fast_url, $meta_keywords, $meta_desc)
    {
        $fast_url = empty($fast_url) ? $name : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog_category');

        $params = array();
        $params['name']          = $name;
        $params['description']   = $description;
        $params['fast_url']      = $fast_url;
        $params['meta_keywords'] = $meta_keywords;
        $params['meta_desc']     = $meta_desc;
        $params['now']           = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[blog_category]]
                ([name], [description], [fast_url], [meta_keywords], [meta_description], [createtime], [updatetime])
            VALUES
                ({name}, {description}, {fast_url}, {meta_keywords}, {meta_desc}, {now}, {now})';

        $result  = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), _t('BLOG_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates a category entry
     *
     * @access  public
     * @param   int     $cid            Category ID
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords of the category
     * @param   string  $meta_desc      Meta description of the category
     * @return  mixed   True on success, Jaws_Error on failure
     */
    function UpdateCategory($cid, $name, $description, $fast_url, $meta_keywords, $meta_desc)
    {
        $fast_url = empty($fast_url) ? $name : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog_category', false);

        $params = array();
        $params['id']            = $cid;
        $params['name']          = $name;
        $params['description']   = $description;
        $params['fast_url']      = $fast_url;
        $params['meta_keywords'] = $meta_keywords;
        $params['meta_desc']     = $meta_desc;
        $params['now']           = $GLOBALS['db']->Date();

        $sql = '
            UPDATE [[blog_category]] SET
                [name]             = {name},
                [description]      = {description},
                [fast_url]         = {fast_url},
                [meta_keywords]    = {meta_keywords},
                [meta_description] = {meta_desc},
                [updatetime]       = {now}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_category_xml') == 'true') {
            $catAtom = $this->GetCategoryAtomStruct($cid);
            $this->MakeCategoryAtom($cid, $catAtom, true);
            $this->MakeCategoryRSS($cid, $catAtom, true);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a category entry
     *
     * @access  public
     * @param   int     $id     ID of category
     * @return  mixed   Returns True if Category was successfully deleted, else Jaws_Error
     */
    function DeleteCategory($id)
    {
        $params       = array();
        $params['id'] = $id;

        /**
         * Uncomment if you want don't want a category associated with a post
        $sql = "SELECT COUNT([entry_id]) FROM [[blog_entrycat]] WHERE [category_id] = {id}";
        $count = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($count)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if ($count > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORIES_LINKED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_LINKED'), _t('BLOG_NAME'));
        }
        **/

        $sql = 'DELETE FROM [[blog_entrycat]] WHERE [category_id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        $sql = 'DELETE FROM [[blog_category]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Add a category to a given entry (in blog_category table)
     *
     * @access  public
     * @param   int     $blog_id        Post ID
     * @param   int     $category_id    Category ID
     * @return  mixed   Returns True if everything is ok, else Jaws_Error
     */
    function AddCategoryToEntry($blog_id, $category_id)
    {
        $params = array();
        $params['entry_id']    = (int)$blog_id;
        $params['category_id'] = (int)$category_id;
        $sql = 'INSERT INTO [[blog_entrycat]] VALUES({entry_id}, {category_id})';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'), _t('BLOG_NAME'));
        }
        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_category_xml') == 'true') {
            $catAtom = $this->GetCategoryAtomStruct($category_id);
            if (Jaws_Error::IsError($catAtom)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), _t('BLOG_NAME'));
            } else {
                $this->MakeCategoryAtom($category_id, $catAtom, true);
                $this->MakeCategoryRSS($category_id, $catAtom, true);
            }
        }
        return true;
    }

    /**
     * Delete category from an entry
     *
     * @param   int     $blog_id        Post ID
     * @param   int     $category_id    Category ID
     * @return  mixed   Returns True if everything is ok, else Jaws_Error
     */
    function DeleteCategoryInEntry($blog_id, $category_id)
    {
        $params = array();
        $params['entry_id']    = (int)$blog_id;
        $params['category_id'] = (int)$category_id;
        $sql = 'DELETE FROM [[blog_entrycat]] WHERE [entry_id] = {entry_id} AND [category_id] = {category_id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_category_xml') == 'true') {
            $catAtom = $this->GetCategoryAtomStruct($category_id);
            if (Jaws_Error::IsError($catAtom)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), _t('BLOG_NAME'));
            }

            $this->MakeCategoryAtom($category_id, $catAtom, true);
            $this->MakeCategoryRSS($category_id, $catAtom, true);
        }

        return true;
    }

    /**
     * Get all the main settings of the Blog
     *
     * @access  public
     * @return  array   An array of settings
     */
    function GetSettings()
    {
        $settings = array();
        $settings['default_view']               = $GLOBALS['app']->Registry->Get('/gadgets/Blog/default_view');
        $settings['last_entries_limit']         = $GLOBALS['app']->Registry->Get('/gadgets/Blog/last_entries_limit');
        $settings['popular_limit']              = $GLOBALS['app']->Registry->Get('/gadgets/Blog/popular_limit');
        $settings['default_category']           = $GLOBALS['app']->Registry->Get('/gadgets/Blog/default_category');
        $settings['xml_limit']                  = $GLOBALS['app']->Registry->Get('/gadgets/Blog/xml_limit');
        $settings['comments']                   = $GLOBALS['app']->Registry->Get('/gadgets/Blog/allow_comments');
        $settings['trackback']                  = $GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback');
        $settings['trackback_status']           = $GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback_status');
        $settings['last_comments_limit']        = $GLOBALS['app']->Registry->Get('/gadgets/Blog/last_comments_limit');
        $settings['last_recentcomments_limit']  = $GLOBALS['app']->Registry->Get('/gadgets/Blog/last_recentcomments_limit');
        $settings['comment_status']             = $GLOBALS['app']->Registry->Get('/gadgets/Blog/comment_status');
        $settings['pingback']                   = $GLOBALS['app']->Registry->Get('/gadgets/Blog/pingback');

        return $settings;
    }

    /**
     * Save the main settings of the Blog
     *
     * @access  public
     * @param   string  $view                   The default View
     * @param   int     $limit                  Limit of entries that blog will show
     * @param   int     $popularLimit           Limit of popular entries
     * @param   int     $commentsLimit          Limit of comments that blog will show
     * @param   int     $recentcommentsLimit    Limit of recent comments to display
     * @param   string  $category               The default category for blog entries
     * @param   int     $xml_limit              xml limit
     * @param   bool    $comments               If comments should appear
     * @param   string  $comment_status         Default comment status
     * @param   bool    $trackback              If Trackback should be used
     * @param   string  $trackback_status       Default trackback status
     * @param   bool    $pingback               If Pingback should be used
     * @return  mixed   Return True if settings were saved without problems, else Jaws_Error
     */
    function SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category, 
                          $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                          $pingback)
    {
        $result = array();
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/default_view', $view);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/last_entries_limit', $limit);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/popular_limit', $popularLimit);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/default_category', $category);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/xml_limit', $xml_limit);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/allow_comments', $comments);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/comment_status', $comment_status);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/trackback', $trackback);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/trackback_status', $trackback_status);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/last_comments_limit', $commentsLimit);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/last_recentcomments_limit', $recentcommentsLimit);
        $result[] = $GLOBALS['app']->Registry->Set('/gadgets/Blog/pingback', $pingback);

        foreach ($result as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_SETTINGS_NOT_SAVE'), _t('BLOG_NAME'));
            }
        }
        $GLOBALS['app']->Registry->Commit('Blog');
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Creates a new post
     *
     * @access  public
     * @param   int     $user           User ID
     * @param   array   $categories     Array with categories id's
     * @param   string  $title          Title of the entry
     * @param   string  $summary        post summary
     * @param   string  $content        Content of the entry
     * @param   string  $fast_url       FastURL
     * @param   string  $meta_keywords  Meta keywords
     * @param   string  $meta_desc      Meta description
     * @param   bool    $allow_comments If entry should allow commnets
     * @param   bool    $trackbacks     
     * @param   bool    $publish        If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @param   bool    $autodraft      Does it comes from an autodraft action?
     * @return  mixed   Returns the ID of the new post or Jaws_Error on failure
     */
    function NewEntry($user, $categories, $title, $summary, $content, $fast_url, $meta_keywords, $meta_desc,
                      $allow_comments, $trackbacks, $publish, $timestamp = null, $autoDraft = false)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog', $autoDraft === false);

        $params                  = array();
        $params['user']          = $user;
        $params['title']         = $title;
        $params['content']       = str_replace("\r\n", "\n", $content);
        $params['summary']       = str_replace("\r\n", "\n", $summary);
        $params['trackbacks']    = $trackbacks;
        $params['publish']       = $GLOBALS['app']->Session->GetPermission('Blog', 'PublishEntries')? $publish : false;
        $params['fast_url']      = $fast_url;
        $params['meta_keywords'] = $meta_keywords;
        $params['meta_desc']     = $meta_desc;
        $params['comments']      = $allow_comments;

        // Switch out for the MDB2 way
        if (!is_bool($params['comments'])) {
            $params['comments'] = $params['comments'] == '1' ? true : false;
        }

        if (!is_bool($params['publish'])) {
            $params['publish'] = $params['publish'] == '1' ? true : false;
        }

        $date = $GLOBALS['app']->loadDate();
        $params['now'] = $GLOBALS['db']->Date();

        if (!is_null($timestamp)) {
            // Maybe we need to not allow crazy dates, e.g. 100 years ago
            $timestamp = $date->ToBaseDate(preg_split('/[- :]/', $timestamp), 'Y-m-d H:i:s');
            $params['publishtime'] = $GLOBALS['app']->UserTime2UTC($timestamp,  'Y-m-d H:i:s');
        } else {
            $params['publishtime'] = $params['now'];
        }

        $sql = '
            INSERT INTO [[blog]]
                ([user_id], [title], [summary], [text], [fast_url], [meta_keywords], [meta_description],
                 [createtime], [updatetime], [publishtime], [trackbacks], [published], [allow_comments])
            VALUES
                ({user}, {title}, {summary}, {content}, {fast_url}, {meta_keywords}, {meta_desc},
                 {now}, {now}, {publishtime}, {trackbacks}, {publish}, {comments})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), _t('BLOG_NAME'));
        }

        $sql = 'SELECT MAX([id]) FROM [[blog]] WHERE [title] = {title}';
        $max = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($max)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), _t('BLOG_NAME'));
        }

        if ($max) {
            // Categories stuff
            if (is_array($categories) && count($categories) > 0) {
                foreach ($categories as $category) {
                    $res = $this->AddCategoryToEntry($max, $category);
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'), RESPONSE_ERROR);
                        return $res;
                    }
                }
            }
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_ADDED'), RESPONSE_NOTICE);
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/pingback') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Pingback.php';
            $pback =& Jaws_PingBack::getInstance();
            $pback->sendFromString($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $max), false, 'site_url'),
                                   $params['content']);
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_xml') == 'true') {
            $this->MakeAtom(true);
            $this->MakeRSS(true);
        }

        return $max;
    }

    /**
     * Updates an entry
     *
     * @access  public
     * @param   int     $post_id        Post ID
     * @param   int     $categories     Categories array
     * @param   string  $title          Title of the Entry
     * @param   string  $summary        entry summary
     * @param   string  $content        Content of the Entry
     * @param   string  $fast_url       FastURL
     * @param   string  $meta_keywords  Meta keywords
     * @param   string  $meta_desc      Meta description
     * @param   bool    $allow_comments If entry should allow commnets
     * @param   bool    $trackbacks     
     * @param   bool    $publish        If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @param   bool    $autodraft      Does it comes from an autodraft action?
     * @return  mixed   Returns the ID of the post or Jaws_Error on failure
     */
    function UpdateEntry($post_id, $categories, $title, $summary, $content, $fast_url, $meta_keywords, $meta_desc,
                         $allow_comments, $trackbacks, $publish, $timestamp = null, $autoDraft = false)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog', false);

        $params                  = array();
        $params['title']         = $title;
        $params['content']       = str_replace("\r\n", "\n", $content);
        $params['summary']       = str_replace("\r\n", "\n", $summary);
        $params['trackbacks']    = $trackbacks;
        $params['published']     = $publish;
        $params['comments']      = $allow_comments;
        $params['id']            = $post_id;
        $params['fast_url']      = $fast_url;
        $params['meta_keywords'] = $meta_keywords;
        $params['meta_desc']     = $meta_desc;

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] == '1' ? true : false;
        }

        if (!is_bool($params['comments'])) {
            $params['comments'] = $params['comments'] == '1' ? true : false;
        }

        $e = $this->GetEntry($params['id']);
        if (Jaws_Error::IsError($e)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($e['published'] && !$GLOBALS['app']->Session->GetPermission('Blog', 'ModifyPublishedEntries')) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Session->GetAttribute('user') != $e['user_id']) {
            if (!$GLOBALS['app']->Session->GetPermission('Blog', 'ModifyOthersEntries')) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        if (!$GLOBALS['app']->Session->GetPermission('Blog', 'PublishEntries')) {
            $params['published']  = $e['published'];
        }

        //Current fast url changes?
        if ($e['fast_url'] != $fast_url && $autoDraft === false) {
            $fast_url = $this->GetRealFastUrl($fast_url, 'blog');
            $params['fast_url'] = $fast_url;
        }

        // Switch out for the MDB2 way
        if (!is_bool($params['comments'])) {
            $params['comments'] = $params['comments'] === 1 ? true : false;
        }

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] === 1 ? true : false;
        }

        $params['now'] = $GLOBALS['db']->Date();

        $sql = '
            UPDATE [[blog]] SET
                [title] = {title},
                [fast_url] = {fast_url},
                [meta_keywords] = {meta_keywords},
                [meta_description] = {meta_desc},
                [summary]  = {summary},
                [text] = {content},
                [updatetime] = {now},
                [trackbacks] = {trackbacks},
                [published]  = {published},
                [allow_comments] = {comments}';

        $date = $GLOBALS['app']->loadDate();
        if (!is_null($timestamp)) {
            // Maybe we need to not allow crazy dates, e.g. 100 years ago
            $timestamp = $date->ToBaseDate(preg_split('/[- :]/', $timestamp), 'Y-m-d H:i:s');
            $params['publishtime'] = $GLOBALS['app']->UserTime2UTC($timestamp,  'Y-m-d H:i:s');
            $sql .= ', [publishtime] = {publishtime} ';
        }

        $sql .= ' WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_xml') == 'true') {
            $this->MakeAtom(true);
            $this->MakeRSS (true);
        }

        if (!is_array($categories)) {
            $categories = array();
        }

        $catAux = array();
        foreach ($e['categories'] as $cat) {
            $catAux[] = $cat['id'];
        }

        foreach ($categories as $category) {
            if (!in_array($category, $catAux)) {
                $this->AddCategoryToEntry($params['id'], $category);
            } else {
                if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_category_xml') == 'true') {
                    $catAtom = $this->GetCategoryAtomStruct($category);
                    $this->MakeCategoryAtom($category, $catAtom, true);
                    $this->MakeCategoryRSS($category, $catAtom, true);
                }
            }
        }

        foreach ($e['categories'] as $k => $v) {
            if (!in_array($v['id'], $categories)) {
                $this->DeleteCategoryInEntry($params['id'], $v['id']);
            }
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/pingback') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Pingback.php';
            $pback =& Jaws_PingBack::getInstance();
            $pback->sendFromString($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $params['id']),
                                                                   false, 'site_url'),
                                   $params['content']);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_UPDATED'), RESPONSE_NOTICE);
        return $params['id'];
    }

    /**
     * Delete an entry
     *
     * @access  public
     * @param   int     $post_id    The entry ID
     * @return  mixed   True if entry was successfully deleted, Jaws_Error on failure
     */
    function DeleteEntry($post_id)
    {
        $e = $this->GetEntry($post_id);
        if (Jaws_Error::IsError($e)) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if (
            $GLOBALS['app']->Session->GetAttribute('user') != $e['user_id'] &&
            !$GLOBALS['app']->Session->GetPermission('Blog', 'ModifyOthersEntries')
        ) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if (is_array($e['categories']) && count($e['categories']) > 0) {
            foreach ($e['categories'] as $k => $v) {
                $this->DeleteCategoryInEntry($post_id, $v['id']);
            }
        }

        $params = array();
        $params['id'] = $post_id;
        $sql = 'DELETE FROM [[blog]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/generate_xml') == 'true') {
            $this->MakeAtom(true);
            $this->MakeRSS (true);
        }

        // Remove comment entries..
        $this->DeleteCommentsIn($post_id);

        return true;
    }

    /**
     * Send a trackback to a site
     *
     * @access  public
     * @param   string  $title     Title of the Site
     * @param   string  $excerpt   The Excerpt
     * @param   string  $permalink The Permalink to send
     * @param   array   $to        Where to send the trackback
     */
    function SendTrackback($title, $excerpt, $permalink, $to)
    {
        $title = urlencode(stripslashes($title));
        $excerpt = urlencode(stripslashes($excerpt));
        $blog_name = urlencode(stripslashes($GLOBALS['app']->Registry->Get('/config/site_name')));
        $permalink = urlencode($permalink);

        require_once PEAR_PATH. 'HTTP/Request.php';

        $options = array();
        $timeout = (int)$GLOBALS['app']->Registry->Get('/config/connection_timeout');
        $options['timeout'] = $timeout;
        if ($GLOBALS['app']->Registry->Get('/network/proxy_enabled') == 'true') {
            if ($GLOBALS['app']->Registry->Get('/network/proxy_auth') == 'true') {
                $options['proxy_user'] = $GLOBALS['app']->Registry->Get('/network/proxy_user');
                $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('/network/proxy_pass');
            }
            $options['proxy_host'] = $GLOBALS['app']->Registry->Get('/network/proxy_host');
            $options['proxy_port'] = $GLOBALS['app']->Registry->Get('/network/proxy_port');
        }

        $httpRequest = new HTTP_Request('', $options);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
        foreach ($to as $url) {
            $httpRequest->setURL($url);
            $httpRequest->addPostData('title',     $title);
            $httpRequest->addPostData('url',       $permalink);
            $httpRequest->addPostData('blog_name', $blog_name);
            $httpRequest->addPostData('excerpt',   $excerpt);
            $resRequest = $httpRequest->sendRequest();
            $httpRequest->clearPostData();
        }
    }

    /**
     * Get the total number of posts of an user
     *
     * @access  public
     * @return  int     Number of posts on Success, or zero on error
     */
    function TotalOfPosts()
    {
        $sql = '
            SELECT
                COUNT([[blog]].[id])
            FROM [[blog]]
            INNER JOIN [[users]] ON [[blog]].[user_id] = [[users]].[id]';

        $howMany = $GLOBALS['db']->queryOne($sql);

        return Jaws_Error::IsError($howMany) ? 0 : $howMany;
    }

    /**
     * Updates a comment
     *
     * @access  public
     * @param   string  $id         Comment id
     * @param   string  $name       Name of the author
     * @param   string  $title      Title of the comment
     * @param   string  $url        Url of the author
     * @param   string  $email      Email of the author
     * @param   string  $comments   Text of the comment
     * @param   string  $permalink  Permanent link to post
     * @param   string  $status     Comment Status
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateComment($id, $name, $title, $url, $email, $comments, $permalink, $status)
    {
        require_once JAWS_PATH . 'include/Jaws/Comment.php';

        $params              = array();
        $params['id']        = $id;
        $params['name']      = strip_tags($name);
        $params['title']     = strip_tags($title);
        $params['url']       = strip_tags($url);
        $params['email']     = strip_tags($email);
        $params['comments']  = strip_tags($comments);
        $params['permalink'] = $permalink;
        $params['status']    = $status;

        $api = new Jaws_Comment($this->_Gadget);
        $res = $api->UpdateComment($params['id'],        $params['name'],
                                   $params['email'],     $params['url'],
                                   $params['title'],     $params['comments'],
                                   $params['permalink'], $params['status']);

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_UPDATED'), _t('BLOG_NAME'));
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_COMMENT_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a comment
     *
     * @access  public
     * @param   string  $id     Comment id
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function DeleteComment($id)
    {
        require_once JAWS_PATH.'include/Jaws/Comment.php';

        $comment = $this->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
        }

        $api = new Jaws_Comment($this->_Gadget);
        $res = $api->DeleteComment($id);

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if ($comment['status'] == COMMENT_STATUS_APPROVED) {
            $params = array();
            $params['id'] = $comment['gadget_reference'];
            $howmany = $api->HowManyFilteredComments('gadget_reference',
                                                     $comment['gadget_reference'],
                                                     'approved');
            if (!Jaws_Error::IsError($howmany)) {
                $params['comments'] = $howmany;
                $sql = 'UPDATE [[blog]] SET [comments] = {comments} WHERE [id] = {id}';
                $result = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
                    return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
                }
            }
        }

        return true;
    }

    /**
     * Delete all comments in a given entry
     *
     * @access  public
     * @param   int     $id         Post id.
     */
    function DeleteCommentsIn($id)
    {
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_Gadget);
        $res = $api->DeleteCommentsByReference($id);
    }

    /**
     * Mark as different status a comment
     *
     * @access  public
     * @param   array   $ids     Id's of the comments to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function MarkCommentsAs($ids, $status = 'spam')
    {
        if (count($ids) == 0 || empty($status)) {
            return true;
        }

        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_Gadget);
        $api->MarkAs($ids, $status);

        // Fix blog comment counter...
        foreach ($ids as $id) {
            $comment = $api->GetComment($id);
            $params = array();
            $params['id'] = $comment['gadget_reference'];
            $howmany = $api->HowManyFilteredComments('gadget_reference',
                                                     $comment['gadget_reference'],
                                                     'approved');
            if (!Jaws_Error::IsError($howmany)) {
                $params['comments'] = $howmany;
                $sql = 'UPDATE [[blog]] SET [comments] = {comments} WHERE [id] = {id}';
                $result = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_UPDATED'), RESPONSE_ERROR);
                    return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_UPDATED'), _t('BLOG_NAME'));
                }
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_COMMENT_MARKED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Mark as different status a trackback
     *
     * @access  public
     * @param   array   $ids     Id's of the trackbacks to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function MarkTrackbacksAs($ids, $status = 'spam')
    {
        if (count($ids) == 0 || empty($status)) {
            return true;
        }

        // Fix blog trackback counter...
        foreach ($ids as $id) {
            $sql = 'UPDATE [[blog_trackback]] SET [status] = {status} WHERE [id] = {id}';
            $result = $GLOBALS['db']->query($sql, array('id' => $id, 'status' => $status));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_TRACKBACK_MARKED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Does a massive comment delete
     *
     * @access  public
     * @param   array   $ids  Ids of comments
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveCommentDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->DeleteComment($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_COMMENT_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Does a massive entry delete
     *
     * @access  public
     * @param   array   $ids  Ids of entries
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveEntryDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->DeleteEntry($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Change status of group of entries ids
     *
     * @access  public
     * @param   array   $ids        Ids of entries
     * @param   string  $status     New status
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function ChangeEntryStatus($ids, $status = '0')
    {
        if (count($ids) == 0) {
            return true;
        }

        foreach ($ids as $id) {
            $sql = 'UPDATE [[blog]] SET [published] = {published} WHERE [id] = {id}';
            $result = $GLOBALS['db']->query($sql, array('id' => $id, 'published' => (bool) $status));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Does a massive trackback delete
     *
     * @access  public
     * @param   array   $ids  Ids of trackbacks
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveTrackbackDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->DeleteTrackback($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TRACKBACK_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_TRACKBACK_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a trackback
     *
     * @access  public
     * @param   int     $id     Trackback's ID
     * @return  mixed   True if sucess or Jaws_Error on any error
     */
    function DeleteTrackback($id)
    {
        $params             = array();
        $params['id']       = $id;

        $sql = 'DELETE FROM [[blog_trackback]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_TRACKBACKS_ERROR_NOT_DELETED'), 'CORE');
        }

        return true;
    }

    /**
     * Gets a list of trackbacks that match a certain filter.
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $filterMode     Which mode should be used to filter
     * @param   string  $filterData     Data that will be used in the filter
     * @param   string  $status         Spam status (approved, waiting, spam)
     * @param   mixed   $limit          Limit of data (numeric/boolean: no limit)
     * @return  mixed   Returns an array with of filtered trackbacks or Jaws_Error on error
     */
    function GetFilteredTrackbacks($filterMode, $filterData, $status, $limit)
    {
        if (
            $filterMode != 'postid' &&
            $filterMode != 'status' &&
            $filterMode != 'ip'
            ) {
            $filterData = '%'.$filterData.'%';
        }

        $params = array();
        $params['filterData'] = $filterData;

        $sql = '
            SELECT
                [id],
                [parent_id],
                [blog_name],
                [url],
                [title],
                [ip],
                [url],
                [status],
                [createtime]
            FROM [[blog_trackback]]';

        $sql_condition = '';
        switch ($filterMode) {
        case 'postid':
            $sql_condition.= ' AND [parent_id] = {filterData}';
            break;
        case 'blog_name':
            $sql_condition.= ' AND [blog_name] LIKE {filterData}';
            break;
        case 'url':
            $sql_condition.= ' AND [url] LIKE {filterData}';
            break;
        case 'title':
            $sql_condition.= ' AND [title] LIKE {filterData}';
            break;
        case 'ip':
            $sql_condition.= ' AND [ip] LIKE {filterData}';
            break;
        case 'excerpt':
            $sql_condition.= ' AND [excerpt] LIKE {filterData}';
            break;
        case 'various':
            $sql_condition.= ' AND ([blog_name] LIKE {filterData}';
            $sql_condition.= ' OR [url] LIKE {filterData}';
            $sql_condition.= ' OR [title] LIKE {filterData}';
            $sql_condition.= ' OR [excerpt] LIKE {filterData})';
            break;
        default:
            if (is_bool($limit)) {
                $limit = false;
                //By default we get the last 20 comments
                $result = $GLOBALS['db']->setLimit('20');
                if (Jaws_Error::IsError($result)) {
                    return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
                }
            }
            break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $params['status'] = $status;
            $sql.= ' AND [status] = {status}';
        }

        if (is_numeric($limit)) {
            $result = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
            }
        }

        $sql .= (empty($sql_condition)? '' : 'WHERE 1=1 ') . $sql_condition;
        $sql .= ' ORDER BY [createtime] DESC';

        $rows = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
        }

        return $rows;
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $filterby Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter   Filter data
     * @param   string  $status   Spam status (approved, waiting, spam)
     * @param   mixed   $limit    Data limit (numeric/boolean)
     * @return  array   Filtered Comments
     */
    function GetTrackbacksDataAsArray($filterby, $filter, $status, $limit)
    {
        $trackbacks = $this->GetFilteredTrackbacks($filterby, $filter, $status, $limit);
        if (Jaws_Error::IsError($trackbacks)) {
            return array();
        }

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $date = $GLOBALS['app']->loadDate();
        $data = array();
        foreach ($trackbacks as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            $newRow['blog_name']    = '<a href="'.$xss->filter($row['url']).'">'.$xss->filter($row['blog_name']).'</a>';;

            $url = BASE_SCRIPT . '?gadget=Blog&action=ViewTrackback&id='.$row['id'];
            $newRow['title']   = '<a href="'.$url.'">'.$xss->filter($row['title']).'</a>';

            $newRow['created'] = $date->Format($row['createtime']);
            switch($row['status']) {
            case 'approved':
                $newRow['status'] = _t('GLOBAL_STATUS_APPROVED');
                break;
            case 'waiting':
                $newRow['status'] = _t('GLOBAL_STATUS_WAITING');
                break;
            case 'spam':
                $newRow['status'] = _t('GLOBAL_STATUS_SPAM');
                break;
            }

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'), $url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                        "javascript: trackbackDelete('".$row['id']."');",
                                        STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $newRow['actions'] = $actions;

            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Counts how many trackbacks are with a given filter
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $filterMode     Which mode should be used to filter
     * @param   string  $filterData     Data that will be used in the filter
     * @param   string  $status         Spam status (approved, waiting, spam)
     * @return  mixed   Returns how many trackbacks exists with a given filter or Jaws_Error on failure
     */
    function HowManyFilteredTrackbacks($filterMode, $filterData, $status)
    {
        if (
            $filterMode != 'postid' &&
            $filterMode != 'status' &&
            $filterMode != 'ip'
            ) {
            $filterData = '%'.$filterData.'%';
        }

        $params = array();
        $params['filterData'] = $filterData;

        $sql = '
            SELECT
                COUNT(*) AS howmany
            FROM [[blog_trackback]]';

        $sql_condition = '';
        switch ($filterMode) {
        case 'postid':
            $sql_condition.= ' AND [parent_id] = {filterData}';
            break;
        case 'blog_name':
            $sql_condition.= ' AND [blog_name] LIKE {filterData}';
            break;
        case 'url':
            $sql_condition.= ' AND [url] LIKE {filterData}';
            break;
        case 'title':
            $sql_condition.= ' AND [title] LIKE {filterData}';
            break;
        case 'ip':
            $sql_condition.= ' AND [ip] LIKE {filterData}';
            break;
        case 'excerpt':
            $sql_condition.= ' AND [excerpt] LIKE {filterData}';
            break;
        case 'various':
            $sql_condition.= ' AND ([blog_name] LIKE {filterData}';
            $sql_condition.= ' OR [url] LIKE {filterData}';
            $sql_condition.= ' OR [title] LIKE {filterData}';
            $sql_condition.= ' OR [excerpt] LIKE {filterData})';
            break;
        default:
            if (is_bool($limit)) {
                $limit = false;
                //By default we get the last 20 comments
                $result = $GLOBALS['db']->setLimit('20');
                if (Jaws_Error::IsError($result)) {
                    return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
                }
            }
            break;
        }

        if ($status != 'various' && (!in_array($status, array('approved', 'waiting', 'spam')))) {
            if ($GLOBALS['app']->Registry->Get('/gadget/blog/trackback_status') == 'waiting') {
                $status = 'waiting';
            } else {
                $status = 'approved';
            }          
        }

        if ($status != 'various') {
            $sql_condition.= ' AND [status] = {status}';
            $params['status'] = $status;
        }

        $sql .= (empty($sql_condition)? '' : 'WHERE 1=1 ') . $sql_condition;

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
        }

        return $howmany;
    }

}