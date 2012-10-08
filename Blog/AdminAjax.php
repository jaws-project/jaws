<?php
/**
 * BlogStaticPage AJAX API
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlogAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object  $model  model reference
     */
    function BlogAdminAjax(&$model)
    {
        $this->_Model  =& $model;
    }

    /**
     * Parse text
     *
     * @access  public
     * @param   string  $text  Input text
     * @return  string  parsed Text
     */
    function ParseText($text)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
        return $gadget->ParseText($text, 'Blog');
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
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
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
        $entries = $this->_Model->AdvancedSearch(false, $period, $cat, $status, $search,
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
     * @param   string  $commentStatus          Default comment status
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
        $this->_Model->SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category, 
                                    $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                                    $pingback);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Prepare the Category form
     *
     * @access  public
     * @param   string  $action
     * @param   int     $id
     * @return  string  XHTML of Category Form
     */
    function GetCategoryForm($action, $id)
    {
        $this->CheckSession('Blog', 'ManageCategories');
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
        return $gadget->CategoryForm($action, $id);
    }

    /**
     * Add a new category
     *
     * @access  public
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @return  array   Response array (notice or error)
     */
    function AddCategory($name, $description, $fast_url)
    {
        $this->CheckSession('Blog', 'ManageCategories');
        $this->_Model->NewCategory($name, $description, $fast_url);
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
     * @return  array   Response array (notice or error)
     */
    function UpdateCategory($id, $name, $description, $fast_url)
    {
        $this->CheckSession('Blog', 'ManageCategories');
        $this->_Model->UpdateCategory($id, $name, $description, $fast_url);
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
        $this->CheckSession('Blog', 'ManageCategories');
        $this->_Model->DeleteCategory($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Retrieves the category combo (the big one)
     *
     * @access  public
     * @return  string  XHTML of the combo
     */
    function GetCategoryCombo()
    {
        $this->CheckSession('Blog', 'ManageCategories');
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
        return $gadget->GetCategoriesAsCombo();
    }

    /**
     * Search for comments and return the data in an array
     *
     * @access  public
     * @param   int     $limit   Data limit
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  array   Data array
     */
    function SearchComments($limit, $filter, $search, $status)
    {
        $this->CheckSession('Blog', 'ManageComments');
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
        return $gadget->CommentsData($limit, $filter, $search, $status);
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
        $this->CheckSession('Blog', 'ManageTrackbacks');
        $gadget = $GLOBALS['app']->LoadGadget('Blog', 'AdminHTML');
        return $gadget->TrackbacksData($limit, $filter, $search, $status);
    }

    /**
     * Get total posts of a comment search
     *
     * @access  public
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  int     Total of posts
     */
    function SizeOfCommentsSearch($filter, $search, $status)
    {
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment('Blog');
        $filterMode = null;
        switch($filter) {
            case 'postid':
                $filterMode = COMMENT_FILTERBY_REFERENCE;
                break;
            case 'name':
                $filterMode = COMMENT_FILTERBY_NAME;
                break;
            case 'email':
                $filterMode = COMMENT_FILTERBY_EMAIL;
                break;
            case 'url':
                $filterMode = COMMENT_FILTERBY_URL;
                break;
            case 'title':
                $filterMode = COMMENT_FILTERBY_TITLE;
                break;
            case 'ip':
                $filterMode = COMMENT_FILTERBY_IP;
                break;
            case 'comment':
                $filterMode = COMMENT_FILTERBY_MESSAGE;
                break;
            case 'various':
                $filterMode = COMMENT_FILTERBY_VARIOUS;
                break;
            case 'status':
                $filterMode = COMMENT_FILTERBY_STATUS;
                break;
            default:
                $filterMode = null;
                break;
        }
        return $api->HowManyFilteredComments($filterMode, $search, $status, false);
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
        return $this->_Model->HowManyFilteredTrackbacks($filter, $search, $status, false);
    }

    /**
     * Does a massive delete on comments
     *
     * @access  public
     * @param   array   $ids     Comment ids
     * @return  array   Response array (notice or error)
     */
    function DeleteComments($ids)
    {
        $this->CheckSession('Blog', 'ManageComments');
        $this->_Model->MassiveCommentDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        $this->CheckSession('Blog', 'DeleteEntries');
        $this->_Model->MassiveEntryDelete($ids);
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
        $this->CheckSession('Blog', 'PublishEntries');
        $this->_Model->ChangeEntryStatus($ids, $status);
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
        $this->CheckSession('Blog', 'ManageTrackbacks');
        $this->_Model->MassiveTrackbackDelete($ids);
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
    function MarkAs($ids, $status)
    {
        $this->CheckSession('Blog', 'ManageComments');
        $this->_Model->MarkCommentsAs($ids, $status);
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
        $this->CheckSession('Blog', 'ManageTrackbacks');
        $this->_Model->MarkTrackbacksAs($ids, $status);
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
     * @param   bool    $allow_comments If entry should allow commnets
     * @param   string  $trackbacks     Trackback to send
     * @param   bool    $published      If entry should be published
     * @param   string  $timestamp      Entry timestamp (optional)
     * @return  array   Response array (notice or error)
     */
    function AutoDraft($id, $categories, $title, $summary, $text, $fasturl, $allow_comments,
                       $trackbacks, $published, $timestamp)
    {
        $this->CheckSession('Blog', 'AddEntries');

        if ($id == 'NEW') {
            $res = $this->_Model->NewEntry($GLOBALS['app']->Session->GetAttribute('user'),
                                           $categories,
                                           $title,
                                           $summary,
                                           $text,
                                           $fasturl,
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
            $this->_Model->UpdateEntry($id,
                                       $categories,
                                       $title,
                                       $summary,
                                       $text,
                                       $fasturl,
                                       $allow_comments,
                                       $trackbacks,
                                       $published,
                                       $timestamp,
                                       true);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}
