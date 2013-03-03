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
 * @category   GadgetModel
 * @package    Comments
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model extends Jaws_Gadget_Model
{
    /**
     * Get last ID of inserted comment (by some params to prevent duplicated entries)
     *
     * @access  private
     * @param   string  $createtime  Create time of the last ID
     * @param   string  $messageKey  MD5 of the message
     * @return  int     Last ID
     */
    function GetLastCommentID($createtime, $messageKey)
    {
        $params                = array();
        $params['createtime']  = $createtime;
        $params['message_key'] = $messageKey;

        $sql = '
            SELECT [id] FROM [[comments]]
            WHERE
                [createtime] = {createtime}
              AND
                [msg_key] = {message_key}';

        $id = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($id)) {
            return false;
        }

        return $id;
    }

    /**
     * Message is unique? Is it not duplicated?
     *
     * @access  public
     * @param   string   $md5     Message key in MD5
     * @return  bool    Exists (true) or Not Exists (false)
     */
    function IsMessageDuplicated($md5)
    {
        $params = array();
        $params['md5']    = $md5;

        $sql = '
            SELECT COUNT([id])
            FROM [[comments]]
            WHERE [msg_key] = {md5}';

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
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
     * @return  int     Comment id or Jaws_Error on any error
     * @access  public
     */
    function NewComment($gadget, $gadgetId, $action, $name, $email, $url,
                        $message, $ip, $permalink, $status = COMMENT_STATUS_APPROVED)
    {
        if (!in_array($status, array(COMMENT_STATUS_APPROVED, COMMENT_STATUS_WAITING, COMMENT_STATUS_SPAM))) {
            $status = COMMENT_STATUS_SPAM;
        }

        $message_key = md5($message);
        if ($this->gadget->GetRegistry('allow_duplicate', 'Policy') == 'no') {
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

        $sql = '
            INSERT INTO [[comments]]
               ( [reference], [action], [gadget], [user], [name], [email], [url],
               [ip], [msg_txt], [status], [msg_key], [createtime])
            VALUES
               ( {gadgetId}, {action}, {gadget}, {user}, {name}, {email}, {url},
               {ip}, {msg_txt}, {status}, {msg_key}, {now})';

        $params = array();
        $params['gadgetId'] = $gadgetId;
        $params['action']   = $action;
        $params['gadget']   = $gadget;
        $params['name']     = $name;
        $params['email']    = $email;
        $params['url']      = $url;
        $params['msg_txt']  = $message;
        $params['status']   = $status;
        $params['msg_key']  = $message_key;
        $params['ip']       = $ip;
        $params['user']     = $GLOBALS['app']->Session->GetAttribute('user');
        $params['now']      = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('COMMENTS_NAME'));
        }

        $lastId = $this->GetLastCommentID($params['now'], $params['msg_key']);

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
        $params = array();
        $params['id']     = $id;
        $params['gadget'] = $gadget;

        $sql = '
            SELECT
                [id],
                [reference],
                [action],
                [gadget],
                [reply],
                [replier],
                [name],
                [email],
                [url],
                [ip],
                [msg_txt],
                [status],
                [createtime]
            FROM [[comments]]
            WHERE
                [id] = {id}';

        if (!empty($gadget)) {
            $sql .= ' AND [gadget] = {gadget}';
        }

        return $GLOBALS['db']->queryRow($sql, $params);
    }

    /**
     * Gets a list of comments that match a thread of comments and a gadget reference ID
     *
     * @param   string  $gadget   Gadget's name
     * @param   int     $gadgetId Gadget's reference id.
     *                            It can be the ID of a blog entry, the ID of a
     *                            photo in Phoo, etc. This needs to be a reference
     *                            to find the comments releated to a specific record
     *                            in a gadget.
     * @param   string $action
     * @param   bool   $getApproved    If true get comments that are approved (optional, default true);
     * @param   bool   $getWaiting     If true get comments that are waiting for moderation (optional, default false);
     * @param   bool   $getSpam    If true get comments that are marked as spam (optional, default false);
     * @param   bool   $getAllCurrentUser If true get all the comments for the current user (based on user cookie)
     * @return  array  Returns an array with data of a list of comments or Jaws_Error on error
     * @access  public
     */
    function GetComments($gadget, $gadgetId, $action, $getApproved = true, $getWaiting = false, $getSpam = false, $getAllCurrentUser = false)
    {
        if (!$getApproved && !$getWaiting && !$getSpam) return array();

        $params = array();
        $params['gadgetId'] = $gadgetId;
        $params['action']   = $action;
        $params['gadget']   = $gadget;

        $sql = '
            SELECT
                [id],
                [reference],
                [gadget],
                [reply],
                [replier],
                [name],
                [email],
                [url],
                [ip],
                [msg_txt],
                [status],
                [createtime]
            FROM [[comments]]
            WHERE
                [reference] = {gadgetId}
                AND [action] = {action}';

        $sql .= ' AND [gadget] = {gadget} AND (';
        if ($getApproved) $sql .= ' [status] = ' . COMMENT_STATUS_APPROVED . ' OR ';
        if ($getWaiting)  $sql .= ' [status] = ' . COMMENT_STATUS_WAITING . ' OR ';
        if ($getSpam)     $sql .= ' [status] = ' . COMMENT_STATUS_SPAM . ' OR ';
        $sql = substr($sql, 0, -3);
        if ($getAllCurrentUser) {
            $params['visitor_name'] = $GLOBALS['app']->Session->GetCookie('visitor_name');
            $params['visitor_email'] = $GLOBALS['app']->Session->GetCookie('visitor_email');
            if ($params['visitor_name'] && $params['visitor_email']) {
                $sql .= ' OR ( ([name] = {visitor_name}) AND ([email] = {visitor_email}) ) ';
            }
        }
        $sql .= ') ORDER BY [createtime] ASC';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_COMMENTS'), _t('COMMENTS_NAME'));
        }

        return $result;
    }

    /**
     * Gets a list of old comments.
     *
     * @param   string  $gadget   Gadget's name
     * @param   int     $limit   How many comments
     * @param   bool   $getApproved    If true get comments that are approved (optional, default true);
     * @param   bool   $getWaiting     If true get comments that are waiting for moderation (optional, default false);
     * @param   bool   $getSpam    If true get comments that are marked as spam (optional, default false);
     * @return  array   Returns an array with data of a list of last comments or Jaws_Error on error
     * @access  public
     */
    function GetRecentComments($gadget, $limit, $getApproved = true, $getWaiting = false, $getSpam = false)
    {
        $params = array();
        $params['gadget'] = $gadget;

        $sql = '
            SELECT
                [id],
                [reference],
                [action],
                [gadget],
                [name],
                [email],
                [url],
                [ip],
                [msg_txt],
                [status],
                [createtime]
            FROM [[comments]]';

        if(empty($gadget)) {
            $sql .= ' WHERE (';
        } else {
            $sql .= ' WHERE [gadget] = {gadget} AND (';
        }

        if ($getApproved) $sql .= ' [status] = ' . COMMENT_STATUS_APPROVED . ' OR ';
        if ($getWaiting)  $sql .= ' [status] = ' . COMMENT_STATUS_WAITING . ' OR ';
        if ($getSpam)     $sql .= ' [status] = ' . COMMENT_STATUS_SPAM . ' OR ';
        $sql = substr($sql, 0, -3);
        $sql .= ') ORDER BY [createtime] DESC';

        $result = $GLOBALS['db']->setLimit($limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_RECENT_COMMENTS'), _t('COMMENTS_NAME'));
        }

        $rows = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_RECENT_COMMENTS'), _t('COMMENTS_NAME'));
        }

        return $rows;
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
            if ($GLOBALS['app']->Registry->Get('default_status', $gadget, JAWS_COMPONENT_GADGET) == COMMENT_STATUS_WAITING) {
                $status = COMMENT_STATUS_WAITING;
            } else {
                $status = COMMENT_STATUS_APPROVED;
            }
        }

        $params = array();
        $params['gadget'] = $gadget;
        $params['status'] = $status;

        $sql = '
            SELECT
              COUNT([id]) AS total
            FROM [[comments]]
            WHERE
                [gadget] = {gadget}
            ';
        if (!empty($status)) {
            $sql.= 'AND [status] = {status}';
        }

        $howMany = $GLOBALS['db']->queryOne($sql, $params);
        return Jaws_Error::IsError($howMany) ? 0 : $howMany;
    }

    /**
     * Counts how many comments are with a given filter
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $gadget     Gadget's name
     * @param   string  $filterMode Which mode should be used to filter
     * @param   string  $filterData Data that will be used in the filter
     * @param   int     $status     Spam status (approved=1, waiting=2, spam=3)
     * @return  int     Returns how many comments exists with a given filter
     */
    function HowManyFilteredComments($gadget, $filterMode, $filterData, $status)
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

        $howmany = $commentsTable->getOne();
        return $howmany;
    }

}