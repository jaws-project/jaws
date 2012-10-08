<?php
/**
 * StaticPage AJAX API
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPageAdminAjax extends Jaws_Ajax
{
    // {{{ Constructor PHP4
    /**
     * PHP 4 Constructor
     *
     * @access  public
     */
    function StaticPageAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Deletes a page and all translated of it.
     *
     * @access  public
     * @param   int     $id  Page ID
     * @return  array   Response (notice or error)
     */
    function DeletePage($id)
    {
        $this->CheckSession('StaticPage', 'DeletePage');
        $this->_Model->DeletePage($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes a translated page
     *
     * @access  public
     * @param   int     $id  Page ID
     * @return  array   Response (notice or error)
     */
    function DeleteTranslation($id)
    {
        $this->CheckSession('StaticPage', 'DeletePage');
        $this->_Model->DeleteTranslation($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Executes a massive-delete of pages
     *
     * @access  public
     * @param   array   $pages  Array with the ids of pages
     * @return  array   Response (notice or error)
     */
    function MassiveDelete($pages)
    {
        $this->CheckSession('StaticPage', 'DeletePage');
        $this->_Model->MassiveDelete($pages);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Update settings
     *
     * @access  public
     * @param   string  $defaultPage  Default page to use
     * @param   string  $multiLang    Use a multilanguage 'schema'?
     * @return  array   Response (notice or error)
     */
    function UpdateSettings($defaultPage, $multiLang)
    {
        $this->CheckSession('StaticPage', 'Properties');
        $this->_Model->UpdateSettings($defaultPage, $multiLang);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminHTML');
        return $gadget->ParseText($text, 'StaticPage');
    }

    /**
     * Get total pages of a search
     *
     * @access  public
     * @param   string  $status  Status of page(s) we want to display
     * @param   string  $search  Keyword (title/description) of pages we want to look for
     * @return  int     Total of posts
     */
    function SizeOfSearch($group, $status, $search)
    {
        $pages = $this->_Model->SearchPages($group, $status, $search, null);
        return count($pages);
    }

    /**
     * Returns an array with all the pages
     *
     * @access  public
     * @param   string  $status  Status of page(s) we want to display
     * @param   string  $search  Keyword (title/description) of pages we want to look for
     * @param   int     $limit   Data limit
     * @return  array   Pages data
     */
    function SearchPages($group, $status, $search, $limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminHTML');
        if (!is_numeric($limit)) {
            $limit = 0;
        }

        return $gadget->GetPages($group, $status, $search, $limit);
    }

    /**
     * This function will perform an autodraft of the content and set
     * it's value to not published, which will later be changed when the
     * user clicks on save.
     *
     * @access  public
     * @param   int    $id        The id of the staticpage id to update
     * @param   int    $group     The group id of the page that blongs to
     * @param   string $fast_url  The value of the fast_url. This will
     *                          be autocreated if nothing is passed.
     * @param   bool   $showtitle This will to know if we show the title or not.
     * @param   string $title     The new autosaved title
     * @param   string $content   The content of the new page
     * @param   string $language  The language of page
     * @param   bool   $published If the item is published or not. Default: draft
     */
    function AutoDraft($id = '', $group, $fast_url = '', $showtitle = '', $title = '', $content = '',
                       $language = '', $published = '')
    {
        if ($id == 'NEW') {
            $this->_Model->AddPage($title, $group, $fast_url, $show_title, $content, $language, $published, true);
            $newid    = $GLOBALS['db']->lastInsertID('static_pages', 'id');
            $response['id'] = $newid;
            $response['message'] = _t('STATICPAGE_PAGE_AUTOUPDATED',
                                      date('H:i:s'),
                                      (int)$id,
                                      date('D, d'));
            $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
        } else {
            $this->_Model->UpdatePage($id, $group, $fast_url, $showtitle, $title, $content, $language, $published, true);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Fetches data of specified group
     *
     * @access  public
     * @param   int     $id    Group ID
     * @return  array   group information
     */
    function GetGroup($id)
    {
        $group = $this->_Model->GetGroup($id);
        if (Jaws_Error::IsError($group)) {
            return false;
        }

        return $group;
    }

    /**
     * Fills the groups data grid
     *
     * @access  public
     * param    bool    $offset     start offset of result boundaries 
     * @return  string  XHTML datagrid
     */
    function GetGroupsGrid($offset)
    {
        $this->CheckSession('StaticPage', 'ManageGroups');
        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminHTML');

        return $gadget->GetGroupsGrid($offset);
    }

    /**
     * Fills the groups data grid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function GetGroupsCount()
    {
        $this->CheckSession('StaticPage', 'ManageGroups');
        return $this->_Model->GetGroupsCount();
    }

    /**
     * Creates a new group
     *
     * @access  public
     * @param    $title      The title of the group
     * @param    $fast_url   Shortcut keyword to link to the group
     * @param    $visible    The visibility of the group

     * @return  array       Response (notice or error)
     */
    function InsertGroup($title, $fast_url, $visible)
    {
        $this->CheckSession('StaticPage', 'ManageGroups');
        $res = $this->_Model->InsertGroup($title, $fast_url, $visible);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_NOTICE_GROUP_CREATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the group
     *
     * @access  public
     * @param    $id         Group ID
     * @param    $title      Title of the group
     * @param    $fast_url   Shortcut keyword to link to the group
     * @param    $visible    Visibility of the group

     * @return  array       Response (notice or error)
     */
    function UpdateGroup($id, $title, $fast_url, $visible)
    {
        $this->CheckSession('StaticPage', 'ManageGroups');
        $res = $this->_Model->UpdateGroup($id, $title, $fast_url, $visible == 'true');
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_NOTICE_GROUP_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $id   group ID
     * @return  array   Response (notice or error)
     */
    function DeleteGroup($id)
    {
        $this->CheckSession('StaticPage', 'ManageGroups');
        $res = $this->_Model->DeleteGroup($id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_NOTICE_GROUP_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}