<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Comments extends Blog_HTML
{

    /**
     * Displays a given blog comments and a form for replying
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Reply()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'comment_id'), 'get');
        $postHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Post');
        return $postHTML->SingleView($post['id'], false, (int)$post['comment_id']);
    }

    /**
     * Displays a preview of the given blog comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        $request =& Jaws_Request::getInstance();
        $names = array(
            'name', 'email', 'url', 'title', 'comments', 'createtime',
            'ip_address', 'parent_id', 'parent'
        );
        $post = $request->get($names, 'post');
        $id   = (int)$post['parent_id'];
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Blog_Comment');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entry = $model->GetEntry($id, true);
        if (Jaws_Error::isError($entry)) {
            $GLOBALS['app']->Session->PushSimpleResponse($entry->getMessage(), 'Blog');
            Jaws_Header::Location($this->gadget->GetURLFor('DefaultAction'));
        }

        $postHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Post');
        $id = !empty($entry['fast_url']) ? $entry['fast_url'] : $entry['id'];
        return $postHTML->SingleView($id, true);
    }

    /**
     * Displays a preview of the given blog comment
     *
     * @access  public
     * @return  string XHTML template content
     */
    function ShowPreview()
    {
        $post = $GLOBALS['app']->Session->PopSimpleResponse('Blog_Comment', false);
        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Comment.html');
        $tpl->SetBlock('comment');

        $tpl->SetVariable('name',  $post['name']);
        $tpl->SetVariable('email', $post['email']);
        $tpl->SetVariable('url',   $post['url']);
        if (is_null($post['ip_address'])) {
            $post['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        $tpl->SetVariable('title', $post['title']);
        $tpl->SetVariable('comments', Jaws_String::AutoParagraph($post['comments']));
        if (!isset($post['createtime'])) {
            $date = $GLOBALS['app']->loadDate();
            $post['createtime'] = $date->Format(time());
        }
        $tpl->SetVariable('createtime', $post['createtime']);
        $tpl->SetVariable('level', 0);
        $tpl->SetVariable('status_message', '&nbsp;');
        $tpl->SetVariable('ip_address', $post['ip_address']);
        $tpl->SetVariable('avatar_source', 'images/unknown.png');
        $tpl->SetVariable('replies', '0');
        $tpl->SetVariable('commentname', 'comment_preview');

        $tpl->ParseBlock('comment');
        return $tpl->Get();
    }


}