<?php
/**
 * Banner Gadget
 *
 * @category   GadgetModel
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Amir Mohammad Saied <amir@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_Model extends Jaws_Gadget_Model
{
    /**
     * Retrieve banner
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  mixed   An array of banner's data and Jaws_Error on error
     */
    function GetBanner($bid)
    {
        $sql = '
            SELECT  [id], [title], [url], [gid], [banner], [template], [views], [views_limitation],
                    [clicks], [clicks_limitation], [start_time], [stop_time], [rank], [random], [published]
            FROM [[banners]]
            WHERE [id] = {bid}';

        $params = array();
        $params['bid'] = $bid;

        $types = array('integer', 'text', 'text', 'integer', 'text', 'text', 'integer', 'integer',
                       'integer', 'integer', 'timestamp', 'timestamp', 'integer', 'integer', 'boolean');
        $res = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Retrieve banners
     *
     * @access  public
     * @param   int     $bid     banner ID
     * @param   int     $gid     group ID
     * @param   int     $limit
     * @param   int     $offset
     * @param   int     $columns
     * @return  mixed   An array of available banners or Jaws_Error on error
     */
    function GetBanners($bid = -1, $gid = -1, $limit = 0, $offset = null, $columns = null)
    {
        if (empty($columns)) {
            $columns = '[id], [title], [url], [gid], [banner], [template], [views], [views_limitation],
                        [clicks], [clicks_limitation], [start_time], [stop_time], [createtime], [updatetime],
                        [random], [published]';
        }

        if (($bid != -1) && ($gid != -1)) {
            $sql = '
                SELECT {columns}
                FROM [[banners]]
                WHERE [[banners]].[id] = {bid} AND [[banners]].[gid] = {gid}
                ORDER BY [[banners]].[rank] ASC';
        } elseif ($gid != -1) {
            $sql = '
                SELECT {columns}
                FROM [[banners]]
                WHERE [[banners]].[gid] = {gid}
                ORDER BY [[banners]].[rank] ASC';
        } elseif ($bid != -1) {
            $sql = '
                SELECT {columns}
                FROM [[banners]]
                WHERE [id] = {bid}';
        } else {
            $sql = '
                SELECT {columns}
                FROM [[banners]]
                ORDER BY [id] ASC';
        }

        $params            = array();
        $params['bid']     = $bid;
        $params['gid']     = $gid;
        $params['columns'] = $columns;

        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $sql = str_replace("{columns}", $columns, $sql);
        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Retrieve group's info
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   An array of group's data or Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $sql = '
            SELECT  [id], [title], [limit_count], [show_title], [show_type], [published]
            FROM [[banners_groups]]
            WHERE [id] = {gid}';

        $params = array();
        $params['gid'] = $gid;

        $types = array('integer', 'text', 'integer', 'boolean', 'integer', 'boolean');
        $res = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Retrieve groups
     *
     * @access  public
     * @param   int     $gid    group ID
     * @param   int     $bid    banner ID
     * @param   int     $columns    
     * @return  mixed   An array of available banners or Jaws_Error on error
     */
    function GetGroups($gid = -1, $bid = -1, $columns = null)
    {
        if (empty($columns)) {
            $columns = '[id], [title], [limit_count], [published]';
        }

        if (($gid != -1) && ($bid != -1)) {
            $sql = '
                SELECT {columns}
                FROM [[banners_groups]]
                INNER JOIN [[banners]] ON [[banners_groups]].[id] = [[banners]].[gid]
                WHERE [[banners_groups]].[id] = {gid} AND [[banners]].[id] = {bid}
                ORDER BY [[banners_groups]].[id] ASC';
        } elseif ($bid != -1) {
            $sql = '
                SELECT {columns}
                FROM [[banners_groups]]
                INNER JOIN [[banners]] ON [[banners_groups]].[id] = [[banners]].[gid]
                WHERE [[banners]].[id] = {bid}
                ORDER BY [[banners_groups]].[id] ASC';
        } elseif ($gid != -1) {
            $sql = '
                SELECT {columns}
                FROM [[banners_groups]]
                WHERE [id] = {gid}';
        } else {
            $sql = '
                SELECT {columns}
                FROM [[banners_groups]]
                ORDER BY [id] ASC';
        }

        $params = array();
        $params['gid']     = $gid;
        $params['bid']     = $bid;
        $params['columns'] = $columns;

        $sql = str_replace("{columns}", $columns, $sql);
        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Retrieve banners that can be visible
     *
     * @access  public
     * @param   int     $gid   group ID
     * @param   bool    $random
     * @return  mixed   An array of available banners or False on error
     */
    function GetEnableBanners($gid = 0, $random = 0)
    {
        if ($gid == 0) {
            $sql = '
                SELECT [id], [title], [url], [banner], [template]
                FROM [[banners]]
                WHERE ([published] = {published}) AND ([random] = {random}) AND 
                    (([views_limitation] = 0) OR ([views] < [views_limitation])) AND
                    (([clicks_limitation] = 0) OR ([clicks] < [clicks_limitation])) AND
                    (([start_time] IS NULL) OR ({now} >= [start_time])) AND
                    (([stop_time] IS NULL) OR ({now} <= [stop_time]))
                ORDER BY [id] ASC';
        } else {
            $sql = '
                SELECT [id], [title], [url], [banner], [template]
                FROM [[banners]]
                WHERE ([[banners]].[gid] = {gid}) AND ([published] = {published}) AND ([random] = {random}) AND 
                    (([views_limitation] = 0) OR ([views] < [views_limitation])) AND
                    (([clicks_limitation] = 0) OR ([clicks] < [clicks_limitation])) AND
                    (([start_time] IS NULL) OR ({now} >= [start_time])) AND
                    (([stop_time] IS NULL) OR ({now} <= [stop_time]))
                ORDER BY [[banners]].[id] ASC';
        }

        $params = array();
        $params['gid']       = $gid;
        $params['random']    = $random;
        $params['now']       = $GLOBALS['db']->Date();
        $params['published'] = true;

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Retrieve visible banners
     *
     * @access  public
     * @param   int     $gid         group ID
     * @param   int     $limit_count
     * @return  array   An array of available banners
     */
    function GetVisibleBanners($gid, $limit_count)
    {
        $limit_count = empty($limit_count)? 256 : $limit_count;
        if (($always_array = $this->GetEnableBanners($gid, 0)) == false) {
            $always_array = array();
        }

        if (($random_array = $this->GetEnableBanners($gid, 1)) == false) {
            $random_array = array();
        }

        $res_array = array();
        if ((count($always_array) + count($random_array)) > $limit_count) {
            if(count($always_array) > $limit_count) {
                while (count($always_array) > $limit_count) {
                    array_splice($always_array, mt_rand(0, count($always_array)-1), 1);
                }
                $res_array = $always_array;
            } else {
                while (count($random_array) > ($limit_count - count($always_array))) {
                    array_splice($random_array, mt_rand(0, count($random_array)-1), 1);
                }
                $res_array = array_merge($always_array, $random_array);
            }
        } else {
            $res_array = array_merge($always_array, $random_array);
        }

        return $res_array;
    }

    /**
     * Increment the number of clicks a banner has had by 1.
     *
     * @access  public
     * @param   int     $bid    The id of the banner to increment
     * @return  mixed   True or Jaws_Error
     */
    function ClickBanner($bid)
    {
        $sql = 'UPDATE [[banners]] SET [clicks] = [clicks] + 1 WHERE [id] = {bid}';
        $res = $GLOBALS['db']->query($sql, array('bid' => $bid));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
     * Increment the number of views a banner has had by 1.
     *
     * @access  public
     * @param   int     $bid     The id of the banenr to increment.
     * @return  mixed   True on success and Jaws_Error on error
     */
    function ViewBanner($bid)
    {
        $sql = 'UPDATE [[banners]] SET [views] = [views] + 1 WHERE [id] = {bid}';
        $res = $GLOBALS['db']->query($sql, array('bid' => $bid));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

}