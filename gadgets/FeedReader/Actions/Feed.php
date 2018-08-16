<?php
/**
 * FeedReader Gadget
 *
 * @category   Gadget
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Actions_Feed extends Jaws_Gadget_Action
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayFeedsLayoutParams()
    {
        $result = array();
        $result[] = array(
            'title' => _t('FEEDREADER_SHOW_FEEDS'),
            'value' => array(
                0 => _t('FEEDREADER_GLOBAL_FEEDS'),
                1 => _t('FEEDREADER_USER_FEEDS'),
            )
        );

        return $result;
    }

    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayFeedLayoutParams()
    {
        $result = array();
        $rModel = $this->gadget->model->load('Feed');
        $sites = $rModel->GetFeeds();
        if (!Jaws_Error::isError($sites)) {
            $psites = array();
            foreach ($sites as $site) {
                $psites[$site['id']] = $site['title'];
            }

            $result[] = array(
                'title' => _t('FEEDREADER_FEED'),
                'value' => $psites
            );
        }

        return $result;
    }

    /**
     * Get Display user feed action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayUserFeedLayoutParams()
    {
        $result = array();
        $rModel = $this->gadget->model->load('Feed');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $sites = $rModel->GetFeeds(true, $user);
        if (!Jaws_Error::isError($sites)) {
            $psites = array();
            foreach ($sites as $site) {
                $psites[$site['id']] = $site['title'];
            }

            $result[] = array(
                'title' => _t('FEEDREADER_FEED'),
                'value' => $psites
            );
        }

        return $result;
    }

    /**
     * Displays titles of the feed sites
     *
     * @access  public
     * @param   int     $user   Only show user's feeds?
     * @return  string  XHTML content with all titles and links of feed sites
     */
    function DisplayFeeds($user = 0)
    {
        $user = empty($user)? 0 : (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Feed');
        $feeds = $model->GetFeeds(true, $user);
        if (Jaws_Error::IsError($feeds)) {
            return false;
        }

        $tpl = $this->gadget->template->load('FeedReaders.html');
        $tpl->SetBlock('feedreaders');

        if (count($feeds) > 0) {
            foreach ($feeds as $feed) {
                $tpl->SetBlock("feedreaders/feed");
                $tpl->SetVariable('url', $this->gadget->urlMap('GetFeed', array('id' => $feed['id'])));
                $tpl->SetVariable('alias', $feed['alias']);
                $tpl->SetVariable('title', $feed['title']);
                $tpl->ParseBlock('feedreaders/feed');
            }
        }

        $tpl->ParseBlock('feedreaders');
        return $tpl->Get();
    }

    /**
     * Displays titles of the feed sites
     *
     * @access  public
     * @param   int     $id     Feed site ID
     * @return  string  XHTML content with all titles and links of feed sites
     */
    function DisplayFeed($id = 0)
    {
        if(empty($id)) {
            $id = $this->gadget->registry->fetch('default_feed');
        }

        $model = $this->gadget->model->load('Feed');
        $site = $model->GetFeed($id);
        if (Jaws_Error::IsError($site) || empty($site) || !$site['published']) {
            return false;
        }

        // check user permissions
        if (!empty($site['user'])) {
            if ($site['user'] != (int)$GLOBALS['app']->Session->GetAttribute('user')) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $tpl = $this->gadget->template->load('FeedReader.html');
        $tpl->SetBlock('feedreader');

        require_once JAWS_PATH . 'gadgets/FeedReader/include/XML_Feed.php';
        $parser = new XML_Feed();
        $parser->cache_time = $site['cache_time'];
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
        $parser->setParams($options);

        if (Jaws_Utils::is_writable(JAWS_DATA.'feedcache')) {
            $parser->cache_dir = JAWS_DATA . 'feedcache';
        }

        $res = $parser->fetch(Jaws_XSS::defilter($site['url']));
        if (PEAR::isError($res)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, '['.$this->gadget->title.']: ',
                _t('FEEDREADER_ERROR_CANT_FETCH', Jaws_XSS::refilter($site['url'])), '');
        }

        if (!isset($parser->feed)) {
            return false;
        }

        $block = ($site['view_type']==0)? 'simple' : 'marquee';
        $tpl->SetBlock("feedreader/$block");
        $tpl->SetVariable('alias', $site['alias']);
        $tpl->SetVariable('title', _t('FEEDREADER_ACTION_TITLE'));

        switch ($site['title_view']) {
            case 1:
                $tpl->SetVariable('feed_title', Jaws_XSS::refilter($parser->feed['channel']['title']));
                $tpl->SetVariable('feed_link',
                    Jaws_XSS::refilter(
                        isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : ''
                    )
                );
                break;
            case 2:
                $tpl->SetVariable('feed_title', Jaws_XSS::refilter($site['title']));
                $tpl->SetVariable('feed_link',
                    Jaws_XSS::refilter(
                        isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : ''
                    )
                );
                break;
            default:
        }
        $tpl->SetVariable('marquee_direction', (($site['view_type']==2)? 'down' :
            (($site['view_type']==3)? 'left' :
                (($site['view_type']==4)? 'right' : 'up'))));
        if (isset($parser->feed['items'])) {
            foreach($parser->feed['items'] as $index => $item) {
                $tpl->SetBlock("feedreader/$block/item");
                $tpl->SetVariable('title', strip_tags($item['title']));
                $tpl->SetVariable('href', isset($item['link'])? Jaws_XSS::refilter($item['link']) : '');
                $tpl->SetVariable(
                    'summary',
                    isset($item['summary'])? strip_tags($item['summary']) : ''
                );
                $tpl->ParseBlock("feedreader/$block/item");
                if (($site['count_entry'] > 0) && ($site['count_entry'] <= ($index + 1))) {
                    break;
                }
            }
        }

        $tpl->ParseBlock("feedreader/$block");
        $tpl->ParseBlock('feedreader');
        return $tpl->Get();
    }

    /**
     * Displays titles of the feed sites
     *
     * @access  public
     * @param   int     $id     Feed site ID
     * @return  string  XHTML content with all titles and links of feed sites
     */
    function DisplayUserFeed($id = 0)
    {
        return $this->DisplayFeed($id);
    }

    /**
     * Gets the dcDate of an item
     *
     * From planet-php.net source code
     *
     * @access  private
     * @param   array    $item          Item to look for the date
     * @param   int      $offset        Offset of item(index)
     * @param   bool     $returnNull    Should it return false?
     * @return  string   The correct dcDate
     */
    function GetDCDate($item, $nowOffset = 0, $returnNull = false)
    {
        if (isset($item['dc']['date'])) {
            $dcdate = $this->FixDate($item['dc']['date']);
        } elseif (isset($item['pubdate'])) {
            $dcdate = $this->FixDate($item['pubdate']);
        } elseif (isset($item['issued'])) {
            $dcdate = $this->FixDate($item['issued']);
        } elseif (isset($item['created'])) {
            $dcdate = $this->FixDate($item['created']);
        } elseif (isset($item['modified'])) {
            $dcdate = $this->FixDate($item['modified']);
        } elseif ($returnNull) {
            return NULL;
        } else {
            //TODO: Find a better alternative here
            $dcdate = gmdate('Y-m-d H:i:s O', time() + $nowOffset);
        }
        return $dcdate;
    }

    /**
     * Fixes the date format
     *
     * @access  private
     * @param   string  $date  Date to fix
     * @return  string  New date format
     */
    function FixDate($date)
    {
        $date =  preg_replace('/([0-9])T([0-9])/', '$1 $2', $date);
        $date =  preg_replace('/([\+\-][0-9]{2}):([0-9]{2})/', '$1$2', $date);
        $date =  gmdate('Y-m-d H:i:s O', strtotime($date));
        return $date;
    }

    /**
     * Gets requested feed
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetFeed()
    {
        $id = $this->gadget->request->fetch('id', 'get');

        $layoutGadget = $this->gadget->action->load('Feed');
        return $layoutGadget->DisplayFeed($id);
    }

    /**
     * Displays an UI for managing user's feeds
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserFeedsList()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_alias', _t('GLOBAL_ALIAS'));
        $this->gadget->define('lbl_published', _t('GLOBAL_PUBLISHED'));
        $this->gadget->define('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->define('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->load('UserFeeds.html');
        $tpl->SetBlock('UserFeeds');
        $tpl->SetVariable('title', _t('FEEDREADER_USER_FEEDS'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));

        $tpl->SetVariable('lbl_cache_time', _t('FEEDREADER_CACHE_TIME'));
        $tpl->SetVariable('lbl_disable', _t('GLOBAL_DISABLE'));
        $tpl->SetVariable('lbl_minutes_10', _t('GLOBAL_DATE_MINUTES', 10));
        $tpl->SetVariable('lbl_minutes_30', _t('GLOBAL_DATE_MINUTES', 30));
        $tpl->SetVariable('lbl_hours_1', _t('GLOBAL_DATE_HOURS', 1));
        $tpl->SetVariable('lbl_hours_5', _t('GLOBAL_DATE_HOURS', 5));
        $tpl->SetVariable('lbl_hours_10', _t('GLOBAL_DATE_HOURS', 10));
        $tpl->SetVariable('lbl_days_1', _t('GLOBAL_DATE_DAYS', 1));
        $tpl->SetVariable('lbl_weeks_1', _t('GLOBAL_DATE_WEEKS', 1));

        $tpl->SetVariable('lbl_view_type', _t('FEEDREADER_VIEW_TYPE'));
        $tpl->SetVariable('lbl_view_type_simple', _t('FEEDREADER_VIEW_TYPE_SIMPLE'));
        $tpl->SetVariable('lbl_view_type_up', _t('FEEDREADER_VIEW_TYPE_MARQUEE_UP'));
        $tpl->SetVariable('lbl_view_type_down', _t('FEEDREADER_VIEW_TYPE_MARQUEE_DOWN'));
        $tpl->SetVariable('lbl_view_type_left', _t('FEEDREADER_VIEW_TYPE_MARQUEE_LEFT'));
        $tpl->SetVariable('lbl_view_type_right', _t('FEEDREADER_VIEW_TYPE_MARQUEE_RIGHT'));

        $tpl->SetVariable('lbl_title_view', _t('FEEDREADER_TITLE_VIEW'));
        $tpl->SetVariable('lbl_title_view_disable', _t('FEEDREADER_TITLE_VIEW_DISABLE'));
        $tpl->SetVariable('lbl_title_view_internal', _t('FEEDREADER_TITLE_VIEW_INTERNAL'));
        $tpl->SetVariable('lbl_title_view_external', _t('FEEDREADER_TITLE_VIEW_EXTERNAL'));

        $tpl->SetVariable('lbl_count_entry', _t('FEEDREADER_SITE_COUNT_ENTRY'));
        $tpl->SetVariable('lbl_alias', _t('GLOBAL_ALIAS'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));

        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->ParseBlock('UserFeeds');
        return $tpl->Get();
    }

    /**
     * Return user's feeds list
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetUserFeeds()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(
            array('limit', 'offset', 'searchBy'),
            'post'
        );

        $model = $this->gadget->model->load('Feed');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

        $filters = array();
        if (!empty($post['searchBy'])) {
            $filters = array('term' => $post['searchBy']);
        }
        $feeds = $model->GetFeeds($filters, $user, $post['limit'], $post['offset']);
        $total = $model->GetFeedsCount($filters, $user);

        foreach ($feeds as $key => $feed) {
            $feed['published'] = ($feed['published'])? _t('GLOBAL_YES') : _t('GLOBAL_NO');
            $feeds[$key] = $feed;
        }
        return $GLOBALS['app']->Session->GetResponse(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $total,
                'records' => $feeds
            )
        );
    }

    /**
     * Return user's feed info
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetUserFeed()
    {
        $id = $this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->load('Feed');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        return $model->GetFeed($id, $user);
    }

    /**
     * Insert user's feed
     *
     * @access  public
     * @return  string  XHTML content
     */
    function InsertFeed()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $data = $this->gadget->request->fetch('data:array', 'post');
        $model = $this->gadget->model->load('Feed');
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->InsertUserFeed($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_ERROR_SITE_NOT_ADDED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_SITE_ADDED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Update user's feed
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UpdateFeed()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $model = $this->gadget->model->load('Feed');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->UpdateUserFeed($post['id'], $post['data'], $user);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_SITE_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete user's feed
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DeleteUserFeed()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int)$this->gadget->request->fetch('id', 'post');

        $model = $this->gadget->model->load('Feed');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->DeleteUserFeed($user, $id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_ERROR_SITE_NOT_DELETED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FEEDREADER_SITE_DELETED'), RESPONSE_NOTICE);
        }
    }

}