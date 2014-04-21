<?php
/**
 * BlogStaticPage AJAX API
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
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
        $text = jaws()->request->fetch(0, 'post', 'strip_crlf');
        return $this->gadget->ParseText($text);
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
        @list($period, $cat, $status, $search, $limit) = jaws()->request->fetchAll('post');
        if(empty($limit)) {
            $limit = 0;
        }
        $gadget = $this->gadget->action->loadAdmin('Entries');
        return $gadget->PostsData($period, $cat, $status, $search, $limit);
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
        @list($period, $cat, $status, $search) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Posts');
        $entries = $model->AdvancedSearch(false, $period, $cat, $status, $search,
                                                 $GLOBALS['app']->Session->GetAttribute('user'));
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
            $xml_limit, $comments, $comment_status, $trackback, $trackback_status, $pingback
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $model->SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category,
                                    $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                                    $pingback);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id) = jaws()->request->fetchAll('post');
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
        @list($name, $description, $fast_url, $meta_keywords, $meta_desc) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->NewCategory($name, $description, $fast_url, $meta_keywords, $meta_desc);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id, $name, $description, $fast_url, $meta_keywords, $meta_desc) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->UpdateCategory($id, $name, $description, $fast_url, $meta_keywords, $meta_desc);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->DeleteCategory($id);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($limit, $filter, $search, $status) = jaws()->request->fetchAll('post');
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
        @list($filter, $search, $status) = jaws()->request->fetchAll('post');
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
        $ids = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Posts');
        $model->MassiveEntryDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($ids, $status) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Posts');
        $model->ChangeEntryStatus($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        $ids = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        $model->MassiveTrackbackDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($ids, $status) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        $model->MarkTrackbacksAs($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id, $categories, $title, $summary, $text, $fasturl, $meta_keywords, $meta_desc, $tags,
            $allow_comments, $trackbacks, $published, $timestamp
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Posts');

        $categories = jaws()->request->fetch('1:array', 'post');
        $summary = jaws()->request->fetch(3, 'post', 'strip_crlf');
        $text    = jaws()->request->fetch(4, 'post', 'strip_crlf');

        if ($id == 'NEW') {
            $res = $model->NewEntry(
               $GLOBALS['app']->Session->GetAttribute('user'),
               $categories,
               $title,
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
               $timestamp,
               true
            );
            if (!Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
                $newid = $res;
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('BLOG_ENTRY_AUTOUPDATED', date('H:i:s'), (int)$id, date('D, d')),
                    RESPONSE_NOTICE,
                    $newid
                );
            }
        } else {
            $model->UpdateEntry(
                $id,
               $categories,
               $title,
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
               $timestamp,
               true
            );
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}
