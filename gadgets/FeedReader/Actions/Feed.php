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
        if (Jaws_Error::IsError($site) || empty($site) || $site['visible'] == 0) {
            return false;
        }

        // check user permissions
        if (!empty($site['user'])) {
            if ($site['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
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
                $tpl->SetVariable('title', Jaws_XSS::refilter($item['title']));
                $tpl->SetVariable('href', isset($item['link'])? Jaws_XSS::refilter($item['link']) : '');
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
        $id = jaws()->request->fetch('id', 'get');

        $layoutGadget = $this->gadget->action->load('Feed');
        return $layoutGadget->DisplayFeed($id);
    }
}