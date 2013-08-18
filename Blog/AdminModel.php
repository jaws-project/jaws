<?php
require_once JAWS_PATH . 'gadgets/Blog/Model.php';
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_AdminModel extends Blog_Model
{
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

        $now = $GLOBALS['db']->Date();
        $params['name']             = $name;
        $params['description']      = $description;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['createtime']       = $now;
        $params['updatetime']       = $now;

        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $result = $catTable->insert($params)->exec();
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

        $params['name']             = $name;
        $params['description']      = $description;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['updatetime']       = $GLOBALS['db']->Date();

        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $result = $catTable->update($params)->where('id', $cid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
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

        $entrycatTable = Jaws_ORM::getInstance()->table('blog_entrycat');
        $result = $entrycatTable->delete()->where('category_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $result = $catTable->delete()->where('id', $id)->exec();
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
        $params['entry_id']    = (int)$blog_id;
        $params['category_id'] = (int)$category_id;

        $entrycatTable = Jaws_ORM::getInstance()->table('blog_entrycat');
        $result = $entrycatTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'), _t('BLOG_NAME'));
        }
        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
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

        $entrycatTable = Jaws_ORM::getInstance()->table('blog_entrycat');
        $entrycatTable->where('entry_id', (int)$blog_id)->and()->where('category_id', (int)$category_id);
        $result = $entrycatTable->delete()->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'), _t('BLOG_NAME'));
        }

        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
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
        $settings['default_view']               = $this->gadget->registry->fetch('default_view');
        $settings['last_entries_limit']         = $this->gadget->registry->fetch('last_entries_limit');
        $settings['popular_limit']              = $this->gadget->registry->fetch('popular_limit');
        $settings['default_category']           = $this->gadget->registry->fetch('default_category');
        $settings['xml_limit']                  = $this->gadget->registry->fetch('xml_limit');
        $settings['comments']                   = $this->gadget->registry->fetch('allow_comments');
        $settings['trackback']                  = $this->gadget->registry->fetch('trackback');
        $settings['trackback_status']           = $this->gadget->registry->fetch('trackback_status');
        $settings['last_comments_limit']        = $this->gadget->registry->fetch('last_comments_limit');
        $settings['last_recentcomments_limit']  = $this->gadget->registry->fetch('last_recentcomments_limit');
        $settings['comment_status']             = $this->gadget->registry->fetch('comment_status');
        $settings['pingback']                   = $this->gadget->registry->fetch('pingback');

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
        $result[] = $this->gadget->registry->update('default_view', $view);
        $result[] = $this->gadget->registry->update('last_entries_limit', $limit);
        $result[] = $this->gadget->registry->update('popular_limit', $popularLimit);
        $result[] = $this->gadget->registry->update('default_category', $category);
        $result[] = $this->gadget->registry->update('xml_limit', $xml_limit);
        $result[] = $this->gadget->registry->update('allow_comments', $comments);
        $result[] = $this->gadget->registry->update('comment_status', $comment_status);
        $result[] = $this->gadget->registry->update('trackback', $trackback);
        $result[] = $this->gadget->registry->update('trackback_status', $trackback_status);
        $result[] = $this->gadget->registry->update('last_comments_limit', $commentsLimit);
        $result[] = $this->gadget->registry->update('last_recentcomments_limit', $recentcommentsLimit);
        $result[] = $this->gadget->registry->update('pingback', $pingback);

        foreach ($result as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_SETTINGS_NOT_SAVE'), _t('BLOG_NAME'));
            }
        }

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

        $date = $GLOBALS['app']->loadDate();
        $now = $GLOBALS['db']->Date();

        $params['user_id']          = $user;
        $params['title']            = $title;
        $params['text']             = str_replace("\r\n", "\n", $content);
        $params['summary']          = str_replace("\r\n", "\n", $summary);
        $params['trackbacks']       = $trackbacks;
        $params['published']        = $this->gadget->GetPermission('PublishEntries')? $publish : false;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['allow_comments']   = $allow_comments;
        $params['createtime']       = $now;
        $params['updatetime']       = $now;

        // Switch out for the MDB2 way
        if (!is_bool($params['allow_comments'])) {
            $params['allow_comments'] = $params['allow_comments'] == '1' ? true : false;
        }

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] == '1' ? true : false;
        }

        if (!is_null($timestamp)) {
            // Maybe we need to not allow crazy dates, e.g. 100 years ago
            $timestamp = $date->ToBaseDate(preg_split('/[- :]/', $timestamp), 'Y-m-d H:i:s');
            $params['publishtime'] = $GLOBALS['app']->UserTime2UTC($timestamp,  'Y-m-d H:i:s');
        } else {
            $params['publishtime'] = $now;
        }

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $result = $blogTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), _t('BLOG_NAME'));
        }

        $max = Jaws_ORM::getInstance()->table('blog')->select('max(id)')->where('title', $title)->fetchOne();
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

        if ($this->gadget->registry->fetch('pingback') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Pingback.php';
            $pback =& Jaws_PingBack::getInstance();
            $pback->sendFromString($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $max), true),
                $params['text']);
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
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

        $params['title']            = $title;
        $params['text']             = str_replace("\r\n", "\n", $content);
        $params['summary']          = str_replace("\r\n", "\n", $summary);
        $params['trackbacks']       = $trackbacks;
        $params['published']        = $publish;
        $params['allow_comments']   = $allow_comments;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['updatetime']       = $GLOBALS['db']->Date();

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] == '1' ? true : false;
        }

        if (!is_bool($params['allow_comments'])) {
            $params['allow_comments'] = $params['allow_comments'] == '1' ? true : false;
        }

        $e = $this->GetEntry($post_id);
        if (Jaws_Error::IsError($e)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($e['published'] && !$this->gadget->GetPermission('ModifyPublishedEntries')) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($GLOBALS['app']->Session->GetAttribute('user') != $e['user_id']) {
            if (!$this->gadget->GetPermission('ModifyOthersEntries')) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        if (!$this->gadget->GetPermission('PublishEntries')) {
            $params['published']  = $e['published'];
        }

        //Current fast url changes?
        if ($e['fast_url'] != $fast_url && $autoDraft === false) {
            $fast_url = $this->GetRealFastUrl($fast_url, 'blog');
            $params['fast_url'] = $fast_url;
        }

        // Switch out for the MDB2 way
        if (!is_bool($params['allow_comments'])) {
            $params['allow_comments'] = $params['allow_comments'] === 1 ? true : false;
        }

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] === 1 ? true : false;
        }

        $date = $GLOBALS['app']->loadDate();
        if (!is_null($timestamp)) {
            // Maybe we need to not allow crazy dates, e.g. 100 years ago
            $timestamp = $date->ToBaseDate(preg_split('/[- :]/', $timestamp), 'Y-m-d H:i:s');
            $params['publishtime'] = $GLOBALS['app']->UserTime2UTC($timestamp,  'Y-m-d H:i:s');
        }

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $result = $blogTable->update($params)->where('id', $post_id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), _t('BLOG_NAME'));
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
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
                $this->AddCategoryToEntry($post_id, $category);
            } else {
                if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
                    $catAtom = $this->GetCategoryAtomStruct($category);
                    $this->MakeCategoryAtom($category, $catAtom, true);
                    $this->MakeCategoryRSS($category, $catAtom, true);
                }
            }
        }

        foreach ($e['categories'] as $k => $v) {
            if (!in_array($v['id'], $categories)) {
                $this->DeleteCategoryInEntry($post_id, $v['id']);
            }
        }

        if ($this->gadget->registry->fetch('pingback') == 'true') {
            require_once JAWS_PATH . 'include/Jaws/Pingback.php';
            $pback =& Jaws_PingBack::getInstance();
            $pback->sendFromString($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $post_id),
                true), $params['text']);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_UPDATED'), RESPONSE_NOTICE);
        return $post_id;
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
            !$this->gadget->GetPermission('ModifyOthersEntries')
        ) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if (is_array($e['categories']) && count($e['categories']) > 0) {
            foreach ($e['categories'] as $k => $v) {
                $this->DeleteCategoryInEntry($post_id, $v['id']);
            }
        }

        $result = Jaws_ORM::getInstance()->table('blog')->delete()->where('id', $post_id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), _t('BLOG_NAME'));
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
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
        $blog_name = urlencode(stripslashes($this->gadget->registry->fetch('site_name', 'Settings')));
        $permalink = urlencode($permalink);

        require_once PEAR_PATH. 'HTTP/Request.php';

        $options = array();
        $timeout = (int)$this->gadget->registry->fetch('connection_timeout', 'Settings');
        $options['timeout'] = $timeout;
        if ($this->gadget->registry->fetch('proxy_enabled', 'Settings') == 'true') {
            if ($this->gadget->registry->fetch('proxy_auth', 'Settings') == 'true') {
                $options['proxy_user'] = $this->gadget->registry->fetch('proxy_user', 'Settings');
                $options['proxy_pass'] = $this->gadget->registry->fetch('proxy_pass', 'Settings');
            }
            $options['proxy_host'] = $this->gadget->registry->fetch('proxy_host', 'Settings');
            $options['proxy_port'] = $this->gadget->registry->fetch('proxy_port', 'Settings');
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
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $howMany = $blogTable->select('count(blog.id)')->join('users', 'blog.user_id', 'users.id')->fetchOne();
        return Jaws_Error::IsError($howMany) ? 0 : $howMany;
    }

    /**
     * Update a post comments count
     *
     * @access  public
     * @param   int     $id              Post id.
     * @param   int     $commentCount    How Many comment
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdatePostCommentsCount($id, $commentCount)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        return $blogTable->update(array('comments'=>$commentCount))->where('id', $id)->exec();
    }

    /**
     * Delete all comments in a given entry
     *
     * @access  public
     * @param   int     $id         Post id.
     */
    function DeleteCommentsIn($id)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'DeleteComments');
        return $cModel->DeleteGadgetComments($this->gadget->name, $id);
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
            $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
            $result = $trackbackTable->update(array('status'=>$status))->where('id', $id)->exec();

            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_TRACKBACK_MARKED'), RESPONSE_NOTICE);
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
            $blogTable = Jaws_ORM::getInstance()->table('blog');
            $result = $blogTable->update(array('published'=>(bool) $status))->where('id', $id)->exec();
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
        $result = Jaws_ORM::getInstance()->table('blog_trackback')->delete()->where('id', $id)->exec();

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

        $table = Jaws_ORM::getInstance()->table('blog_trackback');
        $table->select(
            'id:integer', 'parent_id:integer', 'blog_name', 'url', 'title', 'ip', 'url', 'status', 'createtime'
        );

        switch ($filterMode) {
            case 'postid':
                $table->and()->where('parent_id', $filterData);
                break;
            case 'blog_name':
                $table->and()->where('blog_name', $filterData, 'like');
                break;
            case 'url':
                $table->and()->where('url', $filterData, 'like');
                break;
            case 'title':
                $table->and()->where('title', $filterData, 'like');
                break;
            case 'ip':
                $table->and()->where('ip', $filterData, 'like');
                break;
            case 'excerpt':
                $table->and()->where('excerpt', $filterData, 'like');
                break;
            case 'various':
                $table->and()->openWhere()->where('blog_name', $filterData, 'like')->or();
                $table->where('url', $filterData, 'like')->or();
                $table->where('title', $filterData, 'like')->or();
                $table->where('excerpt', $filterData, 'like')->closeWhere();
                break;
            default:
                if (is_bool($limit)) {
                    $limit = false;
                    //By default we get the last 20 comments
                    $table->limit(20);
                }
                break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $table->and()->where('status', $status);
        }

        if (is_numeric($limit)) {
            $table->limit(10, $limit);
        }

        $rows = $table->orderBy('createtime desc')->fetchAll();
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

        $date = $GLOBALS['app']->loadDate();
        $data = array();
        foreach ($trackbacks as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            $newRow['blog_name']    = '<a href="'.Jaws_XSS::filter($row['url']).'">'.Jaws_XSS::filter($row['blog_name']).'</a>';;

            $url = BASE_SCRIPT . '?gadget=Blog&action=ViewTrackback&id='.$row['id'];
            $newRow['title']   = '<a href="'.$url.'">'.Jaws_XSS::filter($row['title']).'</a>';

            $newRow['created'] = $date->Format($row['createtime']);
            switch($row['status']) {
                case 'approved':
                    $newRow['status'] = _t('COMMENTS_STATUS_APPROVED');
                    break;
                case 'waiting':
                    $newRow['status'] = _t('COMMENTS_STATUS_WAITING');
                    break;
                case 'spam':
                    $newRow['status'] = _t('COMMENTS_STATUS_SPAM');
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
     * @param   mixed   $limit          Limit of data (numeric/boolean: no limit)
     * @return  mixed   Returns how many trackbacks exists with a given filter or Jaws_Error on failure
     */
    function HowManyFilteredTrackbacks($filterMode, $filterData, $status, $limit)
    {
        if (
            $filterMode != 'postid' &&
            $filterMode != 'status' &&
            $filterMode != 'ip'
        ) {
            $filterData = '%'.$filterData.'%';
        }

        $table = Jaws_ORM::getInstance()->table('blog_trackback');
        $table->select('count(*) as howmany');


        switch ($filterMode) {
            case 'postid':
                $table->and()->where('parent_id', $filterData);
                break;
            case 'blog_name':
                $table->and()->where('blog_name', $filterData, 'like');
                break;
            case 'url':
                $table->and()->where('url', $filterData, 'like');
                break;
            case 'title':
                $table->and()->where('title', $filterData, 'like');
                break;
            case 'ip':
                $table->and()->where('ip', $filterData, 'like');
                break;
            case 'excerpt':
                $table->and()->where('excerpt', $filterData, 'like');
                break;
            case 'various':
                $table->and()->openWhere()->where('blog_name', $filterData, 'like')->or();
                $table->where('url', $filterData, 'like')->or();
                $table->where('title', $filterData, 'like')->or();
                $table->where('excerpt', $filterData, 'like')->closeWhere();
                break;
            default:
                if (is_bool($limit)) {
                    $limit = false;
                    //By default we get the last 20 comments
                    $table->limit(20);
                }
                break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $table->and()->where('status', $status);
        }

        if (is_numeric($limit)) {
            $table->limit(10, $limit);
        }

        $howmany = $table->fetchOne();
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
        }

        return $howmany;
    }

}