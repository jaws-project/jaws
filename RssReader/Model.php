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
class RssReader_Model extends Jaws_Gadget_Model
{
    /**
     * Gets list of possible RSS sites
     *
     * @access  public
     * @param   bool    $onlyVisible    Visible sites only
     * @param   int     $limit          Number of data to retrieve (false = returns all)
     * @param   int     $offset         Data offset
     * @return  mixed   Array of RSS sites or Jaws_Error on failure
     */
    function GetRSSs($onlyVisible = false, $limit = false, $offset = null)
    {
        if (is_numeric($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        if ($onlyVisible) {
            $sql = '
                SELECT
                    [id], [title], [url], [cache_time], [view_type], [count_entry], [title_view]
                FROM [[rss_sites]]
                WHERE [visible] = 1
                ORDER BY [id] ASC';
        } else {
            $sql = '
                SELECT
                    [id], [title], [url], [cache_time], [view_type], [count_entry], [title_view], [visible]
                FROM [[rss_sites]]
                ORDER BY [id] ASC';
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Gets information of the RSS site
     *
     * @access  public
     * @param   int     $id    RSS Site ID
     * @return  mixed   Array of the RSS site data or Jaws_Error on failure
     */
    function GetRSS($id)
    {
        $sql = '
            SELECT
                [id], [title], [url], [cache_time], [view_type], [count_entry], [title_view], [visible]
            FROM [[rss_sites]]
            WHERE [id] = {id}';

        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('RSSREADER_ERROR_SITE_DOES_NOT_EXISTS'), _t('RSSREADER_NAME'));
    }
}