<?php
/**
 * Comments Model
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_Comments extends Jaws_Gadget_Model
{
    /**
     * Gets a comment
     *
     * @access  public
     * @param   int     $id Comment ID
     * @return  array   Returns an array with comment data or Jaws_Error on error
     */
    function GetComment($id)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $commentsTable->select(
            'comments_details.id:integer', 'gadget', 'action', 'reference:integer',
            'reference_title', 'reference_link', 'reply', 'replier',
            'name', 'email', 'url', 'uip', 'msg_txt', 'status', 'comments_details.insert_time'
        );
        $commentsTable->join('comments', 'comments.id', 'comments_details.cid');

        return $commentsTable->where('comments_details.id', $id)->fetchRow();
    }

    /**
     * Gets a list of comments that match a certain filter options
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Which mode should be used to filter
     * @param   int     $reference  Gadget reference id
     * @param   string  $term       Data that will be used in the filter
     * @param   int     $status     Comment status (approved=1, waiting=2, spam=3)
     * @param   int     $limit      How many comments
     * @param   int     $offset     Offset of data
     * @param   int     $orderBy    The column index which the result must be sorted by
     * @param   int     $user       User Id
     * @return  array   Returns an array with of filtered comments or Jaws_Error on error
     */
    function GetComments($gadget = '', $action = '', $reference = '', $term = '', $status = array(),
        $limit = 15, $offset = 0, $orderBy = 0, $user = null)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select(
            'comments_details.id:integer', 'gadget', 'action', 'reference:integer',
            'reference_title', 'reference_link', 'user', 'reply', 'replier',
            'comments_details.name', 'comments_details.email', 'comments_details.url',
            'uip', 'msg_txt', 'comments_details.status:integer', 'comments_details.insert_time',
            'users.username', 'users.nickname', 'users.email as user_email', 'users.avatar',
            'users.registered_date as user_registered_date', 'replier.nickname as replier_nickname',
            'replier.username as replier_username', 'comments.comments_count'
        );

        $commentsTable->join('comments_details', 'comments_details.cid', 'comments.id');
        $commentsTable->join('users', 'users.id', 'comments_details.user', 'left');
        $commentsTable->join('users as replier', 'replier.id', 'comments_details.replier', 'left');

        if (!empty($gadget)) {
            $commentsTable->where('comments.gadget', $gadget);
        }
        if(!empty($action)) {
            $commentsTable->and()->where('comments.action', $action);
        }
        if(!empty($reference)) {
            $commentsTable->and()->where('comments.reference', (int)$reference);
        }
        if(!empty($user)) {
            $commentsTable->and()->where('comments_details.user', (int)$user);
        }

        if (!empty($status)) {
            if (is_array($status)) {
                $commentsTable->and()->where('comments_details.status', $status, 'in');
            } else {
                $commentsTable->and()->where('comments_details.status', $status);
            }
        }

        if (!empty($term)) {
            $commentsTable->and()->openWhere('comments.reference_title', $term);
            $commentsTable->or()->where('comments_details.name', $term, 'like');
            $commentsTable->or()->where('comments_details.email', $term, 'like');
            $commentsTable->or()->where('comments_details.url', $term, 'like');
            $commentsTable->or()->closeWhere('comments_details.msg_txt', $term, 'like');
        }

        $orders = array(
            1 => 'comments_details.insert_time asc',
            2 => 'comments_details.insert_time desc',
        );
        $orderBy = isset($orders[$orderBy])? $orderBy : (int)$this->gadget->registry->fetch('order_type');
        $orderBy = $orders[$orderBy];
        return $commentsTable->limit($limit, $offset)->orderBy($orderBy)->fetchAll();
    }

    /**
     * Gets count of comments that match a certain filter options
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Which mode should be used to filter
     * @param   int     $reference  Gadget reference id
     * @param   string  $term       Data that will be used in the filter
     * @param   int     $status     Comment status (approved=1, waiting=2, spam=3)
     * @param   int     $user       User Id
     * @return  array   Returns count of filtered comments or Jaws_Error on error
     */
    function GetCommentsCount($gadget = '', $action = '', $reference = '', $term = '', $status = array(), $user = null)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $commentsTable->select('count(comments_details.id):integer');

        $commentsTable->join('comments', 'comments.id', 'comments_details.cid');
        if (!empty($gadget)) {
            $commentsTable->where('gadget', $gadget);
        }
        if(!empty($action)) {
            $commentsTable->and()->where('action', $action);
        }
        if(!empty($reference)) {
            $commentsTable->and()->where('reference', (int)$reference);
        }
        if(!empty($user)) {
            $commentsTable->and()->where('user', (int)$user);
        }

        if (!empty($status)) {
            if (is_array($status)) {
                $commentsTable->and()->where('status', $status, 'in');
            } else {
                $commentsTable->and()->where('status', $status);
            }
        }

        if (!empty($term)) {
            $commentsTable->and()->openWhere('reference_title', $term);
            $commentsTable->or()->where('name', $term, 'like');
            $commentsTable->or()->where('email', $term, 'like');
            $commentsTable->or()->where('url', $term, 'like');
            $commentsTable->or()->closeWhere('msg_txt', $term, 'like');
        }

        return $commentsTable->fetchOne();
    }

    /**
     * Gets list of most commented entries by users
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   int     $limit      How many entries
     * @param   int     $offset     Offset of data
     * @return  array   Returns an array of most commented entries or Jaws_Error on error
     */
    function MostCommented($gadget = '', $limit = 10, $offset = 0)
    {
        $objORM = Jaws_ORM::getInstance()->table('comments')->select(
            'gadget', 'action', 'reference:integer',
            'reference_title', 'reference_link', 'comments_count:integer'
        );
        if (!empty($gadget)) {
            $objORM->where('gadget', $gadget);
        }

        return $objORM->limit($limit, $offset)->orderBy('comments_count desc')->fetchAll();
    }

}