<?php
/**
 * BlogStaticPage AJAX API
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_AdminAjax extends Jaws_Gadget_HTML
{

    /**
     * Parse text
     *
     * @access  public
     * @param   string  $text  Input text
     * @return  string  parsed Text
     */
    function ParseText($text)
    {
        return $this->gadget->ParseText($text);
    }

    /**
     * Search for posts and return a datagrid
     *
     * @access  public
     * @param   string  $period  Period to look for
     * @param   int     $cat     Category ID
     * @param   int     $status  Status (0=Draft, 1=Published)
     * @param   string  $search  Search word
     * @param   int     $limit   Limit data
     * @return  array   Posts Array
     */
    function SearchPosts($period, $cat, $status, $search, $limit = 0)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML', 'Entries');
        return $gadget->PostsData($period, $cat, $status, $search, $limit);
    }

    /**
     * Get total posts of a search
     *
     * @access  public
     * @param   string  $period  Period to look for
     * @param   int     $cat     Category ID
     * @param   int     $status  Status (0=Draft, 1=Published)
     * @param   string  $search  Search word
     * @return  int     Total of posts
     */
    function SizeOfSearch($period, $cat, $status, $search)
    {
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Posts');
        $entries = $model->AdvancedSearch(false, $period, $cat, $status, $search,
                                                 $GLOBALS['app']->Session->GetAttribute('user'));
        return count($entries);
    }

    /**
     * Save blog settings
     *
     * @access  public
     * @param   string  $view                   The default View
     * @param   int     $limit                  Limit of entries that blog will show
     * @param   int     $popularLimit           Limit of popular entries
     * @param   int     $commentsLimit          Limit of comments that blog will show
     * @param   int     $recentcommentsLimit    Limit of recent comments to display
     * @param   string  $category               The default category for blog entries
     * @param   int     xml_limit               limit
     * @param   bool    $comments               If comments should appear
     * @param   string  $comment_status         Default comment status
     * @param   bool    $trackback              If Trackback should be used
     * @param   string  $trackback_status       Default trackback status
     * @param   bool    $pingback               If Pingback should be used
     * @return  array   Response array (notice or error)
     */
    function SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category, 
                          $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                          $pingback)
    {
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Settings');
        $model->SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category,
                                    $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                                    $pingback);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a category data
     *
     * @access  public
     * @param   int     $id
     * @return  Array  Category data
     */
    function GetCategory($id)
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->loadGadget('Blog', 'Model', 'Categories');
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
        $model = $GLOBALS['app']->loadGadget('Blog', 'Model', 'Categories');
        return $model->GetCategories();
    }

    /**
     * Add a new category
     *
     * @access  public
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords
     * @param   string  $meta_desc      Meta description
     * @return  array   Response array (notice or error)
     */
    function AddCategory($name, $description, $fast_url, $meta_keywords, $meta_desc)
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Categories');
        $model->NewCategory($name, $description, $fast_url, $meta_keywords, $meta_desc);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update a category
     *
     * @access  public
     * @param   int     $id             ID of category
     * @param   string  $name           Name of category
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords
     * @param   string  $meta_desc      Meta description
     * @return  array   Response array (notice or error)
     */
    function UpdateCategory($id, $name, $description, $fast_url, $meta_keywords, $meta_desc)
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Categories');
        $model->UpdateCategory($id, $name, $description, $fast_url, $meta_keywords, $meta_desc);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a category
     *
     * @access  public
     * @param   int     $id   ID of category
     * @return  array   Response array (notice or error)
     */
    function DeleteCategory($id)
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Categories');
        $model->DeleteCategory($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Search for trackbacks and return the data in an array
     *
     * @access  public
     * @param   int     $limit   Data limit
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  array   Data array
     */
    function SearchTrackbacks($limit, $filter, $search, $status)
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML', 'Trackbacks');
        return $gadget->TrackbacksData($limit, $filter, $search, $status);
    }

     /**
     * Get total posts of a trackback search
     *
     * @access  public
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  int     Total of posts
     */
    function SizeOfTrackbacksSearch($filter, $search, $status)
    {
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Trackbacks');
        return $model->HowManyFilteredTrackbacks($filter, $search, $status, false);
    }

    /**
     * Does a massive delete on entries
     *
     * @access  public
     * @param   array   $ids     Entries ids
     * @return  array   Response array (notice or error)
     */
    function DeleteEntries($ids)
    {
        $this->gadget->CheckPermission('DeleteEntries');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Posts');
        $model->MassiveEntryDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Change status of group of entries ids
     *
     * @access  public
     * @param   array   $ids        Ids of entries
     * @param   string  $status     New status
     * @return  array   Response array (notice or error)
     */
    function ChangeEntryStatus($ids, $status)
    {
        $this->gadget->CheckPermission('PublishEntries');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Posts');
        $model->ChangeEntryStatus($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Does a massive delete on trackbacks
     *
     * @access  public
     * @param   array   $ids     Trackback ids
     * @return  array   Response array (notice or error)
     */
    function DeleteTrackbacks($ids)
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Trackbacks');
        $model->MassiveTrackbackDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
    }


    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @param   array   $ids        Ids of comments
     * @param   string  $status     New status
     * @return  array   Response array (notice or error)
     */
    function TrackbackMarkAs($ids, $status)
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Trackbacks');
        $model->MarkTrackbacksAs($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * This function will perform an autodraft of the content and set
     * it's value to not published, which will later be changed when the
     * user clicks on save.
     *
     * @access  public
     * @param   int     $id             ID
     * @param   array   $categories     Array with categories id's
     * @param   string  $title          Title of the entry
     * @param   string  $summary        Summary of the entry
     * @param   string  $text           Content of the entry
     * @param   string  $fasturl        FastURL
     * @param   string  $meta_keywords  Meta keywords
     * @param   string  $meta_desc      Meta description
     * @param   bool    $allow_comments If entry should allow commnets
     * @param   string  $trackbacks     Trackback to send
     * @param   bool    $published      If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @return  array   Response array (notice or error)
     */
    function AutoDraft($id, $categories, $title, $summary, $text, $fasturl, $meta_keywords, $meta_desc,
                       $allow_comments, $trackbacks, $published, $timestamp)
    {
        $this->gadget->CheckPermission('AddEntries');
        $model = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Posts');

        $request =& Jaws_Request::getInstance();
        $summary = $request->get(3, 'post', false);
        $text    = $request->get(4, 'post', false);

        if ($id == 'NEW') {
            $res = $model->NewEntry($GLOBALS['app']->Session->GetAttribute('user'),
                                           $categories,
                                           $title,
                                           $summary,
                                           $text,
                                           $fasturl,
                                           $meta_keywords,
                                           $meta_desc,
                                           $allow_comments,
                                           $trackbacks,
                                           false,
                                           $timestamp,
                                           true);
            if (!Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
                $newid          = $GLOBALS['db']->lastInsertID('blog', 'id');
                $response['id'] = $newid;
                $response['message'] = _t('BLOG_ENTRY_AUTOUPDATED',
                                          date('H:i:s'),
                                          (int)$id,
                                          date('D, d'));
                $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
            }
        } else {
            $model->UpdateEntry($id,
                                       $categories,
                                       $title,
                                       $summary,
                                       $text,
                                       $fasturl,
                                       $meta_keywords,
                                       $meta_desc,
                                       $allow_comments,
                                       $trackbacks,
                                       $published,
                                       $timestamp,
                                       true);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}
