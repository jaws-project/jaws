<?php
/**
 * RssReader Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class RssReaderLayoutHTML
{

    /**
     * Loads layout actions
     *
     * @access  private
     * @return  array   List of actions
     */
    function LoadLayoutActions()
    {
        $model = $GLOBALS['app']->LoadGadget('RssReader', 'Model');
        $sites = $model->GetRSSs();

        $actions = array();
        if (!Jaws_Error::isError($sites)) {
            foreach ($sites as $site) {
                $actions['Display(' . $site['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $site['title'],
                    'desc' => _t('RSSREADER_LAYOUT_SHOW_TITLES_DESCRIPTION')
                );
            }
        }

        return $actions;
    }

    /**
     * Displays titles of the RSS sites
     *
     * @access  public
     * @param   int     $id     RSS site ID
     * @return  string  XHTML content with all titles and links of RSS sites
     */
    function Display($id = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('RssReader', 'Model');
        $site = $model->GetRSS($id);
        if (Jaws_Error::IsError($site) || empty($site) || $site['visible'] == 0) {
            return false;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $tpl = new Jaws_Template('gadgets/RssReader/templates/');
        $tpl->Load('RssReader.html');
        $tpl->SetBlock('rssreader');

        require_once JAWS_PATH . 'gadgets/RssReader/include/XML_Feed.php';
        $parser = new XML_Feed();
        $parser->cache_time = $site['cache_time'];
        $options = array();
        $timeout = (int)$GLOBALS['app']->Registry->Get('/config/connection_timeout');
        $options['timeout'] = $timeout;
        if ($GLOBALS['app']->Registry->Get('/network/proxy_enabled') == 'true') {
            if ($GLOBALS['app']->Registry->Get('/network/proxy_auth') == 'true') {
                $options['proxy_user'] = $GLOBALS['app']->Registry->Get('/network/proxy_user');
                $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('/network/proxy_pass');
            }
            $options['proxy_host'] = $GLOBALS['app']->Registry->Get('/network/proxy_host');
            $options['proxy_port'] = $GLOBALS['app']->Registry->Get('/network/proxy_port');
        }
        $parser->setParams($options);

        if (Jaws_Utils::is_writable(JAWS_DATA.'rsscache')) {
            $parser->cache_dir = JAWS_DATA . 'rsscache';
        }

        $res = $parser->fetch($site['url']);
        if (PEAR::isError($res)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, '['._t('RSSREADER_NAME').']: ',
                                 _t('RSSREADER_ERROR_CANT_FETCH', $xss->filter($site['url'])), '');
        }

        if (!isset($parser->feed)) {
            return false;
        }

        $block = ($site['view_type']==0)? 'simple' : 'marquee';
        $tpl->SetBlock("rssreader/$block");
        $tpl->SetVariable('title', _t('RSSREADER_ACTION_TITLE'));

        switch ($site['title_view']) {
            case 1:
                $tpl->SetVariable('feed_title', $xss->filter($parser->feed['channel']['title']));
                $tpl->SetVariable('feed_link',
                      $xss->filter((isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : '')));
                break;
            case 2:
                $tpl->SetVariable('feed_title', $xss->filter($site['title']));
                $tpl->SetVariable('feed_link',
                      $xss->filter((isset($parser->feed['channel']['link']) ? $parser->feed['channel']['link'] : '')));
                break;
            default:
        }
        $tpl->SetVariable('marquee_direction', (($site['view_type']==2)? 'down' :
                                               (($site['view_type']==3)? 'left' :
                                               (($site['view_type']==4)? 'right' : 'up'))));
        if (isset($parser->feed['items'])) {
            foreach($parser->feed['items'] as $index => $item) {
                $tpl->SetBlock("rssreader/$block/item");
                $tpl->SetVariable('title', $xss->filter($item['title']));
                $tpl->SetVariable('href', isset($item['link'])? $xss->filter($item['link']) : '');
                $tpl->ParseBlock("rssreader/$block/item");
                if (($site['count_entry'] > 0) && ($site['count_entry'] <= ($index + 1))) {
                    break;
                }
            }
        }

        $tpl->ParseBlock("rssreader/$block");
        $tpl->ParseBlock('rssreader');
        return $tpl->Get();
    }
}