<?php
/**
 * BlogStaticPage AJAX API
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Ajax extends Jaws_Gadget_Action
{

    /**
     * Parse text
     *
     * @access   public
     * @internal param  string  $text   Input text
     * @return   string  parsed Text
     */
    function ParseText()
    {
        $text = $this->gadget->request->fetch(0, 'post', 'strip_crlf');
        return $this->gadget->plugin->parse($text);
    }

    /**
     * Search for posts and return a datagrid
     *
     * @access   public
     * @internal param  string  $period     Period to look for
     * @internal param  int     $cat        Category ID
     * @internal param  int     $status     Status (0=Draft, 1=Published)
     * @internal param  string  $search     Search word
     * @internal param  int     $limit      Limit data
     * @return   array  Posts Array
     */
    function SearchPosts()
    {
        @list($cat, $status, $search, $limit) = $this->gadget->request->fetchAll('post');
        if(empty($limit)) {
            $limit = 0;
        }
        $gadget = $this->gadget->action->loadAdmin('Entries');
        return $gadget->PostsData($cat, $status, $search, $limit);
    }

    /**
     * Get total posts of a search
     *
     * @access   public
     * @internal param  string  $period     Period to look for
     * @internal param  int     $cat        Category ID
     * @internal param  int     $status     Status (0=Draft, 1=Published)
     * @internal param  string  $search     Search word
     * @return   int    Total of posts
     */
    function SizeOfSearch()
    {
        @list($cat, $status, $search) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Posts');
        $entries = $model->AdvancedSearch(false, $cat, $status, $search,
                                                 $this->app->session->user->id);
        return count($entries);
    }

    /**
     * Save blog settings
     *
     * @access   public
     * @internal param  string      $view           The default View
     * @internal param  int         $limit          Limit of entries that blog will show
     * @internal param  int         $popularLimit   Limit of popular entries
     * @internal param  int         $commentsLimit  Limit of comments that blog will show
     * @internal param  int         $recentcomments Limit Limit of recent comments to display
     * @internal param  string      $category       The default category for blog entries
     * @internal param  xml_limit   $int            limit
     * @internal param  bool        $comments       If comments should appear
     * @internal param  string      $comment_status Default comment status
     * @internal param  bool        $trackback      If Trackback should be used
     * @internal param  string      $trackback_status Default trackback status
     * @internal param  bool        $pingback       If Pingback should be used
     * @return   array  Response array (notice or error)
     */
    function SaveSettings()
    {
        @list($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category,
            $xml_limit, $comments, $trackback, $trackback_status, $pingback
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $model->SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category,
                                    $xml_limit, $comments, $trackback, $trackback_status,
                                    $pingback);
        return $this->gadget->session->pop();
    }

    /**
     * Get a category data
     *
     * @access   public
     * @internal param  int     $id
     * @return   Array  Category data
     */
    function GetCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Categories');
        return $model->GetCategory($id);
    }

    /**
     * Get all categories list
     *
     * @access  public
     * @return  Array  list of all Categories
     */
    function GetCategories()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $this->gadget->model->load('Categories');
        return $model->GetCategories();
    }

    /**
     * Add a new category
     *
     * @access   public
     * @internal param  string  $name           Category name
     * @internal param  string  $description    Category description
     * @internal param  string  $fast_url       Category fast url
     * @internal param  string  $meta_keywords  Meta keywords
     * @internal param  string  $meta_desc      Meta description
     * @return   array  Response array (notice or error)
     */
    function AddCategory2()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $post = $this->gadget->request->fetch(
            array('name', 'description', 'fast_url', 'meta_keywords', 'meta_desc', 'image:array', 'delete_image')
            , 'post'
        );
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->NewCategory($post['name'], $post['description'], $post['fast_url'],
            $post['meta_keywords'], $post['meta_desc'], $post['image'], $post['delete_image']);
        return $this->gadget->session->pop();
    }

    /**
     * Update a category
     *
     * @access   public
     * @internal param  int     $id             ID of category
     * @internal param  string  $name           Name of category
     * @internal param  string  $description    Category description
     * @internal param  string  $fast_url       Category fast url
     * @internal param  string  $meta_keywords  Meta keywords
     * @internal param  string  $meta_desc      Meta description
     * @return   array  Response array (notice or error)
     */
    function UpdateCategory2()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $post = $this->gadget->request->fetch(
            array('id', 'name', 'description', 'fast_url', 'meta_keywords', 'meta_desc', 'image:array', 'delete_image')
            , 'post'
        );

        $model = $this->gadget->model->loadAdmin('Categories');
        $model->UpdateCategory($post['id'], $post['name'], $post['description'], $post['fast_url'],
            $post['meta_keywords'], $post['meta_desc'], $post['image'], $post['delete_image']);
        return $this->gadget->session->pop();
    }

    /**
     * Delete a category
     *
     * @access   public
     * @internal param  int     $id     ID of category
     * @return   array  Response array (notice or error)
     */
    function DeleteCategory2()
    {
        $this->gadget->CheckPermission('ManageCategories');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->DeleteCategory($id);
        return $this->gadget->session->pop();
    }

    /**
     * Search for trackbacks and return the data in an array
     *
     * @access   public
     * @internal param  int     $limit      Data limit
     * @internal param  string  $filter     Filter
     * @internal param  string  $search     Search word
     * @internal param  string  $status     Spam status (approved, waiting, spam)
     * @return   array  Data array
     */
    function SearchTrackbacks()
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        @list($limit, $filter, $search, $status) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Trackbacks');
        return $gadget->TrackbacksData($limit, $filter, $search, $status);
    }

    /**
     * Get total posts of a trackback search
     *
     * @access   public
     * @internal param  string  $filter     Filter
     * @internal param  string  $search     Search word
     * @internal param  string  $status     Spam status (approved, waiting, spam)
     * @return   int    Total of posts
     */
    function SizeOfTrackbacksSearch()
    {
        @list($filter, $search, $status) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        return $model->HowManyFilteredTrackbacks($filter, $search, $status, false);
    }

    /**
     * Does a massive delete on entries
     *
     * @access   public
     * @internal param  array   $ids    Entries ids
     * @return   array  Response array (notice or error)
     */
    function DeleteEntries()
    {
        $this->gadget->CheckPermission('DeleteEntries');
        $ids = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Posts');
        $model->MassiveEntryDelete($ids);
        return $this->gadget->session->pop();
    }

    /**
     * Change status of group of entries ids
     *
     * @access   public
     * @internal param  array   $ids        Ids of entries
     * @internal param  string  $status     New status
     * @return   array   Response array (notice or error)
     */
    function ChangeEntryStatus()
    {
        $this->gadget->CheckPermission('PublishEntries');
        @list($ids, $status) = $this->gadget->request->fetchAll('post');
        $ids = $this->gadget->request->fetch('0:array', 'post');
        $model = $this->gadget->model->loadAdmin('Posts');
        $model->ChangeEntryStatus($ids, $status);
        return $this->gadget->session->pop();
    }

    /**
     * Does a massive delete on trackbacks
     *
     * @access   public
     * @internal param  array   $ids    Trackback ids
     * @return   array  Response array (notice or error)
     */
    function DeleteTrackbacks()
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        $ids = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        $model->MassiveTrackbackDelete($ids);
        return $this->gadget->session->pop();
    }


    /**
     * Mark as different type a group of ids
     *
     * @access   public
     * @internal param  array   $ids    Ids of comments
     * @internal param  string  $status New status
     * @return   array  Response array (notice or error)
     */
    function TrackbackMarkAs()
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        @list($ids, $status) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        $model->MarkTrackbacksAs($ids, $status);
        return $this->gadget->session->pop();
    }

    /**
     * This function will perform an autodraft of the content and set
     * it's value to not published, which will later be changed when the
     * user clicks on save.
     *
     * @access   public
     * @internal param  int     $id             ID
     * @internal param  array   $categories     Array with categories id's
     * @internal param  string  $title          Title of the entry
     * @internal param  string  $summary        Summary of the entry
     * @internal param  string  $text           Content of the entry
     * @internal param  string  $fasturl        FastURL
     * @internal param  string  $meta_keywords  Meta keywords
     * @internal param  string  $meta_desc      Meta description
     * @internal param  string  $tags           Tags
     * @internal param  bool    $allow_comments If entry should allow commnets
     * @internal param  string  $trackbacks     Trackback to send
     * @internal param  bool    $published      If entry should be published
     * @internal param  string  $timestamp      Entry timestamp (optional)
     * @return   array  Response array (notice or error)
     */
    function AutoDraft()
    {
        $this->gadget->CheckPermission('AddEntries');
        @list($id, $categories, $title, $subtitle, $summary, $text, $fasturl, $meta_keywords, $meta_desc, $tags,
            $allow_comments, $trackbacks, $published, $type, $favorite, $timestamp
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Posts');

        $categories = $this->gadget->request->fetch('1:array', 'post');
        $summary = $this->gadget->request->fetch(4, 'post', 'strip_crlf');
        $text    = $this->gadget->request->fetch(5, 'post', 'strip_crlf');

        if ($id == 'NEW') {
            $res = $model->NewEntry(
               $this->app->session->user->id,
               $categories,
               $title,
               $subtitle,
               $summary,
               $text,
               '',
               $fasturl,
               $meta_keywords,
               $meta_desc,
               $tags,
               $allow_comments,
               $trackbacks,
               false,
               $type,
               $favorite,
               $timestamp,
               true
            );
        } else {
            $res = $model->UpdateEntry(
               $id,
               $categories,
               $title,
               $subtitle,
               $summary,
               $text,
               null,
               $fasturl,
               $meta_keywords,
               $meta_desc,
               $tags,
               $allow_comments,
               $trackbacks,
               $published,
               $type,
               $favorite,
               $timestamp,
               true
            );
        }

        $this->gadget->session->pop(); // emptying all responses message
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                $res->getMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                $this::t('ENTRY_AUTOUPDATED', date('H:i:s'), (int)$id, date('D, d')),
                RESPONSE_NOTICE,
                $res
            );
        }
    }
}
