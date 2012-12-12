<?php
/**
 * Chatbox AJAX API
 *
 * @category   Ajax
 * @package    Chatbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ChatboxAdminAjax extends Jaws_Gadget_Ajax
{
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
        $this->CheckSession('Chatbox', 'ManageComments');
        $gadget = $GLOBALS['app']->LoadGadget('Chatbox', 'AdminHTML');
        return $gadget->CommentsData($limit, $filter, $search, $status);
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
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');
        $filterMode = null;
        switch($filter) {
        case 'name':
            $filterMode = COMMENT_FILTERBY_NAME;
            break;
        case 'email':
            $filterMode = COMMENT_FILTERBY_EMAIL;
            break;
        case 'url':
            $filterMode = COMMENT_FILTERBY_URL;
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

        return $cModel->HowManyFilteredComments($this->_Gadget, $filterMode, $search, $status, false);
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
        $this->CheckSession('Chatbox', 'ManageComments');
        $this->_Model->MassiveCommentDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @param   array   $ids    Ids of comments
     * @param   string  $status    New status
     * @return  array   Response array (notice or error)
     */
    function MarkAs($ids, $status)
    {
        $this->CheckSession('Chatbox', 'ManageComments');
        $this->_Model->MarkCommentsAs($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the properties
     *
     * @access  public
     * @param   int     $limit      Limit of chatbox entries
     * @param   int     $max_strlen Maximum length of comment entry
     * @param   bool    $authority
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($limit, $max_strlen, $authority)
    {
        $this->CheckSession('Chatbox', 'UpdateProperties');
        $this->_Model->UpdateProperties($limit, $max_strlen, $authority == 'true');
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}