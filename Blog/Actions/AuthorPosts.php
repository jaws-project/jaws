<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_AuthorPosts extends Blog_HTML
{
    /**
     * Generates and returns Author Page
     * 
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewAuthorPage()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'page'), 'get');

        $page = $post['page'];
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $user = $post['id'];
        if (!isset($user) || empty($user)) {
            return false;
        }

        $condition = null;
        if (is_numeric($user)) {
            $condition = ' AND [[blog]].[user_id] = {user}';
        } else {
            $condition = ' AND [[users]].[username] = {user}';
        }

        $bModel = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entries = $bModel->GetEntriesAsPage(null, $page, $condition, array('user' => $user));
        if (!Jaws_Error::IsError($entries) && !empty($entries)) {
            $tpl = new Jaws_Template('gadgets/Blog/templates/');
            $tpl->Load('ViewAuthor.html', true);
            $tpl->SetBlock('view_author');

            $title = $entries[key($entries)]['nickname'];
            $this->SetTitle($title);
            $tpl->SetVariable('title', $title);

            $total  = $bModel->GetAuthorNumberOfPages($user);
            $limit  = $this->gadget->GetRegistry('last_entries_limit');
            $params = array('id'  => $user);
            $tpl->SetVariable('navigation',
                              $this->GetNumberedPageNavigation($page, $limit, $total, 'ViewAuthorPage', $params));

            $res = '';
            $tpl->SetBlock('view_author/entry');
            $tplEntry = $tpl->GetRawBlockContent();
            foreach ($entries as $entry) {
                $res .= $this->ShowEntry($entry, true, true, $tplEntry);
            }
            $tpl->SetCurrentBlockContent($res);
            $tpl->ParseBlock('view_author/entry');

            $tpl->ParseBlock('view_author');
            return $tpl->Get();
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
    }

}