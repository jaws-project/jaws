<?php
/**
 * FeedReader Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayLayoutParams()
    {
        $result = array();
        $rModel = $GLOBALS['app']->LoadGadget('FeedReader', 'Model');
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
     * @param   int     $id     Feed site ID
     * @return  string  XHTML content with all titles and links of feed sites
     */
    function Display($id = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('FeedReader', 'Model');
        $site = $model->GetFeed($id);
        if (Jaws_Error::IsError($site) || empty($site) || $site['visible'] == 0) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('FeedReader.html');
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

        $res = $parser->fetch($site['url']);
        if (PEAR::isError($res)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, '['._t('FEEDREADER_NAME').']: ',
                                 _t('FEEDREADER_ERROR_CANT_FETCH', Jaws_XSS::filter($site['url'])), '');
        }

        if (!isset($parser->feed)) {
            return false;
        }

        $block = ($site['view_type']==0)? 'simple' : 'marquee';
        $tpl->SetBlock("feedreader/$block");
        $tpl->SetVariable('title', _t('FEEDREADER_ACTION_TITLE'));

        switch ($site['title_view']) {
            case 1:
                $tpl->SetVariable('feed_title', Jaws_XSS::filter($parser->feed['channel']['title']));
                $tpl->SetVariable('feed_link',
                      Jaws_XSS::filter((isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : '')));
                break;
            case 2:
                $tpl->SetVariable('feed_title', Jaws_XSS::filter($site['title']));
                $tpl->SetVariable('feed_link',
                      Jaws_XSS::filter((isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : '')));
                break;
            default:
        }
        $tpl->SetVariable('marquee_direction', (($site['view_type']==2)? 'down' :
                                               (($site['view_type']==3)? 'left' :
                                               (($site['view_type']==4)? 'right' : 'up'))));
        if (isset($parser->feed['items'])) {
            foreach($parser->feed['items'] as $index => $item) {
                $tpl->SetBlock("feedreader/$block/item");
                $tpl->SetVariable('title', Jaws_XSS::filter($item['title']));
                $tpl->SetVariable('href', isset($item['link'])? Jaws_XSS::filter($item['link']) : '');
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
}