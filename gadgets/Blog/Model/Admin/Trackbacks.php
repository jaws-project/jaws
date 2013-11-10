<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Admin_Trackbacks extends Jaws_Gadget_Model
{
    /**
     * Send a trackback to a site
     *
     * @access  public
     * @param   string  $title     Title of the Site
     * @param   string  $excerpt   The Excerpt
     * @param   string  $permalink The Permalink to send
     * @param   array   $to        Where to send the trackback
     */
    function SendTrackback($title, $excerpt, $permalink, $to)
    {
        $title = urlencode(stripslashes($title));
        $excerpt = urlencode(stripslashes($excerpt));
        $blog_name = urlencode(stripslashes($this->gadget->registry->fetch('site_name', 'Settings')));
        $permalink = urlencode($permalink);

        require_once PEAR_PATH. 'HTTP/Request.php';

        $options = array();
        $timeout = (int)$this->gadget->registry->fetch('connection_timeout', 'Settings');
        $options['timeout'] = $timeout;
        if ($this->gadget->registry->fetch('proxy_enabled', 'Settings') == 'true') {
            if ($this->gadget->registry->fetch('proxy_auth', 'Settings') == 'true') {
                $options['proxy_user'] = $this->gadget->registry->fetch('proxy_user', 'Settings');
                $options['proxy_pass'] = $this->gadget->registry->fetch('proxy_pass', 'Settings');
            }
            $options['proxy_host'] = $this->gadget->registry->fetch('proxy_host', 'Settings');
            $options['proxy_port'] = $this->gadget->registry->fetch('proxy_port', 'Settings');
        }

        $httpRequest = new HTTP_Request('', $options);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
        foreach ($to as $url) {
            $httpRequest->setURL($url);
            $httpRequest->addPostData('title',     $title);
            $httpRequest->addPostData('url',       $permalink);
            $httpRequest->addPostData('blog_name', $blog_name);
            $httpRequest->addPostData('excerpt',   $excerpt);
            $resRequest = $httpRequest->sendRequest();
            $httpRequest->clearPostData();
        }
    }

    /**
     * Mark as different status a trackback
     *
     * @access  public
     * @param   array   $ids     Id's of the trackbacks to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function MarkTrackbacksAs($ids, $status = 'spam')
    {
        if (count($ids) == 0 || empty($status)) {
            return true;
        }

        // Fix blog trackback counter...
        foreach ($ids as $id) {
            $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
            $result = $trackbackTable->update(array('status'=>$status))->where('id', $id)->exec();

            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_UPDATED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_TRACKBACK_MARKED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Does a massive trackback delete
     *
     * @access  public
     * @param   array   $ids  Ids of trackbacks
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveTrackbackDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->DeleteTrackback($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_TRACKBACK_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_TRACKBACK_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a trackback
     *
     * @access  public
     * @param   int     $id     Trackback's ID
     * @return  mixed   True if sucess or Jaws_Error on any error
     */
    function DeleteTrackback($id)
    {
        $result = Jaws_ORM::getInstance()->table('blog_trackback')->delete()->where('id', $id)->exec();

        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_TRACKBACKS_ERROR_NOT_DELETED'), 'CORE');
        }

        return true;
    }

    /**
     * Gets a list of trackbacks that match a certain filter.
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $filterMode     Which mode should be used to filter
     * @param   string  $filterData     Data that will be used in the filter
     * @param   string  $status         Spam status (approved, waiting, spam)
     * @param   mixed   $limit          Limit of data (numeric/boolean: no limit)
     * @return  mixed   Returns an array with of filtered trackbacks or Jaws_Error on error
     */
    function GetFilteredTrackbacks($filterMode, $filterData, $status, $limit)
    {
        if (
            $filterMode != 'postid' &&
            $filterMode != 'status' &&
            $filterMode != 'ip'
        ) {
            $filterData = '%'.$filterData.'%';
        }

        $table = Jaws_ORM::getInstance()->table('blog_trackback');
        $table->select(
            'id:integer', 'parent_id:integer', 'blog_name', 'url', 'title', 'ip', 'url', 'status', 'createtime'
        );

        switch ($filterMode) {
            case 'postid':
                $table->and()->where('parent_id', $filterData);
                break;
            case 'blog_name':
                $table->and()->where('blog_name', $filterData, 'like');
                break;
            case 'url':
                $table->and()->where('url', $filterData, 'like');
                break;
            case 'title':
                $table->and()->where('title', $filterData, 'like');
                break;
            case 'ip':
                $table->and()->where('ip', $filterData, 'like');
                break;
            case 'excerpt':
                $table->and()->where('excerpt', $filterData, 'like');
                break;
            case 'various':
                $table->and()->openWhere()->where('blog_name', $filterData, 'like')->or();
                $table->where('url', $filterData, 'like')->or();
                $table->where('title', $filterData, 'like')->or();
                $table->where('excerpt', $filterData, 'like')->closeWhere();
                break;
            default:
                if (is_bool($limit)) {
                    $limit = false;
                    //By default we get the last 20 comments
                    $table->limit(20);
                }
                break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $table->and()->where('status', $status);
        }

        if (is_numeric($limit)) {
            $table->limit(10, $limit);
        }

        $rows = $table->orderBy('createtime desc')->fetchAll();
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
        }

        return $rows;
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $filterby Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter   Filter data
     * @param   string  $status   Spam status (approved, waiting, spam)
     * @param   mixed   $limit    Data limit (numeric/boolean)
     * @return  array   Filtered Comments
     */
    function GetTrackbacksDataAsArray($filterby, $filter, $status, $limit)
    {
        $trackbacks = $this->GetFilteredTrackbacks($filterby, $filter, $status, $limit);
        if (Jaws_Error::IsError($trackbacks)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $data = array();
        foreach ($trackbacks as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            $newRow['blog_name']    = '<a href="'.Jaws_XSS::filter($row['url']).'">'.Jaws_XSS::filter($row['blog_name']).'</a>';;

            $url = BASE_SCRIPT . '?gadget=Blog&action=ViewTrackback&id='.$row['id'];
            $newRow['title']   = '<a href="'.$url.'">'.Jaws_XSS::filter($row['title']).'</a>';

            $newRow['created'] = $date->Format($row['createtime']);
            switch($row['status']) {
                case 'approved':
                    $newRow['status'] = _t('COMMENTS_STATUS_APPROVED');
                    break;
                case 'waiting':
                    $newRow['status'] = _t('COMMENTS_STATUS_WAITING');
                    break;
                case 'spam':
                    $newRow['status'] = _t('COMMENTS_STATUS_SPAM');
                    break;
            }

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'), $url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                "javascript: trackbackDelete('".$row['id']."');",
                STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $newRow['actions'] = $actions;

            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Counts how many trackbacks are with a given filter
     *
     * See Filter modes for more info
     *
     * @access  public
     * @param   string  $filterMode     Which mode should be used to filter
     * @param   string  $filterData     Data that will be used in the filter
     * @param   string  $status         Spam status (approved, waiting, spam)
     * @param   mixed   $limit          Limit of data (numeric/boolean: no limit)
     * @return  mixed   Returns how many trackbacks exists with a given filter or Jaws_Error on failure
     */
    function HowManyFilteredTrackbacks($filterMode, $filterData, $status, $limit)
    {
        if (
            $filterMode != 'postid' &&
            $filterMode != 'status' &&
            $filterMode != 'ip'
        ) {
            $filterData = '%'.$filterData.'%';
        }

        $table = Jaws_ORM::getInstance()->table('blog_trackback');
        $table->select('count(*) as howmany');


        switch ($filterMode) {
            case 'postid':
                $table->and()->where('parent_id', $filterData);
                break;
            case 'blog_name':
                $table->and()->where('blog_name', $filterData, 'like');
                break;
            case 'url':
                $table->and()->where('url', $filterData, 'like');
                break;
            case 'title':
                $table->and()->where('title', $filterData, 'like');
                break;
            case 'ip':
                $table->and()->where('ip', $filterData, 'like');
                break;
            case 'excerpt':
                $table->and()->where('excerpt', $filterData, 'like');
                break;
            case 'various':
                $table->and()->openWhere()->where('blog_name', $filterData, 'like')->or();
                $table->where('url', $filterData, 'like')->or();
                $table->where('title', $filterData, 'like')->or();
                $table->where('excerpt', $filterData, 'like')->closeWhere();
                break;
            default:
                if (is_bool($limit)) {
                    $limit = false;
                    //By default we get the last 20 comments
                    $table->limit(20);
                }
                break;
        }

        if (in_array($status, array('approved', 'waiting', 'spam'))) {
            $table->and()->where('status', $status);
        }

        if (is_numeric($limit)) {
            $table->limit(10, $limit);
        }

        $howmany = $table->fetchOne();
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('GLOBAL_COMMENT_ERROR_GETTING_FILTERED_COMMENTS'), 'CORE');
        }

        return $howmany;
    }
}