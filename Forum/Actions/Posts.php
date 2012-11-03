<?php
/**
 * Forum Gadget
 *
 * @category    Gadget
 * @package     Forum
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
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
        $topic = $tModel->GetTopic($req['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

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
        $post = $pModel->GetPost($req['pid']);
        if (Jaws_Error::IsError($post) || empty($post)) {
            return false;
        }

        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topic = $tModel->GetTopic($post['tid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

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

        if ($topic['first_post_id'] == (int)$req['pid']) {
            $tpl->SetBlock('editpost/subject');
            $tpl->SetVariable('lbl_subject', _t('FORUM_TOPIC_SUBJECT'));
            $tpl->SetVariable('subject', $topic['subject']);
            $tpl->ParseBlock('editpost/subject');
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
     * Add/Edit a post
     *
     * @access  public
     */
    function UpdatePost()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('tid', 'pid', 'subject', 'message', 'update_reason'), 'post');

        $pModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Posts');
        if (empty($post['pid'])) {
            $result = $pModel->InsertPost($GLOBALS['app']->Session->GetAttribute('user'), $post['tid'], $post['message']);
        } else {
            $result = $pModel->UpdatePost(
                $post['pid'],
                $GLOBALS['app']->Session->GetAttribute('user'),
                $post['subject'],
                $post['message'],
                $post['update_reason']
            );
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

    /**
     * Delete a post or topic
     *
     * @access  public
     */
    function DeletePost()
    {
        $tpl = new Jaws_Template('gadgets/Forum/templates/');
        $tpl->Load('DeletePost.html', true);
        if (!$GLOBALS['app']->Session->Logged()) {
            //Add lang
            $tpl->SetBlock('not_allow');
            $tpl->SetVariable('msg', _t('FORUM_NOT_PERMISON_PLEASE_LOGIN'));
            $tpl->ParseBlock('not_allow');
            return $tpl->Get();
        }

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('pid', 'tid', 'step'));
        $pModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Posts');

        $postInfo = $pModel->GetPost($post['pid']);
        if (Jaws_Error::IsError($postInfo) || empty($postInfo)) {
            return false;
        }
        $tModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Topics');
        $topicInfo = $tModel->GetTopic($postInfo['tid']);
        if (!is_null($post['step']) && $post['step'] == 'delete') {
            if ($postInfo['id'] == $topicInfo['first_post_id']) {
                // Delete Topic And All Posts In this
                $tModel->DeleteTopic($topicInfo['id']);
                Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum', 'Topics', array('id' => $topicInfo['fid'])));
            } else {
                // Delete Post
                $pModel->DeletePost($postInfo['id']);
                Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum', 'Topic', array('tid' => $postInfo['tid'])));
            }
        } else if (!is_null($post['step']) && $post['step'] == 'cancel') {
            Jaws_Header::Location($GLOBALS['app']->Map->GetURLFor('Forum', 'Topic', array('tid' => $postInfo['tid'])));
        }

        $tpl->SetBlock('deletepost');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('pid',  $postInfo['id']);
        $tpl->SetVariable('tid', $topicInfo['id']);
        $tpl->SetVariable('subject', $topicInfo['subject']);
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Forum', 'Topic', array('tid' => $topicInfo['id'])));
        $tpl->SetVariable('title', _t('FORUM_DELETE_POST'));
        $tpl->SetVariable('separator', _t('FORUM_SEPARATOR'));

        // Message
        $tpl->SetVariable('delete_message', _t('FORUM_DELETE_POST_CONFIRM'));

        $date = $GLOBALS['app']->loadDate();
        $tpl->SetVariable('psted_date',   $date->Format($date->ToISO($postInfo['createtime'])));
        $tpl->SetVariable('posted_by',    _t('FORUM_POSTED_BY'));
        $tpl->SetVariable('user_name',    $postInfo['username']);
        $tpl->SetVariable('lbl_message',  _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('message',      $postInfo['message']);

        $tpl->SetVariable('delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('cancel', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('deletepost');
        return $tpl->Get();
    }

}