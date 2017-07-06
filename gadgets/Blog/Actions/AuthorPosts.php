<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_AuthorPosts extends Blog_Actions_Default
{
    /**
     * Generates and returns Author Page
     * 
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewAuthorPage()
    {
        $post = $this->gadget->request->fetch(array('id', 'page'), 'get');

        $page = $post['page'];
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $user = $post['id'];
        if (!isset($user) || empty($user)) {
            return false;
        }

        $whereArray = null;
        if (is_numeric($user)) {
            $whereArray = array(
                array('blog.user_id', $user, '=')
            );
        } else {
            $whereArray = array(
                array('users.username', $user, '=')
            );
        }

        $pModel = $this->gadget->model->load('Posts');
        $aModel = $this->gadget->model->load('AuthorPosts');
        $entries = $pModel->GetEntriesAsPage(null, $page, $whereArray);
        if (!Jaws_Error::IsError($entries) && !empty($entries)) {
            $tpl = $this->gadget->template->load('AuthorPosts.html');
            $tpl->SetBlock('view_author');

            // set author nickname
            $firstEntry = reset($entries);
            $this->SetTitle($firstEntry['nickname']);
            $tpl->SetVariable('title', $firstEntry['nickname']);

            $total  = $aModel->GetAuthorNumberOfPages($user);
            $limit  = $this->gadget->registry->fetch('last_entries_limit');
            $params = array('id'  => $user);
            $this->gadget->action->load('Navigation')->pagination(
                $tpl,
                $page,
                $limit,
                $total,
                'ViewAuthorPage',
                $params
            );

            foreach ($entries as $entry) {
                $this->ShowEntry($tpl, 'view_author', $entry);
            }

            $tpl->ParseBlock('view_author');
            return $tpl->Get();
        } else {
            return Jaws_HTTPError::Get(404);
        }
    }

}