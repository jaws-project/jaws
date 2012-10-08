<?php
/**
 * RssReader Gadget
 *
 * @category   GadgetModel
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/RssReader/Model.php';

class RssReaderAdminModel extends RssReaderModel
{
    /**
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'rsscache' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('RSSREADER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/RssReader/default_feed', '0');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('rss_sites');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('RSSREADER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        //registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/RssReader/default_feed');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // ACL keys
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/RssReader/DeleteSite');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/RssReader/UpdateProperties');

        //registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/RssReader/default_feed', '0');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/RssReader/limit_entries');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/RssReader/order_type');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/RssReader/sort_type');

        return true;
    }

    /**
     * Insert the information of a RSS Site
     *
     * @access  public
     * @param   string  $title    Title of the RSS Site
     * @param   string  $url     URL of the RSS Site
     * @param   int     $cache_time
     * @param   int     $view_type
     * @param   int     $count_entry   The count of the viewable RSS title
     * @param   int     $title_view
     * @param   int     $visible The visible of the RSS Site
     * @return  bool    True on success and Jaws_Error on failure
     */
    function InsertRSS($title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            INSERT INTO [[rss_sites]]
                ([title], [url], [cache_time], [view_type], [count_entry], [title_view], [visible])
            VALUES
                ({title}, {url}, {cache_time}, {view_type}, {count_entry}, {title_view}, {visible})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['title']       = $xss->parse($title);
        $params['url']         = $xss->parse($url);
        $params['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $params['view_type']   = $view_type;
        $params['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_ADDED'),_t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update the information of a RSS Site
     *
     * @access  public
     * @param   string $RSSid   ID of the RSS Site
     * @param   string  $title   Title of the RSS Site
     * @param   string  $url     URL of the RSS Site
     * @param   int     $cache_time
     * @param   int     $view_type
     * @param   int     $count_entry   The count of the viewable RSS title
     * @param   int     $visible The visible of the RSS Site
     * @return  bool    True on success and Jaws_Error on failure
     */
    function UpdateRSS($RSSid, $title, $url, $cache_time, $view_type, $count_entry, $title_view, $visible)
    {
        $sql = '
            UPDATE [[rss_sites]] SET
                [title]       = {title},
                [url]         = {url},
                [cache_time]  = {cache_time},
                [view_type]   = {view_type},
                [count_entry] = {count_entry},
                [title_view]  = {title_view},
                [visible]     = {visible}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['id']          = (int)$RSSid;
        $params['title']       = $xss->parse($title);
        $params['url']         = $xss->parse($url);
        $params['cache_time']  = ((!is_numeric($cache_time)) ? 3600: $cache_time);
        $params['view_type']   = $view_type;
        $params['count_entry'] = ((empty($count_entry) || !is_numeric($count_entry)) ? 0: $count_entry);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_ERROR_SITE_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_UPDATED'), _t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a RSS Site
     *
     * @access  public
     * @param   string  $id ID of the RSS Site
     * @return  bool    True on success and Jaws_Error on failure
     */
    function DeleteRSS($id)
    {
        $sql = 'DELETE FROM [[rss_sites]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_ERROR_SITE_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('RSSREADER_ERROR_SITE_NOT_DELETED'), _t('RSSREADER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('RSSREADER_SITE_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}