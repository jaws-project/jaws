<?php
/* Filter modes */
define('COMMENT_FILTERBY_REFERENCE', 'reference');
define('COMMENT_FILTERBY_NAME',      'name');
define('COMMENT_FILTERBY_EMAIL',     'email');
define('COMMENT_FILTERBY_URL',       'url');
define('COMMENT_FILTERBY_IP',        'ip');
define('COMMENT_FILTERBY_MESSAGE',   'message');
define('COMMENT_FILTERBY_STATUS',    'status');
define('COMMENT_FILTERBY_VARIOUS',   'various');
define('COMMENT_STATUS_APPROVED',    1);
define('COMMENT_STATUS_WAITING',     2);
define('COMMENT_STATUS_SPAM',        3);

/**
 * Comments Gadget
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model extends Jaws_Gadget_Model
{
    /**
     * Message is unique? Is it not duplicated?
     *
     * @access  public
     * @param   string   $md5     Message key in MD5
     * @return  bool    Exists (true) or Not Exists (false)
     */
    function IsMessageDuplicated($md5)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $howmany = $commentsTable->select('count([id]):integer')->where('msg_key', $md5)->getOne();

        ///FIXME check for errors
        return ($howmany == '0') ? false : true;
    }

    /**
     * Adds a new comment
     *
     * @param   string  $gadget   Gadget's name
     * @param   int     $gadgetId  Gadget's reference id.
     *                             It can be the ID of a blog entry, the ID of a
     *                             photo in Phoo, etc. This needs to be a reference
     *                             to find the comments related to a specific record
     *                             in a gadget.
     * @param   string  $action
     * @param   string  $name      Author's name
     * @param   string  $email     Author's email
     * @param   string  $url       Author's url
     * @param   string  $message   Author's message
     * @param   string  $ip        Author's IP
     * @param   string  $permalink Permanent link to resource
     * @param   int     $status
     * @return  int     Comment status or Jaws_Error on any error
     * @access  public
     */
    function NewComment($gadget, $gadgetId, $action, $name, $email, $url, $message,
                        $ip, $permalink, $status = COMMENT_STATUS_APPROVED)
    {
        if (!in_array((int)$status, array(COMMENT_STATUS_APPROVED, COMMENT_STATUS_WAITING, COMMENT_STATUS_SPAM))) {
            $status = COMMENT_STATUS_SPAM;
        }

        $message_key = md5($message);
        if ($this->gadget->registry->fetch('allow_duplicate') == 'no') {
            if ($this->IsMessageDuplicated($message_key)) {
                return new Jaws_Error(_t('GLOBAL_SPAM_POSSIBLE_DUPLICATE_MESSAGE'), _t('COMMENTS_NAME'));
            }
        }

        // Validate website url
        if (!preg_match('$^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?$i', $url)) {
            $url = '';
        }

        // Comment Status...
        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->IsSpam($permalink, $gadget, $name, $email, $url, $message)) {
            $status = COMMENT_STATUS_SPAM;
        }

        $cData = array();
        $cData['reference']     = $gadgetId;
        $cData['action']        = $action;
        $cData['gadget']        = $gadget;
        $cData['name']          = $name;
        $cData['email']         = $email;
        $cData['url']           = $url;
        $cData['msg_txt']       = $message;
        $cData['status']        = $status;
        $cData['msg_key']       = $message_key;
        $cData['ip']            = $ip;
        $cData['user']          = $GLOBALS['app']->Session->GetAttribute('user');
        $cData['createtime']    = $GLOBALS['db']->Date();

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $result = $commentsTable->insert($cData)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('COMMENTS_NAME'));
        }

        return $status;
    }

    /**
     * Gets a comment
     *
     * @param   int     $id     Comment's ID
     * @param   string  $gadget Gadget name
     * @return  array   Returns an array with comment data or Jaws_Error on error
     * @access  public
     */
    function GetComment($id, $gadget = '')
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select(
            'id:integer', 'reference:integer', 'action', 'gadget', 'reply', 'replier',
            'name', 'email', 'url', 'ip', 'msg_txt', 'status', 'createtime'
        );
        $commentsTable->where('id', $id);

        if (!empty($gadget)) {
            $commentsTable->and()->where('gadget', $gadget);
        }

        $result = $commentsTable->getRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('COMMENTS_COMMENT_ERROR_GETTING_COMMENT'), _t('COMMENTS_NAME'));
        }
        return $result;
    }

    /**
     * Gets a list of comments that match a thread of comments and a gadget reference ID
     *
     * @param   string  $gadget   Gadget's name
     * @param   int     $limit    How many comments
     * @param   int     $gadgetId Gadget's reference id.
     *                            It can be the ID of a blog entry, the ID of a
     *                            photo in Phoo, etc. This needs to be a reference
     *                            to find the comments releated to a specific record
     *                            in a gadget.
     * @param   string  $action
     * @param   array   $status   Array of comment status (approved=1, waiting=2, spam=3)
     * @param   bool    $getAllCurrentUser If true get all the comments for the current user (based on user cookie)
     * @param   int     $offset   Offset of data array
     * @param   int     $orderBy  The column index which the result must be sorted by
     * @return  array  Returns an array with data of a list of comments or Jaws_Error on error
     * @access  public
     */
    function GetComments($gadget, $limit, $gadgetId = null, $action = null , $status = array(), $getAllCurrentUser = false,
                         $offset = null, $orderBy = 0)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select(
            'comments.id:integer', 'reference:integer', 'user', 'action', 'gadget', 'reply', 'replier',
            'name', 'comments.email', 'comments.url', 'ip', 'msg_txt', 'comments.status:integer', 'createtime',
            'users.username', 'users.nickname', 'users.email as user_email', 'users.avatar',
            'users.registered_date as user_registered_date', 'users.url as user_url',
            'replier.nickname as replier_nickname','replier.username as replier_username'
        );

        $commentsTable->join('users', 'users.id', 'comments.user', 'left');
        $commentsTable->join('users as replier', 'replier.id', 'comments.replier', 'left');

        $commentsTable->where('gadget', $gadget);

        if(!empty($gadgetId)) {
            $commentsTable->and()->where('reference', $gadgetId);
        }
        if(!empty($action)) {
            $commentsTable->and()->where('action', $action);
        }
        if (count($status) > 0) {
            $commentsTable->and()->where('comments.status', $status, 'in');
        }

        if ($getAllCurrentUser) {
            $visitor_name = $GLOBALS['app']->Session->GetCookie('visitor_name');
            $visitor_email = $GLOBALS['app']->Session->GetCookie('visitor_email');
            if ($visitor_name && $visitor_email) {
                $commentsTable->and()->openWhere('name', $visitor_name)->and()->closeWhere('comments.email', $visitor_email);
            }
        }

        $orders = array(
            'createtime ASC',
            'createtime DESC',
        );
        $orderBy = (int)$orderBy;
        $orderBy = $orders[($orderBy > 1)? 1 : $orderBy];
        $commentsTable->orderBy($orderBy);
        $commentsTable->limit($limit, $offset);

        $result = $commentsTable->getAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_COMMENTS'), _t('COMMENTS_NAME'));
        }

        return $result;
    }

    /**
     * Return the total number of comments
     *
     * @access  public
     * @param   string  $gadget   Gadget's name
     * @param   string  $status comment status (approved=1, waiting=2, spam=3)
     * @return  int     Number of comments
     */
    function TotalOfComments($gadget, $status = '')
    {
        if (!in_array($status, array('', 1, 2, 3))) {
            if ($GLOBALS['app']->Registry->fetch('default_status', $gadget) == COMMENT_STATUS_WAITING) {
                $status = COMMENT_STATUS_WAITING;
            } else {
                $status = COMMENT_STATUS_APPROVED;
            }
        }

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select('count([id]):integer')->where('gadget', $gadget);
        if (!empty($status)) {
            $commentsTable->and()->where('status', $status);
        }
        $howMany = $commentsTable->getOne();

        return Jaws_Error::IsError($howMany) ? 0 : $howMany;
    }

    /**
     * Counts how many comments are with a given filter
     *
     * @access  public
     * @param   string  $gadget     Gadget's name
     * @param   string  $action
     * @param   string  $reference
     * @return  int     Returns how many comments exists with a given filter
     */
    function HowManyComments($gadget, $action, $reference)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select('count(id) as howmany:integer');
        $commentsTable->where('gadget', $gadget)->and()->where('action', $action);
        $commentsTable->and()->where('reference', $reference);
        return $commentsTable->getOne();
    }

    /**
     * Counts how many comments are with a given filter
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $gadget     Gadget's name
     * @param   string  $filterData Data that will be used in the filter
     * @param   int     $status     Spam status (approved=1, waiting=2, spam=3)
     * @return  int     Returns how many comments exists with a given filter
     */
    function HowManyFilteredComments($gadget, $filterData, $status)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select('count(id) as howmany:integer');

        if (!empty($gadget)) {
            $commentsTable->where('gadget', $gadget);
        }

        if (in_array($status, array(1, 2, 3))) {
            $commentsTable->and()->where('status', $status);
        }

        if (!empty($filterData)) {
            $commentsTable->and()->openWhere('reference', $filterData);
            $commentsTable->or()->where('name', '%'.$filterData.'%', 'like');
            $commentsTable->or()->where('email', '%'.$filterData.'%', 'like');
            $commentsTable->or()->where('url', '%'.$filterData.'%', 'like');
            $commentsTable->or()->closeWhere('msg_txt', '%'.$filterData.'%', 'like');
        }

        $howmany = $commentsTable->getOne();
        return $howmany;
    }

}