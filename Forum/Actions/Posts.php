<?php
/**
 * Forum Gadget
 *
 * @category   Gadget
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forum_Actions_Posts extends ForumHTML
{

    /**
     * Show new post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function NewPost()
    {
        $request =& Jaws_Request::getInstance();
        $req = $request->get(array('message', 'tid'));
        if (empty($req['tid'])) {
            return false;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topic = $tModel->GetTopicInfo($req['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('NewPost.html');
        $tpl->SetBlock('newpost');

        $tpl->SetVariable('lbl_topic', $topic['subject']);
        $tpl->SetVariable('url_topic',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'Topic',
                                                          array('tid' => $topic['id']))
        );
        $tpl->SetVariable('title', _t('FORUM_POST_ADD_TITLE'));
        $tpl->SetVariable('separator', _t('FORUM_SEPARATOR'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('tid', $req['tid']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('newpost/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('newpost/response');
        }

        $tpl->SetVariable('lbl_message', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('newpost', _t('FORUM_POST_ADD_BUTTON'));
        $tpl->SetVariable('message', '');

        $tpl->ParseBlock('newpost');
        return $tpl->Get();
    }

    /**
     * Show edit post form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditPost()
    {
        $request =& Jaws_Request::getInstance();
        $req = $request->get(array('message', 'pid'));
        if (empty($req['pid'])) {
            return false;
        }

        $pModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Posts');
        $post = $pModel->GetPostInfo($req['pid']);
        if (Jaws_Error::IsError($post) || empty($post)) {
            return false;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topic = $tModel->GetTopicInfo($post['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('EditPost.html');
        $tpl->SetBlock('editpost');

        $tpl->SetVariable('lbl_topic', $topic['subject']);
        $tpl->SetVariable('url_topic',
                          $GLOBALS['app']->Map->GetURLFor('Forum',
                                                          'Topic',
                                                          array('tid' => $topic['id']))
        );
        $tpl->SetVariable('title', _t('FORUM_POST_ADD_TITLE'));
        $tpl->SetVariable('separator', _t('FORUM_SEPARATOR'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('tid', $post['tid']);
        $tpl->SetVariable('pid', $post['id']);

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Forum')) {
            $tpl->SetBlock('editpost/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('editpost/response');
        }

        $tpl->SetVariable('editpost', _t('FORUM_POST_EDIT_BUTTON'));
        $tpl->SetVariable('lbl_message', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('message', $post['message']);
        $tpl->SetVariable('lbl_update_reason', _t('FORUM_POST_UPDATE_REASON'));
        $tpl->SetVariable('update_reason', '');

        $tpl->ParseBlock('editpost');
        return $tpl->Get();
    }

    /**
     * Add/Edit a topic
     *
     * @access  public
     */
    function UpdatePost()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('tid', 'pid', 'message', 'update_reason'), 'post');

        $pModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost($GLOBALS['app']->Session->GetAttribute('user'), $post['tid'], $post['message']);
        } else {
            $result = $pModel->UpdatePost($post['pid'], $GLOBALS['app']->Session->GetAttribute('user'),
                                          $post['message'], $post['update_reason']);
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($apid->getMessage(),
                                                         'Topic');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('FORUM_POST_TOPIC_UPDATED'),
                                                         'Topic');
        }

        Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum',
                                                              'Topic',
                                                              array('tid' => $post['tid'])), true);
    }

}