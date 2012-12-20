<?php
/**
 * Phoo AJAX API
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PhooAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Import an image located in 'import' folder
     *
     * @access  public
     * @param   string  $image  Image file
     * @param   string  $name   Name of the image
     * @param   string  $album  In which album the image will be imported
     */
    function ImportImage($image, $name, $album)
    {
        $this->CheckSession('Phoo', 'Import');
        $file = array();
        $file['tmp_name'] = JAWS_DATA . 'phoo/import/' . $image;
        $file['name'] = $image;
        $file['size'] = @filesize($file['tmp_name']);
        $album_data = $this->_Model->getAlbumInfo($album);
        $id = $this->_Model->NewEntry($GLOBALS['app']->Session->GetAttribute('user'),
                                $file,
                                $name,
                                '',
                                false,
                                $album_data);
        $res = $this->_Model->AddEntryToAlbum($id, $album);
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
        $this->CheckSession('Phoo', 'ManageComments');
        $gadget = $GLOBALS['app']->LoadGadget('Phoo', 'AdminHTML');
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
            case 'id':
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

        return $cModel->HowManyFilteredComments($this->name, $filterMode, $search, $status, false);
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
        $this->CheckSession('Phoo', 'ManageComments');
        $this->_Model->MassiveCommentDelete($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @param   array   $ids    Ids of comments
     * @param   string  $status New status
     * @return  array   Response array (notice or error)
     */
    function MarkAs($ids, $status)
    {
        $this->CheckSession('Phoo', 'ManageComments');
        $this->_Model->MarkCommentsAs($ids, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Update album photo information
     *
     * @access  public
     * @param   int     $id             Photo Id
     * @param   string  $title          Photo title
     * @param   string  $desc           Photo description
     * @param   bool    $allow_comments Comment status
     * @param   bool     $published      Publish status
     * @param    array    $albums
     * @return  array   Response array (notice or error)
     */
    function UpdatePhoto($id, $title, $desc, $allow_comments, $published, $albums = null) {
        if (!$this->GetPermission('Phoo', 'ManageAlbums')) {
            $albums    = null;
            $published = null;
        }

        $request =& Jaws_Request::getInstance();
        $desc = $request->get(2, 'post', false);
        $this->_Model->UpdateEntry($id, $title, $desc, $allow_comments, $published, $albums);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}