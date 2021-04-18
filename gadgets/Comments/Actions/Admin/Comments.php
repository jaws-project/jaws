<?php
/**
 * Comments Core Gadget
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Comments extends Comments_Actions_Admin_Default
{
    /**
     * Show comments list
     *
     * @access  public
     * @param   string $req_gadget  Gadget name
     * @param   string $menubar     Menubar
     * @return  string XHTML template content
     */
    function Comments($req_gadget = '', $menubar = '')
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmCommentDelete', _t('COMMENTS_CONFIRM_DELETE'));

        $this->gadget->define('LANGUAGE', array(
            'gadget'=> Jaws::t('GADGETS'),
            'comment'=> _t('COMMENTS_COMMENT'),
            'username'=> Jaws::t('USERNAME'),
            'time'=> Jaws::t('CREATED'),
            'status'=> Jaws::t('STATUS'),
            'mark_as_approved'=> _t('COMMENTS_MARK_AS_APPROVED'),
            'mark_as_waiting'=> _t('COMMENTS_MARK_AS_WAITING'),
            'mark_as_spam'=> _t('COMMENTS_MARK_AS_SPAM'),
            'mark_as_private'=> _t('COMMENTS_MARK_AS_PRIVATE'),
            'edit'=> Jaws::t('EDIT'),
            'delete'=> Jaws::t('DELETE')
        ));

        $gadgets = $this->gadget->model->load()->recommendedfor();
        $gadgetsList = array(array('name' => 'Comments', 'title' => _t('COMMENTS_TITLE')));
        if (!Jaws_Error::IsError($gadgets) && count($gadgets) > 0) {
            foreach ($gadgets as $gadget) {
                $gadgetsList[] = array('name' => $gadget, 'title' => _t(strtoupper($gadget . '_TITLE')));
            }
        }

        $statusItems = array(
            Comments_Info::COMMENTS_STATUS_APPROVED => _t('COMMENTS_STATUS_APPROVED'),
            Comments_Info::COMMENTS_STATUS_WAITING => _t('COMMENTS_STATUS_WAITING'),
            Comments_Info::COMMENTS_STATUS_SPAM => _t('COMMENTS_STATUS_SPAM'),
            Comments_Info::COMMENTS_STATUS_PRIVATE => _t('COMMENTS_STATUS_PRIVATE'),
        );
        $this->gadget->define('gadgetList', array_column($gadgetsList, 'title', 'name'));
        $this->gadget->define('statusItems', $statusItems);
        $this->gadget->define('status', array(
            'approve' => Comments_Info::COMMENTS_STATUS_APPROVED,
            'waiting' => Comments_Info::COMMENTS_STATUS_WAITING,
            'spam' => Comments_Info::COMMENTS_STATUS_SPAM,
            'private' => Comments_Info::COMMENTS_STATUS_PRIVATE,
        ));

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Comments') : $menubar;
        $assigns['gadgets'] = $gadgetsList;
        $assigns['statusItems'] = $statusItems;
        return $this->gadget->template->xLoadAdmin('Comments.html')->render($assigns);
    }

    /**
     * Return list of Comments data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetComments()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $filters = $post['filters'];

        $model = $this->gadget->model->load('Comments');
        $comments = $model->GetComments($filters['gadget'], '', '', $filters['term'], $filters['status'], 15, $post['offset'], $post['limit']);
        if (Jaws_Error::IsError($comments)) {
            return $this->gadget->session->response($comments->GetMessage(), RESPONSE_ERROR);
        }

        $commentsCount = $model->GetCommentsCount($filters['gadget'], '', '', $filters['term'], $filters['status']);
        if (Jaws_Error::IsError($commentsCount)) {
            return $this->gadget->session->response($commentsCount->GetMessage(), RESPONSE_ERROR);
        }

        if ($commentsCount > 0) {
            $objDate = Jaws_Date::getInstance();
            foreach ($comments as &$comment) {

                $comment['msg_abbr'] = Jaws_UTF8::strlen($comment['msg_txt']) > 25 ?
                    (Jaws_UTF8::substr($comment['msg_txt'], 0, 22). '...') : $comment['msg_txt'];

                $comment['insert_time'] = $objDate->Format($comment['insert_time']);
            }
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $commentsCount,
                'records' => $comments
            )
        );
    }

    /**
     * Get information of a Comment
     *
     * @access  public
     * @return  array   Comment info array
     */
    function GetComment()
    {
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $comment = $this->gadget->model->load('Comments')->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            return $this->gadget->session->response($comment->GetMessage(), RESPONSE_ERROR);
        }

        $date = Jaws_Date::getInstance();
        $comment['insert_time'] = $date->Format($comment['insert_time'], 'Y-m-d H:i:s');

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $comment
        );
    }

    /**
     * Update comment information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateComment()
    {
        $this->gadget->CheckPermission('ManageComments');
//        @list($gadget, $id, $name, $email, $url, $message, $reply, $status, $sendEmail) = $this->gadget->request->fetchAll('post');

        $post = $this->gadget->request->fetch(array('id:integer', 'data:array'), 'post');
        $data = $post['data'];

        // TODO: Fill permalink In New Versions, Please!!
        $res = $this->gadget->model->loadAdmin('Comments')->UpdateComment(
            $data['gadget'], $post['id'], $data['name'], $data['email'],
            $data['url'], $data['msg_txt'], $data['reply'], '', $data['status']
        );
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        }

        if (!empty($data['reply']) && !empty($data['email']) && $data['send_email']) {
            $res = $this->gadget->action->load('Comments')->EmailReply(
                $data['email'],
                $data['msg_txt'],
                $data['reply'],
                $this->app->session->user->nickname
            );
            if (Jaws_Error::IsError($res)) {
                return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
            }
        }

        return $this->gadget->session->response(
            _t('COMMENTS_COMMENT_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Does a massive delete on comments
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        $ids = $this->gadget->request->fetch('ids:array', 'post');
        $res = $this->gadget->model->loadAdmin('Comments')->DeleteMassiveComment($ids);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('COMMENTS_COMMENT_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MarkComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        $post = $this->gadget->request->fetch(array('ids:array', 'status'), 'post');
        $cModel = $this->gadget->model->loadAdmin('Comments');
        $res = $cModel->MarkAs($post['ids'], $post['status']);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('COMMENTS_COMMENT_MARKED'),
            RESPONSE_NOTICE
        );
    }

}