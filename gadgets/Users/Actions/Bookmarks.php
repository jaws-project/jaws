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

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));

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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(
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
            $GLOBALS['app']->Session->GetAttribute('user'),
            $data,
            $bookmarked
        );
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(
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
        $this->gadget->layout->setVariable('lbl_gadget', _t('GLOBAL_GADGET'));
        $this->gadget->layout->setVariable('lbl_action', _t('GLOBAL_ACTION'));
        $this->gadget->layout->setVariable('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->layout->setVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->layout->setVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->layout->setVariable('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('Bookmarks.html');
        $tpl->SetBlock('Bookmarks');
        $tpl->SetVariable('gadget_title', _t('USERS_ACTIONS_BOOKMARKS'));
        $this->SetTitle(_t('USERS_ACTIONS_BOOKMARKS'));

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Account'));
        $tpl->SetVariable(
            'submenubar',
            $this->SubMenuBar('Bookmarks', array('Account', 'Personal', 'Preferences', 'Bookmarks', 'Contact', 'Contacts'))
        );

        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_gadget', _t('ABUSEREPORTER_GADGET'));
        $tpl->SetVariable('lbl_action', _t('ABUSEREPORTER_ACTION'));
        $tpl->SetVariable('lbl_reference', _t('ABUSEREPORTER_REFERENCE'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_update', _t('GLOBAL_UPDATE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));

        // Gadgets Filter
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();
        array_unshift($gadgetList, array('name' => 0, 'title' => _t('GLOBAL_ALL')));
        foreach ($gadgetList as $gadget) {
            $tpl->SetBlock('Bookmarks/filterGadget');
            $tpl->SetVariable('title', $gadget['title']);
            $tpl->SetVariable('value', $gadget['name']);
            $tpl->ParseBlock('Bookmarks/filterGadget');
        }
        $tpl->SetVariable('lbl_filter_gadget', _t('GLOBAL_GADGETS'));
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
        $post = jaws()->request->fetch(
            array('filters:array', 'limit', 'offset', 'searchLogic', 'search:array', 'sort:array'),
            'post'
        );

        $bModel = $this->gadget->model->load('Bookmarks');
        $post['filters']['user'] = $GLOBALS['app']->Session->GetAttribute('user');
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
        $id = (int)jaws()->request->fetch('id', 'post');
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
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
        $id = (int)jaws()->request->fetch('id', 'post');

        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        $result = $this->gadget->model->load('Bookmarks')->DeleteBookmark($id, $currentUser);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                _t('USERS_BOOKMARK_DELETED'),
                RESPONSE_NOTICE
            );
        }
    }
}