<?php
require_once JAWS_PATH . 'gadgets/Comments/Model.php';
/**
 * Comments Gadget Admin
 *
 * @category   GadgetModel
 * @package    Comments
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_AdminModel extends Comments_Model
{
    /**
     * Updates a comment
     *
     * @param   string  $gadget  Gadget's name
     * @param   int     $id      Comment's ID
     * @param   string  $name    Author's name
     * @param   string  $email   Author's email
     * @param   string  $url     Author's url
     * @param   string  $message Author's message
     * @param   string  $permalink Permanent link to resource
     * @param   string  $status  Comment status
     * @return  bool   True if success or Jaws_Error on any error
     * @access  public
     */
    function UpdateComment($gadget, $id, $name, $email, $url, $message, $permalink, $status)
    {
        $cData = array();
        $cData['name']        = $name;
        $cData['email']       = $email;
        $cData['url']         = $url;
        $cData['msg_txt']     = $message;
        $cData['msg_key']     = md5($message);
        $cData['status']      = $status;

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $result = $commentsTable->update($cData)->where('id', $id)->and()->where('gadget', $gadget)->exec();

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_UPDATED'), _t('COMMENTS_NAME'));
        }

        $origComment = $this->GetComment($id, $gadget);
        if (($status == COMMENT_STATUS_SPAM ||$origComment['status'] == COMMENT_STATUS_SPAM) &&
            $origComment['status'] != $status)
        {
            $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
            if ($status == COMMENT_STATUS_SPAM) {
                $mPolicy->SubmitSpam($permalink, $gadget, $name, $email, $url, $message);
            } else {
                $mPolicy->SubmitHam($permalink, $gadget, $name, $email, $url, $message);
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Reply a comment
     *
     * @param   string  $gadget  Gadget's name
     * @param   int     $id      Comment's ID
     * @param   string  $reply
     * @return  bool   True if success or Jaws_Error on any error
     * @access  public
     */
    function ReplyComment($gadget, $id, $reply)
    {
        $cData['reply']    = $reply;
        $cData['replier']  = $GLOBALS['app']->Session->GetAttribute('user');

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $res = $commentsTable->update($cData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_UPDATED'), _t('COMMENTS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a comment
     *
     * @param   string  $gadget Gadget's name
     * @param   int     $id     Comment's ID
     * @return  bool    True if success or Jaws_Error on any error
     * @access  public
     */
    function DeleteComment($gadget, $id)
    {
        $commentTable = Jaws_ORM::getInstance()->table('comments');
        $res = $commentTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        return true;
    }

    /**
     * Deletes all comment from a given gadget reference
     *
     * @param   string  $gadget Gadget's name
     * @param   int     $id     Gadget id reference
     * @return  bool   True if success or Jaws_Error on any error
     * @access  public
     */
    function DeleteCommentsByReference($gadget, $id)
    {
        $commentTable = Jaws_ORM::getInstance()->table('comments');
        $res = $commentTable->delete()->where('reference', $id)->and()->where('gadget', $gadget)->exec();

        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }
    }

    /**
     * Mark as a different status several comments
     *
     * @access  public
     * @param   string $gadget  Gadget's name
     * @param   array   $ids     Id's of the comments to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  bool
     */
    function MarkAs($gadget, $ids, $status = 'spam')
    {
        if (count($ids) == 0) return;

        if (!in_array($status, array(1, 2, 3))) {
            $status = COMMENT_STATUS_SPAM;
        }

        // Update status...
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->update(array('status'=>$status))->where('id', $ids, 'in')->exec();

        if ($status == COMMENT_STATUS_SPAM) {
            $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
            // Submit spam...
            $commentsTable = Jaws_ORM::getInstance()->table('comments');
            $commentsTable->select(
                'id:integer', 'name', 'email', 'url', 'msg_txt', 'msg_txt', 'status:integer'
            );
            $items = $commentsTable->where('id', $ids, 'in')->getAll();

            foreach ($items as $i) {
                if ($i['status'] != COMMENT_STATUS_SPAM) {
                    // FIXME Get $permalink
                    $permalink = '';
                    $mPolicy->SubmitSpam($permalink, $gadget, $i['name'], $i['email'], $i['url'], $i['message']);
                }
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_MARKED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes all comments of a certain gadget
     *
     * @access  public
     * @param   string $gadget Gadget's name
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteCommentsOfGadget($gadget)
    {
        $res = Jaws_ORM::getInstance()->table('comments')->delete()->where('gadget', $gadget)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        return true;
    }

    /**
     * Gets a list of comments that match a certain filter.
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $gadget     Gadget's name
     * @param   string  $filterMode Which mode should be used to filter
     * @param   string  $filterData Data that will be used in the filter
     * @param   int     $status     comment status (approved=1, waiting=2, spam=3)
     * @param   mixed   $offset     Offset of data
     * @return  array   Returns an array with of filtered comments or Jaws_Error on error
     */
    function GetFilteredComments($gadget, $filterMode, $filterData, $status, $offset)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select(
            'id', 'reference', 'gadget', 'name','email','url',
            'ip', 'msg_txt', 'status', 'createtime'
        );

        if (!empty($gadget)) {
            $commentsTable->where('gadget', $gadget);
        }

        if (in_array($status, array(1, 2, 3))) {
            $commentsTable->and()->where('status', $status);
        }

        if (!empty($filterData)) {
            switch ($filterMode) {
                case COMMENT_FILTERBY_REFERENCE:
                    $commentsTable->and()->where('reference', $filterData);
                    break;
                case COMMENT_FILTERBY_NAME:
                    $commentsTable->and()->where('name', '%'.$filterData.'%', 'like');
                    break;
                case COMMENT_FILTERBY_EMAIL:
                    $commentsTable->and()->where('email', '%'.$filterData.'%', 'like');
                    break;
                case COMMENT_FILTERBY_URL:
                    $commentsTable->and()->where('url', '%'.$filterData.'%', 'like');
                    break;
                case COMMENT_FILTERBY_IP:
                    $commentsTable->and()->where('ip', '%'.$filterData.'%', 'like');
                    break;
                case COMMENT_FILTERBY_MESSAGE:
                    $commentsTable->and()->where('msg_txt', '%'.$filterData.'%', 'like');
                    break;
                case COMMENT_FILTERBY_VARIOUS:
                    $commentsTable->and()->openWhere('reference', $filterData);
                    $commentsTable->or()->where('name', '%'.$filterData.'%', 'like');
                    $commentsTable->or()->where('email', '%'.$filterData.'%', 'like');
                    $commentsTable->or()->where('url', '%'.$filterData.'%', 'like');
                    $commentsTable->or()->closeWhere('msg_txt', '%'.$filterData.'%', 'like');
                    break;
            }
        }

        $commentsTable->limit(10, $offset);
        $commentsTable->orderBy('createtime desc');
        $rows = $commentsTable->getAll();
        return $rows;
    }

    /**
     * Does a massive comment delete
     *
     * @access  public
     * @param   array   $ids  Ids of comments
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveCommentDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->DeleteComment(null, $id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('GLOBAL_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}