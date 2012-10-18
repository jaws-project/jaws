<?php
/**
 * Banner Admin Model
 *
 * @category   GadgetModel
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Banner/Model.php';

class BannerAdminModel extends BannerModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . $this->GetBannersDirectory('/');
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('BANNER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  mixed    True on a successful install and Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $tables = array('banners',
                        'banners_groups');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('BANNER_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        //registry keys

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   TRUE on success, or Jaws_Error
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', '0.7.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('update.xml', '', '0.8.0.xml', true);
            if (Jaws_Error::IsError($result)) {
                // maybe user have banner group with this name
                //return $result;
            }
        }

        if (version_compare($old, '0.8.1', '<')) {
            $base_path = $GLOBALS['app']->getDataURL() . $this->GetBannersDirectory('/');
            $sql = '
                SELECT [id], [banner]
                FROM [[banners]]';
            $banners = $GLOBALS['db']->queryAll($sql);
            if (!Jaws_Error::IsError($banners)) {
                foreach ($banners as $banner) {
                    if (!empty($banner['banner'])) {
                        if (strpos($banner['banner'], $base_path) !== 0) {
                            continue;
                        }
                        $banner['banner'] = substr($banner['banner'], strlen($base_path));
                        $sql = '
                            UPDATE [[banners]] SET
                                [banner] = {banner}
                            WHERE [id] = {id}';
                        $res = $GLOBALS['db']->query($sql, $banner);
                    }
                }
            }
        }

        if (version_compare($old, '0.8.2', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        return true;
    }

    /**
    * Insert a bannser
    * 
    * @access   public
    * @param    string  $title        banner title
    * @param    string  $url          banner url
    * @param    int     $gid          group ID
    * @param    string  $banner
    * @param    string  $template
    * @param    int     $views_limit
    * @param    int     $clicks_limit
    * @param    long    $start_time
    * @param    long    $stop_time
    * @param    bool    $random
    * @param    bool    $published
    * @return   bool    True on Success, False on Failure
    */
    function InsertBanner($title, $url, $gid, $banner, $template, $views_limit,
                          $clicks_limit, $start_time, $stop_time, $random, $published)
    {
        $sql = '
            INSERT INTO [[banners]]
                ([title], [url], [gid], [banner], [template], [views], [views_limitation],
                [clicks], [clicks_limitation], [start_time], [stop_time], [createtime], [updatetime],
                [random], [published])
            VALUES
                ({title}, {url}, {gid}, {banner}, {template}, 0, {views_limit}, 0,
                {clicks_limit}, {start_time}, {stop_time},{now}, {now}, {random}, {published})';

        $date = $GLOBALS['app']->loadDate();
        $params                 = array();
        $params['title']        = $title;
        $params['url']          = $url;
        $params['gid']          = ((empty($gid) || !is_numeric($gid)) ? 0: $gid);
        $params['banner']       = $banner;
        $params['template']     = $template;
        $params['views_limit']  = ((empty($views_limit)  || !is_numeric($views_limit)) ? 0: $views_limit);
        $params['clicks_limit'] = ((empty($clicks_limit) || !is_numeric($clicks_limit))? 0: $clicks_limit);

        $params['start_time']   = null;
        $params['stop_time']    = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $params['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $params['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }

        $params['now']          = $GLOBALS['db']->Date();
        $params['random']       = $random;
        $params['published']    = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_CREATED', $title), RESPONSE_NOTICE);

        return true;
    }

    /**
    * Update a bannser
    * 
    * @access   public
    * @param    int     $bid         banner ID
    * @param    string  $title       banner title
    * @param    string  $url
    * @param    int     $gid         group ID
    * @param    string  $banner
    * @param    string  $template
    * @param    int     $views_limit
    * @param    int     $clicks_limit
    * @param    string  $start_time
    * @param    string  $stop_time
    * @param    bool    $random
    * @param    bool    $published
    * @return   bool    True on Success, False on Failure
    */
    function UpdateBanner($bid, $title, $url, $gid, $banner, $template, $views_limit,
                          $clicks_limit, $start_time, $stop_time, $random, $published)
    {
        $sql = '
            UPDATE [[banners]] SET
                [title]             = {title},
                [url]               = {url},
                [gid]               = {gid},
                [banner]            = {banner},
                [template]          = {template},
                [views_limitation]  = {views_limit},
                [clicks_limitation] = {clicks_limit},
                [start_time]        = {start_time},
                [stop_time]         = {stop_time},
                [updatetime]        = {now},
                [random]            = {random},
                [published]         = {published}
            WHERE [id] = {bid}';

        $date = $GLOBALS['app']->loadDate();
        $params                 = array();
        $params['bid']          = $bid;
        $params['title']        = $title;
        $params['url']          = $url;
        $params['gid']          = ((empty($gid) || !is_numeric($gid)) ? 0: $gid);
        $params['banner']       = $banner;
        $params['template']     = $template;
        $params['views_limit']  = ((empty($views_limit)  || !is_numeric($views_limit)) ? 0: $views_limit);
        $params['clicks_limit'] = ((empty($clicks_limit) || !is_numeric($clicks_limit))? 0: $clicks_limit);

        $params['start_time']   = null;
        $params['stop_time']    = null;
        if (!empty($start_time)) {
            $start_time = $date->ToBaseDate(preg_split('/[- :]/', $start_time), 'Y-m-d H:i:s');
            $params['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time,  'Y-m-d H:i:s');
        }
        if (!empty($stop_time)) {
            $stop_time  = $date->ToBaseDate(preg_split('/[- :]/', $stop_time), 'Y-m-d H:i:s');
            $params['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time,   'Y-m-d H:i:s');
        }

        $params['now']          = $GLOBALS['db']->Date();
        $params['random']       = $random;
        $params['published']    = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_UPDATED', $title), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Retrieve banners
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   Banners count and Jaws_Error on error
     */
    function GetBannersCount($gid = -1)
    {
        $sql = '
            SELECT COUNT([id])
            FROM [[banners]]';

        if ($gid != -1) {
            $sql .= ' WHERE [[banners]].[gid] = {gid}';
        }

        $res = $GLOBALS['db']->queryOne($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
    * Insert a group
    *
    * @access  public
    * @param    string  $title
    * @param    int     $limit_count
    * @param    bool    $show_title
    * @param    bool    $show_type
    * @param    bool    $published
    * @return   bool    True on Success, False on Failure
    */
    function InsertGroup($title, $limit_count, $show_title, $show_type, $published)
    {
        $sql = '
            INSERT INTO [[banners_groups]]
                ([title], [limit_count], [show_title], [show_type], [published])
            VALUES
                ({title}, {limit_count}, {show_title}, {show_type}, {published})';

        $params = array();
        $params['title']       = $title;
        $params['limit_count'] = (empty($limit_count)  || !is_numeric($limit_count))? 0: $limit_count;
        $params['show_title']  = (bool)$show_title;
        $params['show_type']   = (int)$show_type;
        $params['published']   = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_CREATED', $title), RESPONSE_NOTICE);

        return true;
    }

    /**
    * Update a group
    *
    * @access  public
    * @param    int     $gid         group ID
    * @param    string  $title       group title
    * @param    int     $limit_count
    * @param    bool    $show_title
    * @param    bool    $show_type
    * @param    bool    $published
    * @return   bool    True on Success, False on Failure
    */
    function UpdateGroup($gid, $title, $limit_count, $show_title, $show_type, $published)
    {
        $sql = '
            UPDATE [[banners_groups]] SET
                [title]       = {title},
                [limit_count] = {limit_count},
                [show_title]  = {show_title},
                [show_type]   = {show_type},
                [published]   = {published}
            WHERE [id] = {id}';

        $params = array();
        $params['id']          = $gid;
        $params['title']       = $title;
        $params['limit_count'] = (empty($limit_count)  || !is_numeric($limit_count))? 0: $limit_count;
        $params['show_title']  = (bool)$show_title;
        $params['show_type']   = (int)$show_type;
        $params['published']   = (bool)$published;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_UPDATED', $title), RESPONSE_NOTICE);

        return true;
    }

    /**
     * Add a group of banner (by they ids) to a certain group
     *
     * @access  public
     * @param   int     $gid        Group's ID
     * @param   array   $banners    Array with banner id
     * @return  bool    True
     */
    function AddBannersToGroup($gid, $banners)
    {
        $AllBanners = $this->GetBanners(-1, -1);
        foreach ($AllBanners as $banner) {
            $rank = array_search($banner['id'], $banners);
            $rank = ($rank === false)? 0: $rank + 1;
            if (($banner['gid'] == $gid) || ($rank != 0)) {
                $this->UpdateBannerGroup($banner['id'], -1, $gid, $rank);
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_UPDATED_BANNERS'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds an banner to a group
     *
     * @access  public
     * @param   int     $bid        Banner's ID
     * @param   int     $gid        Group's ID
     * @param   int     $new_gid    Group's ID
     * @param   int     $rank
     * @return  bool    Returns True if banner was sucessfully added to the group, False if not
     */
    function UpdateBannerGroup($bid, $gid, $new_gid, $rank)
    {
        $new_gid = ($rank == 0)? 0 : $new_gid;
        if (($bid != -1) && ($gid != -1)) {
            $sql = '
                UPDATE [[banners]] SET
                    [gid]        = {new_gid},
                    [rank]       = {rank},
                    [updatetime] = {now}
                WHERE [[banners]].[id] = {bid} AND [[banners]].[gid] = {gid}';
        } elseif ($gid != -1) {
            $sql = '
                UPDATE [[banners]] SET
                    [gid]        = {new_gid},
                    [rank]       = {rank},
                    [updatetime] = {now}
                WHERE [[banners]].[gid] = {gid}';
        } elseif ($bid != -1) {
            $sql = '
                UPDATE [[banners]] SET
                    [gid]        = {new_gid},
                    [rank]       = {rank},
                    [updatetime] = {now}
                WHERE [id] = {bid}';
        } else {
            $sql = '
                UPDATE [[banners]] SET
                    [gid]        = {new_gid},
                    [rank]       = {rank},
                    [updatetime] = {now}';
        }

        $date = $GLOBALS['app']->loadDate();
        $params = array();
        $params['bid']     = $bid;
        $params['gid']     = $gid;
        $params['new_gid'] = $new_gid;
        $params['rank']    = $rank;
        $params['now']     = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Delete a banner
     *
     * @access  public
     * @param   int     $bid     The banner ID that will be deleted
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteBanner($bid)
    {
        $banner = $this->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($banner['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $sql = 'DELETE FROM [[banners]] WHERE [id] = {bid}';
        $res = $GLOBALS['db']->query($sql, array('bid' => $bid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_DELETED', $banner['title']), RESPONSE_NOTICE);
        Jaws_Utils::Delete(JAWS_DATA . $this->GetBannersDirectory('/') . $banner['banner']);

        return true;
    }

    /**
     * Reset banner's views counter
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  bool    True if successful, False otherwise
     */
    function ResetViews($bid)
    {
        $banner = $this->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($banner['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            UPDATE [[banners]] SET
                [views]      = {views},
                [updatetime] = {now}
            WHERE [[banners]].[id] = {bid}';

        $date = $GLOBALS['app']->loadDate();
        $params = array();
        $params['bid']   = $bid;
        $params['views'] = 0;
        $params['now']   = $GLOBALS['db']->Date();
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Reset banner's clicks counter
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  bool    True if successful, False otherwise
     */
    function ResetClicks($bid)
    {
        $banner = $this->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($banner['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            UPDATE [[banners]] SET
                [clicks]     = {clicks},
                [updatetime] = {now}
            WHERE [[banners]].[id] = {bid}';

        $date = $GLOBALS['app']->loadDate();
        $params = array();
        $params['bid']    = $bid;
        $params['clicks'] = 0;
        $params['now']    = $GLOBALS['db']->Date();
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $bid     The banner that will be deleted
     * @return  bool    True if query was successful and False on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_ERROR_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $group = $this->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $this->UpdateBannerGroup(-1, $gid, 0, 0);
        $sql = 'DELETE FROM [[banners_groups]] WHERE [id] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}