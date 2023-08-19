<?php
/**
 * StaticPage AJAX API
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Deletes the page and all of its translations
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeletePage()
    {
        $this->gadget->CheckPermission('DeletePage');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Page');
        $model->DeletePage($id);
        return $this->gadget->session->pop();
    }

    /**
     * Deletes the page translation
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteTranslation()
    {
        $this->gadget->CheckPermission('DeletePage');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Translation');
        $model->DeleteTranslation($id);
        return $this->gadget->session->pop();
    }

    /**
     * Executes a batch delete on pages
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MassiveDelete()
    {
        $this->gadget->CheckPermission('DeletePage');
        $pages = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Page');
        $model->MassiveDelete($pages);
        return $this->gadget->session->pop();
    }
    
    /**
     * Updates gadget settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateSettings()
    {
        $this->gadget->CheckPermission('Properties');
        @list($defaultPage, $multiLang) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $model->UpdateSettings($defaultPage, $multiLang);
        return $this->gadget->session->pop();
    }

    /**
     * Parses passed text
     *
     * @access  public
     * @return  string  Parsed text
     */
    function ParseText()
    {
        $text = $this->gadget->request->fetch(0, 'post', false, array('filter' => 'strip_crlf'));
        return $this->gadget->plugin->parseAdmin($text);
    }

    /**
     * Gets total number of search results
     *
     * @access  public
     * @return  int     Total number of pages
     */
    function SizeOfSearch()
    {
        @list($group, $status, $search) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Page');
        $pages = $model->SearchPages($group, $status, $search, null);
        return count($pages);
    }

    /**
     * Searches for specified pages
     *
     * @access  public
     * @return  array   List of pages
     */
    function SearchPages()
    {
        @list($group, $status, $search, $orderBy, $limit) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Page');
        if (!is_numeric($limit)) {
            $limit = 0;
        }

        return $gadget->GetPages($group, $status, $search, $orderBy, $limit);
    }

    /**
     * This function performs an autodraft of the content and set
     * it's value to not published, which will later be changed when the
     * user clicks on save.
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AutoDraft()
    {
        @list($id, $group, $showtitle, $title, $content, $language,
            $fast_url, $meta_keys, $meta_desc, $tags,  $published
        ) = $this->gadget->request->fetchAll('post');
        $content = $this->gadget->request->fetch(4, 'post', false, array('filter' => 'strip_crlf'));
        $model = $this->gadget->model->loadAdmin('Page');

        if ($id == 'NEW') {
            $newid = $model->AddPage($title, $group, $showtitle, $content, $language,
                                   $fast_url, $meta_keys, $meta_desc, $tags, $published, true);
            $response['id'] = $newid;
            $response['message'] = $this::t('PAGE_AUTOUPDATED',
                                      date('H:i:s'),
                                      (int)$id,
                                      date('D, d'));
            $this->gadget->session->push(
                $this::t('PAGE_AUTOUPDATED', date('H:i:s'), (int)$id, date('D, d')),
                RESPONSE_NOTICE,
                $newid
            );
        } else {
            $model->UpdatePage($id, $group, $showtitle, $title, $content, $language,
                                      $fast_url, $meta_keys, $meta_desc, $published, true);
        }
        return $this->gadget->session->pop();
    }

    /**
     * Gets the group data
     *
     * @access  public
     * @return  array   Group information
     */
    function GetGroup()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Group');
        $group = $model->GetGroup($id);
        if (Jaws_Error::IsError($group)) {
            return false;
        }

        return $group;
    }

    /**
     * Gets the group data for grid
     *
     * @access  public
     * @return  string  XHTML grid data
     */
    function GetGroupsGrid()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($offset) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Group');

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
        $model = $this->gadget->model->loadAdmin('Group');
        return $model->GetGroupsCount();
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $fast_url, $meta_keys, $meta_desc, $visible) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $res = $model->InsertGroup($title, $fast_url, $meta_keys, $meta_desc, $visible);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('NOTICE_GROUP_CREATED'), RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
    }

    /**
     * Updates the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($id, $title, $fast_url, $meta_keys, $meta_desc, $visible) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $res = $model->UpdateGroup($id, $title, $fast_url, $meta_keys, $meta_desc, $visible == 'true');
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('NOTICE_GROUP_UPDATED'), RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $res = $model->DeleteGroup($id);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('NOTICE_GROUP_DELETED'), RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
    }

}