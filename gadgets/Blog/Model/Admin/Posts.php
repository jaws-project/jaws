<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Admin_Posts extends Jaws_Gadget_Model
{
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
        $model = $this->gadget->model->load('Feeds');

        $entrycatTable = Jaws_ORM::getInstance()->table('blog_entrycat', '', '');
        $result = $entrycatTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'));
        }
        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
            $catAtom = $model->GetCategoryAtomStruct($category_id);
            if (Jaws_Error::IsError($catAtom)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'));
            } else {
                $model->MakeCategoryAtom($category_id, $catAtom, true);
                $model->MakeCategoryRSS($category_id, $catAtom, true);
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
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_NOT_ADDED'));
        }

        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
            $model = $this->gadget->model->load('Feeds');
            $catAtom = $model->GetCategoryAtomStruct($category_id);
            if (Jaws_Error::IsError($catAtom)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_XML_NOT_GENERATED'));
            }

            $model->MakeCategoryAtom($category_id, $catAtom, true);
            $model->MakeCategoryRSS($category_id, $catAtom, true);
        }

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
     * @param   string  $tags           Tags
     * @param   bool    $allow_comments If entry should allow commnets
     * @param   bool    $trackbacks
     * @param   bool    $publish        If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @param   bool    $autodraft      Does it comes from an autodraft action?
     * @return  mixed   Returns the ID of the new post or Jaws_Error on failure
     */
    function NewEntry($user, $categories, $title, $summary, $content, $fast_url, $meta_keywords, $meta_desc, $tags,
                      $allow_comments, $trackbacks, $publish, $timestamp = null, $autoDraft = false)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog', $autoDraft === false);

        $date = Jaws_Date::getInstance();
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
        $params['categories']       = implode(',', $categories);
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
        //Start Transaction
        $blogTable->beginTransaction();
        $max = $blogTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_ADDED'));
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

        //Commit Transaction
        $blogTable->commit();

        if ($this->gadget->registry->fetch('pingback') == 'true') {
            $pback = Jaws_Pingback::getInstance();
            $pback->sendFromString($this->gadget->urlMap('SingleView', array('id' => $max), true),
                $params['text']);
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
            $model = $this->gadget->model->load('Feeds');
            $model->MakeAtom(true);
            $model->MakeRSS(true);
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->InsertReferenceTags('Blog', 'post', $max, $params['published'],
                                         strtotime($params['publishtime']), $tags);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TAGS_NOT_ADDED'), RESPONSE_ERROR);
            }
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
     * @param   string  $tags           Tags
     * @param   bool    $allow_comments If entry should allow comments
     * @param   bool    $trackbacks
     * @param   bool    $publish        If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @param   bool    $autodraft      Does it comes from an autodraft action?
     * @return  mixed   Returns the ID of the post or Jaws_Error on failure
     */
    function UpdateEntry($post_id, $categories, $title, $summary, $content, $fast_url, $meta_keywords, $meta_desc,
                         $tags, $allow_comments, $trackbacks, $publish, $timestamp = null, $autoDraft = false)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog', false);

        $params['title']            = $title;
        $params['text']             = str_replace("\r\n", "\n", $content);
        $params['summary']          = str_replace("\r\n", "\n", $summary);
        $params['trackbacks']       = $trackbacks;
        $params['published']        = $publish;
        $params['allow_comments']   = $allow_comments;
        $params['categories']       = implode(',', $categories);
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

        $model = $this->gadget->model->load('Posts');
        $e = $model->GetEntry($post_id);
        if (Jaws_Error::IsError($e)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'));
        }

        if ($e['published'] && !$this->gadget->GetPermission('ModifyPublishedEntries')) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'));
        }

        if ($GLOBALS['app']->Session->GetAttribute('user') != $e['user_id']) {
            if (!$this->gadget->GetPermission('ModifyOthersEntries')) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'));
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

        $date = Jaws_Date::getInstance();
        if (!is_null($timestamp)) {
            // Maybe we need to not allow crazy dates, e.g. 100 years ago
            $timestamp = $date->ToBaseDate(preg_split('/[- :]/', $timestamp), 'Y-m-d H:i:s');
            $params['publishtime'] = $GLOBALS['app']->UserTime2UTC($timestamp,  'Y-m-d H:i:s');
        }

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        //Start Transaction
        $blogTable->beginTransaction();

        $result = $blogTable->update($params)->where('id', $post_id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'));
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
            $model = $this->gadget->model->load('Feeds');
            $model->MakeAtom(true);
            $model->MakeRSS (true);
        }

        if (!is_array($categories)) {
            $categories = array();
        }

        $catAux = array();
        foreach ($e['categories'] as $cat) {
            $catAux[] = $cat['id'];
        }

        $feedModel = $this->gadget->model->load('Feeds');
        foreach ($categories as $category) {
            if (!in_array($category, $catAux)) {
                $this->AddCategoryToEntry($post_id, $category);
            } else {
                if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
                    $model = $this->gadget->model->load('Feeds');
                    $catAtom = $model->GetCategoryAtomStruct($category);
                    $feedModel->MakeCategoryAtom($category, $catAtom, true);
                    $feedModel->MakeCategoryRSS($category, $catAtom, true);
                }
            }
        }

        foreach ($e['categories'] as $k => $v) {
            if (!in_array($v['id'], $categories)) {
                $this->DeleteCategoryInEntry($post_id, $v['id']);
            }
        }

        //Commit Transaction
        $blogTable->commit();

        if ($this->gadget->registry->fetch('pingback') == 'true') {
            $pback = Jaws_Pingback::getInstance();
            $pback->sendFromString($this->gadget->urlMap('SingleView', array('id' => $post_id),
                true), $params['text']);
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags') && !empty($tags)) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->UpdateReferenceTags(
                'Blog',
                'post',
                $post_id,
                $params['published'],
                isset($params['publishtime'])? strtotime($params['publishtime']) : time(),
                $tags
            );
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TAGS_NOT_UPDATED'), RESPONSE_ERROR);
            }
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
        $model = $this->gadget->model->load('Posts');
        $e = $model->GetEntry($post_id);
        if (Jaws_Error::IsError($e)) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'));
        }

        if (
            $GLOBALS['app']->Session->GetAttribute('user') != $e['user_id'] &&
            !$this->gadget->GetPermission('ModifyOthersEntries')
        ) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'));
        }

        if (is_array($e['categories']) && count($e['categories']) > 0) {
            foreach ($e['categories'] as $k => $v) {
                $this->DeleteCategoryInEntry($post_id, $v['id']);
            }
        }

        $result = Jaws_ORM::getInstance()->table('blog')->delete()->where('id', $post_id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'));
        }

        if ($this->gadget->registry->fetch('generate_xml') == 'true') {
            $model = $this->gadget->model->load('Feeds');
            $model->MakeAtom(true);
            $model->MakeRSS (true);
        }

        // Remove comment entries..
        $model = $this->gadget->model->loadAdmin('Comments');
        $model->DeleteCommentsIn($post_id);

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->DeleteReferenceTags('Blog', 'post', $post_id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TAGS_NOT_DELETED'), RESPONSE_ERROR);
                return $res;
            }
        }

        return true;
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
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_DELETED'));
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
                return new Jaws_Error(_t('BLOG_ERROR_ENTRY_NOT_UPDATED'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}