<?php
/**
 * Users Bookmarks actions 
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Bookmarks extends Jaws_Gadget_Action
{
    /**
     * Bookmark UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function BookmarkUI()
    {
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
     * Updates bookmark
     *
     * @access  public
     * @return  bool    True if successfully otherwise False
     */
    function UpdateBookmark()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(
            array('requested_gadget', 'requested_action', 'reference', 'bookmarked'),
            'post'
        );

        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $bModel = $this->gadget->model->load('Bookmarks');
        $result = $bModel->UpdateBookmark(
            $user,
            $post['requested_gadget'],
            $post['requested_action'],
            $post['reference'],
            $post['bookmarked']
        );

        return !Jaws_Error::IsError($result);
    }

    /**
     * Gets list of user's bookmarks
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Bookmarks()
    {
        $objUsers = jaws()->loadObject('Jaws_User', 'Users');
        if (!Jaws_Error::IsError($objUsers)) {
            $username = $this->gadget->request->fetch('user', 'get');
            $vUser = $objUsers->GetUser($username);
            if (!Jaws_Error::IsError($vUser)) {
            }
        }
        $user = $objUsers->GetUser($user, true, true, true);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        $tpl = $this->gadget->template->load('Bookmarks.html');
        $tpl->SetBlock('Bookmarks');
        $tpl->SetVariable('title', _t('USERS_ACTIONS_BOOKMARKS'));

        $bModel = $this->gadget->model->load('Bookmarks');
        $bookmarks = $bModel->GetBookmarks();
        if (!Jaws_Error::isError($bookmarks)) {
            foreach($bookmarks as $bookmark) {
                $tpl->SetBlock('Bookmarks/bookmark');
                $tpl->ParseBlock('Bookmarks/bookmark');
            }
        }

        $tpl->ParseBlock('Bookmarks');
        return $tpl->Get();
    }

}