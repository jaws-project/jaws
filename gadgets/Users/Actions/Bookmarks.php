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