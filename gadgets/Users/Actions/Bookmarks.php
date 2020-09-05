<?php
/**
 * Users Bookmarks actions 
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Bookmarks extends Users_Actions_Default
{
    /**
     * Bookmark UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function BookmarkUI()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        $tpl = $this->gadget->template->load('Bookmark.html');
        $tpl->SetBlock('Bookmark');

        $post = $this->gadget->request->fetch(array('bookmark_gadget', 'bookmark_action', 'bookmark_reference'), 'post');
        $tpl->SetVariable('gadget', $post['bookmark_gadget']);
        $tpl->SetVariable('action', $post['bookmark_action']);
        $tpl->SetVariable('reference', $post['bookmark_reference']);

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));

        $tpl->ParseBlock('Bookmark');
        return $tpl->Get();
    }

    /**
     * Update user bookmark
     *
     * @access  public
     * @return  void
     */
    function UpdateBookmark()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(
            array('bookmark_gadget', 'bookmark_action', 'bookmark_reference', 'url', 'title', 'description', 'bookmarked'),
            'post'
        );

        $data = array();
        $data['url'] = $post['url'];
        $data['gadget'] = $post['bookmark_gadget'];
        $data['action'] = $post['bookmark_action'];
        $data['reference'] = $post['bookmark_reference'];
        $data['title'] = $post['title'];
        $data['description'] = $post['description'];

        $bookmarked = (bool)$post['bookmarked'];
        $result = $this->gadget->model->load('Bookmarks')->UpdateBookmark(
            $this->app->session->user->id,
            $data,
            $bookmarked
        );
        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response(
                _t('USERS_BOOKMARK_UPDATED'),
                RESPONSE_NOTICE,
                array('gadget' => $data['gadget'], 'action' => $data['action'], 'reference' => $data['reference'])
            );
        }
    }

    /**
     * Gets list of user's bookmarks
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Bookmarks()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_gadget', Jaws::t('GADGET'));
        $this->gadget->define('lbl_action', Jaws::t('ACTION'));
        $this->gadget->define('lbl_title', Jaws::t('TITLE'));
        $this->gadget->define('lbl_edit', Jaws::t('EDIT'));
        $this->gadget->define('lbl_delete', Jaws::t('DELETE'));
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('Bookmarks.html');
        $tpl->SetBlock('Bookmarks');
        $tpl->SetVariable('title', _t('USERS_ACTIONS_BOOKMARKS'));
        $this->SetTitle(_t('USERS_ACTIONS_BOOKMARKS'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $tpl->SetVariable('lbl_gadget', _t('ABUSEREPORTER_GADGET'));
        $tpl->SetVariable('lbl_action', _t('ABUSEREPORTER_ACTION'));
        $tpl->SetVariable('lbl_reference', _t('ABUSEREPORTER_REFERENCE'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('lbl_update', Jaws::t('UPDATE'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

        // Gadgets Filter
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();
        array_unshift($gadgetList, array('name' => 0, 'title' => Jaws::t('ALL')));
        foreach ($gadgetList as $gadget) {
            $tpl->SetBlock('Bookmarks/filterGadget');
            $tpl->SetVariable('title', $gadget['title']);
            $tpl->SetVariable('value', $gadget['name']);
            $tpl->ParseBlock('Bookmarks/filterGadget');
        }
        $tpl->SetVariable('lbl_filter_gadget', Jaws::t('GADGETS'));
        $tpl->SetVariable('lbl_filter_term', _t('USERS_USERS_SEARCH_TERM'));

        $tpl->ParseBlock('Bookmarks');
        return $tpl->Get();
    }

    /**
     * Get bookmarks list
     *
     * @access  public
     * @return  JSON
     */
    function GetBookmarks()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        $post = $this->gadget->request->fetch(
            array('filters:array', 'limit', 'offset', 'searchLogic', 'search:array', 'sort:array'),
            'post'
        );

        $bModel = $this->gadget->model->load('Bookmarks');
        $post['filters']['user'] = $this->app->session->user->id;
        $bookmarks = $bModel->GetBookmarks($post['filters'], $post['limit'], $post['offset']);
        $bookmarksCount = $bModel->GetBookmarksCount($post['filters']);

        return array(
            'status' => 'success',
            'total' => $bookmarksCount,
            'records' => $bookmarks
        );
    }

    /**
     * Get a bookmark info
     *
     * @access  public
     * @return  JSON
     */
    function GetBookmark()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $currentUser = $this->app->session->user->id;
        return $this->gadget->model->load('Bookmarks')->GetBookmark($id, $currentUser);
    }

    /**
     * Delete a bookmark
     *
     * @access  public
     * @return  void
     */
    function DeleteBookmark()
    {
        $this->gadget->CheckPermission('EditUserBookmarks');
        $id = (int)$this->gadget->request->fetch('id', 'post');

        $currentUser = $this->app->session->user->id;
        $result = $this->gadget->model->load('Bookmarks')->DeleteBookmark($id, $currentUser);
        if (Jaws_Error::isError($result)) {
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response(
                _t('USERS_BOOKMARK_DELETED'),
                RESPONSE_NOTICE
            );
        }
    }
}