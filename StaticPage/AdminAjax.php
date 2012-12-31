<?php
/**
 * StaticPage AJAX API
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function StaticPage_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Deletes the page and all of its translations
     *
     * @access  public
     * @param   int     $id  Page ID
     * @return  array   Response array (notice or error)
     */
    function DeletePage($id)
    {
        $this->gadget->CheckPermission('DeletePage');
        $this->_Model->DeletePage($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the page translation
     *
     * @access  public
     * @param   int     $id  Page ID
     * @return  array   Response array (notice or error)
     */
    function DeleteTranslation($id)
    {
        $this->gadget->CheckPermission('DeletePage');
        $this->_Model->DeleteTranslation($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Executes a batch delete on pages
     *
     * @access  public
     * @param   array   $pages  Array of page IDs
     * @return  array   Response array (notice or error)
     */
    function MassiveDelete($pages)
    {
        $this->gadget->CheckPermission('DeletePage');
        $this->_Model->MassiveDelete($pages);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Updates gadget settings
     *
     * @access  public
     * @param   string  $defaultPage    Default page to use
     * @param   string  $multiLang      Whether uses multilanguage 'schema' or not
     * @return  array   Response array (notice or error)
     */
    function UpdateSettings($defaultPage, $multiLang)
    {
        $this->gadget->CheckPermission('Properties');
        $this->_Model->UpdateSettings($defaultPage, $multiLang);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Parses passed text
     *
     * @access  public
     * @param   string  $text   Input text
     * @return  string  Parsed text
     */
    function ParseText($text)
    {
        $request =& Jaws_Request::getInstance();
        $text = $request->get(0, 'post', false);

        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminHTML');
        return $gadget->gadget->ParseText($text, 'StaticPage');
    }

    /**
     * Gets total number of search results
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   mixed   $status     Status of the page(s) (1/0 or Y/N)
     * @param   string  $search     Keywords(title/description) of the pages we are looking for
     * @return  int     Total number of pages
     */
    function SizeOfSearch($group, $status, $search)
    {
        $pages = $this->_Model->SearchPages($group, $status, $search, null);
        return count($pages);
    }

    /**
     * Searches for specified pages
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   mixed   $status     Status of the pages we are looking for (1/0 or Y/N)
     * @param   string  $search     The Keywords we are looking for in title/description of the pages
     * @param   int     $offset     Data limit
     * @return  array   List of pages
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
     * This function performs an autodraft of the content and set
     * it's value to not published, which will later be changed when the
     * user clicks on save.
     *
     * @access  public
     * @param   int     $id         The ID of the staticpage to update
     * @param   int     $group      The group ID of the page
     * @param   bool    $showtitle  Whether displays page title or not
     * @param   string  $title      Page title
     * @param   string  $content    Page content
     * @param   string  $language   Page language
     * @param   string  $fast_url   The fast URL of the page
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  If the item is published or not. Default: draft
     * @return  array   Response array (notice or error)
     */
    function AutoDraft($id = '', $group, $showtitle = '', $title = '', $content = '', $language = '',
                       $fast_url = '', $meta_keys = '', $meta_desc = '', $published = '')
    {
        $request =& Jaws_Request::getInstance();
        $content = $request->get(4, 'post', false);

        if ($id == 'NEW') {
            $this->_Model->AddPage($title, $group, $show_title, $content, $language, 
                                   $fast_url, $meta_keys, $meta_desc, $published, true);
            $newid    = $GLOBALS['db']->lastInsertID('static_pages', 'id');
            $response['id'] = $newid;
            $response['message'] = _t('STATICPAGE_PAGE_AUTOUPDATED',
                                      date('H:i:s'),
                                      (int)$id,
                                      date('D, d'));
            $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
        } else {
            $this->_Model->UpdatePage($id, $group, $showtitle, $title, $content, $language, 
                                      $fast_url, $meta_keys, $meta_desc, $published, true);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets the group data
     *
     * @access  public
     * @param   int     $id  Group ID
     * @return  array   Group information
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
     * Gets the group data for grid
     *
     * @access  public
     * @param   int     $offset  Start offset of the result boundaries 
     * @return  string  XHTML grid data
     */
    function GetGroupsGrid($offset)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminHTML');

        return $gadget->GetGroupsGrid($offset);
    }

    /**
     * Gets number of groups
     *
     * @access  public
     * @return  mixed   Number of groups or Jaws_Error
     */
    function GetGroupsCount()
    {
        $this->gadget->CheckPermission('ManageGroups');
        return $this->_Model->GetGroupsCount();
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   string  $title      Title of the group
     * @param   string  $fast_url   Shortcut keyword to link to the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $visible    Visibility status of the group
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $res = $this->_Model->InsertGroup($title, $fast_url, $meta_keys, $meta_desc, $visible);
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
     * @param   int     $id         Group ID
     * @param   string  $title      Title of the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   string  $fast_url   Shortcut keyword to link to the group
     * @param   bool    $visible    Visibility status of the group
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($id, $title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $res = $this->_Model->UpdateGroup($id, $title, $fast_url, $meta_keys, $meta_desc, $visible == 'true');
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
     * @param   int     $id  group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($id)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $res = $this->_Model->DeleteGroup($id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_NOTICE_GROUP_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}