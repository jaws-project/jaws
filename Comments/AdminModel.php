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
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class CommentsAdminModel extends CommentsModel
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Install listener for removing comments related to uninstalled gadget
        $GLOBALS['app']->Listener->NewListener($this->_Gadget, 'onUninstallGadget', 'DeleteCommentsOfGadget');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        return true;
    }

    /**
     * Updates a comment
     *
     * @param   string  $gadget  Gadget's name
     * @param   int     $id      Comment's ID
     * @param   string  $name    Author's name
     * @param   string  $email   Author's email
     * @param   string  $url     Author's url
     * qparam   string  $title   Author's title message
     * @param   string  $message Author's message
     * @param   string  $permalink Permanent link to resource
     * @param   string  $status  Comment status
     * @return  bool   True if sucess or Jaws_Error on any error
     * @access  public
     */
    function UpdateComment($gadget, $id, $name, $email, $url, $title, $message, $permalink, $status)
    {
        $sql = '
            UPDATE [[comments]] SET
                [name]    = {name},
                [email]   = {email},
                [url]     = {url},
                [msg_txt] = {message},
                [msg_key] = {message_key},
                [title]   = {title},
                [status]  = {status}
            WHERE
                [id] = {id}
              AND
                [gadget] = {gadget}';

        $params = array();
        $params['id']          = $id;
        $params['gadget']      = $gadget;
        $params['name']        = $name;
        $params['email']       = $email;
        $params['url']         = $url;
        $params['title']       = $title;
        $params['message']     = $message;
        $params['message_key'] = md5($message);
        $params['status']      = $status;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_UPDATED'), _t('COMMENTS_NAME'));
        }

        $GLOBALS['app']->Registry->LoadFile('Policy');
        if ($this->GetRegistry('filter', 'Policy') != 'DISABLED') {
            require_once JAWS_PATH . 'gadgets/Policy/SpamFilter.php';
            $filter = new SpamFilter();
            $origComment = $this->GetComment($gadget, $id);
            if (($origComment['status'] == COMMENT_STATUS_SPAM) &&
                ($status == COMMENT_STATUS_APPROVED)) {
                $filter->SubmitHam($permalink, $gadget, $name, $email, $url, $message);
            }
            if (($origComment['status'] != COMMENT_STATUS_SPAM) &&
                ($status == COMMENT_STATUS_SPAM)) {
                $filter->SubmitSpam($permalink, $gadget, $name, $email, $url, $message);
            }
        }
        return true;
    }

    /**
     * Deletes a comment
     *
     * @param   string  $gadget Gadget's name
     * @param   int     $id     Comment's ID
     * @return  bool   True if sucess or Jaws_Error on any error
     * @access  public
     */
    function DeleteComment($gadget, $id)
    {
        $origComment = $this->GetComment($gadget, $id);
        if (Jaws_Error::IsError($id)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        $params             = array();
        $params['id']       = $id;
        $params['gadget']   = $gadget;
        $params['parent']   = $origComment['parent'];
        $params['gadgetId'] = $origComment['gadget_reference'];
        $origComment = null;

        $sql = 'DELETE FROM [[comments]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        // Up childs to deleted parent level...
        $sql = "UPDATE [[comments]]
                SET [parent] = {parent}
                WHERE [parent] = {id}";
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        // Count new childs...
        $sql = 'SELECT COUNT(*) AS replies
                FROM [[comments]]
                WHERE [parent] = {parent}';
        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }
        $params['replies'] = $row['replies'];

        // Update replies field in parent...
        $sql = '
             UPDATE [[comments]] SET
                 [replies] = {replies}
             WHERE
                 [id] = {parent}
               AND
                 [gadget_reference] = {gadgetId}
               AND
                [gadget] = {gadget}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_NOT_DELETED'), _t('COMMENTS_NAME'));
        }

        return true;
    }

    /**
     * Deletes all comment from a given gadget reference
     *
     * @param   string  $gadget Gadget's name
     * @param   int     $id     Gadget id reference
     * @return  bool   True if sucess or Jaws_Error on any error
     * @access  public
     */
    function DeleteCommentsByReference($gadget, $id)
    {
        $params = array();
        $params['id']       = $id;
        $params['gadget']   = $gadget;
        $sql = "DELETE FROM [[comments]]
                WHERE
                    [gadget_reference] = {id}
                AND
                    [gadget] = {gadget}";
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
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
     */
    function MarkAs($gadget, $ids, $status = 'spam')
    {
        if (count($ids) == 0) return;

        $list = implode(',', $ids);

        if (!in_array($status, array('approved', 'waiting', 'spam'))) {
            $status = COMMENT_STATUS_SPAM;
        }

        // Update status...
        $sql = "UPDATE [[comments]] SET [status] = {status} WHERE [id] IN (" . $list . ")";
        $GLOBALS['db']->query($sql, array('status' => $status));

        // FIXME: Update replies counter...
        if ($status == COMMENT_STATUS_SPAM) {
            $GLOBALS['app']->Registry->LoadFile('Policy');
            if ($this->GetRegistry('filter', 'Policy') != 'DISABLED') {
                // Submit spam...
                $sql     = "SELECT
                              [id],
                              [gadget_reference],
                              [gadget],
                              [parent],
                              [name],
                              [email],
                              [url],
                              [ip],
                              [title],
                              [msg_txt],
                              [replies],
                              [status],
                              [createtime]
                          FROM [[comments]]
                          WHERE [id] IN (" . $list . ")";
                $items = $GLOBALS['db']->queryAll($sql);
                require_once JAWS_PATH . 'gadgets/Policy/SpamFilter.php';
                $filter = new SpamFilter();
                foreach ($items as $i) {
                    if ($i['status'] != COMMENT_STATUS_SPAM) {
                        // FIXME Get $permalink
                        $permalink = '';
                        $filter->SubmitSpam($permalink, $gadget, $i['name'], $i['email'], $i['url'], $i['message']);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Deletes all comments of a certain gadget
     *
     * @access public
     * @param   string $gadget Gadget's name
     * @return mixed   True on success and Jaws_Error on failure
     */
    function DeleteCommentsOfGadget($gadget)
    {
        $params           = array();
        $params['gadget'] = $gadget;

        $sql = '
           DELETE FROM [[comments]]
           WHERE [gadget] = {gadget}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
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
     * @param   string  $status     Spam status (approved, waiting, spam)
     * @param   mixed   $limit      Limit of data (numeric/boolean: no limit)
     * @return  array   Returns an array with of filtered comments or Jaws_Error on error
     */
    function GetFilteredComments($gadget, $filterMode, $filterData, $status, $limit)
    {
        if (
            $filterMode != COMMENT_FILTERBY_REFERENCE &&
            $filterMode != COMMENT_FILTERBY_STATUS &&
            $filterMode != COMMENT_FILTERBY_IP
            ) {
            $filterData = '%'.$filterData.'%';
        }

        $params = array();
        $params['filterData'] = $filterData;
        $params['gadget'] = $gadget;

        $sql = '
            SELECT
                [id],
                [gadget_reference],
                [gadget],
                [parent],
                [name],
                [email],
                [url],
                [ip],
                [title],
                [msg_txt],
                [replies],
                [status],
                [createtime]
            FROM [[comments]]
            WHERE [gadget] = {gadget}';

        switch ($filterMode) {
        case COMMENT_FILTERBY_REFERENCE:
            $sql.= ' AND [gadget_reference] = {filterData}';
            break;
        case COMMENT_FILTERBY_NAME:
            $sql.= ' AND [name] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_EMAIL:
            $sql.= ' AND [email] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_URL:
            $sql.= ' AND [url] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_TITLE:
            $sql.= ' AND [title] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_IP:
            $sql.= ' AND [ip] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_MESSAGE:
            $sql.= ' AND [msg_txt] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_VARIOUS:
            $sql.= ' AND ([name] LIKE {filterData}';
            $sql.= ' OR [email] LIKE {filterData}';
            $sql.= ' OR [url] LIKE {filterData}';
            $sql.= ' OR [title] LIKE {filterData}';
            $sql.= ' OR [msg_txt] LIKE {filterData})';
            break;
        default:
            if (is_bool($limit)) {
                $limit = false;
                //By default we get the last 20 comments
                $result = $GLOBALS['db']->setLimit('20');
                if (Jaws_Error::IsError($result)) {
                    return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), _t('COMMENTS_NAME'));
                }
            }
            break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $params['status'] = $status;
            $sql.= ' AND [status] = {status}';
        }

        if (is_numeric($limit)) {
            $result = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), _t('COMMENTS_NAME'));
            }
        }
        $sql.= ' ORDER BY [createtime] DESC';

        $rows = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), _t('COMMENTS_NAME'));
        }

        return $rows;
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
     * @param   string  $status     Spam status (approved, waiting, spam)
     * @return  int     Returns how many comments exists with a given filter
     */
    function HowManyFilteredComments($gadget, $filterMode, $filterData, $status)
    {
        if (
            $filterMode != COMMENT_FILTERBY_REFERENCE &&
            $filterMode != COMMENT_FILTERBY_STATUS &&
            $filterMode != COMMENT_FILTERBY_IP
            ) {
            $filterData = '%'.$filterData.'%';
        }

        if (!in_array($status, array('approved', 'waiting', 'spam'))) {
            if ($GLOBALS['app']->Registry->Get('/gadget/' . $gadget . '/default_status') == COMMENT_STATUS_WAITING) {
                $status = COMMENT_STATUS_WAITING;
            } else {
                $status = COMMENT_STATUS_APPROVED;
            }
        }

        $params = array();
        $params['filterData'] = $filterData;
        $params['gadget']     = $gadget;
        $params['status']     = $status;

        $sql = '
            SELECT
                COUNT(*) AS howmany
            FROM [[comments]]
            WHERE [gadget] = {gadget}';

        switch ($filterMode) {
        case COMMENT_FILTERBY_REFERENCE:
            $sql.= ' AND [gadget_reference] = {filterData}';
            break;
        case COMMENT_FILTERBY_NAME:
            $sql.= ' AND [name] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_EMAIL:
            $sql.= ' AND [email] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_URL:
            $sql.= ' AND [url] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_TITLE:
            $sql.= ' AND [title] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_IP:
            $sql.= ' AND [ip] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_MESSAGE:
            $sql.= ' AND [msg_txt] LIKE {filterData}';
            break;
        case COMMENT_FILTERBY_VARIOUS:
            $sql.= ' AND ([name] LIKE {filterData}';
            $sql.= ' OR [email] LIKE {filterData}';
            $sql.= ' OR [url] LIKE {filterData}';
            $sql.= ' OR [title] LIKE {filterData}';
            $sql.= ' OR [msg_txt] LIKE {filterData})';
            break;
        }
        $sql.= ' AND [status] = {status}';

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), _t('COMMENTS_NAME'));
        }

        return $howmany;
    }

}