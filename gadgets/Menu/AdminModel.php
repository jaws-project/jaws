<?php
/**
 * Menu Gadget
 *
 * @category   GadgetModel
 * @package    Menu
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Menu/Model.php';

class MenuAdminModel extends MenuModel
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

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Install listener for removing menu's item related to uninstalled gadget
        $GLOBALS['app']->Listener->NewListener($this->_Name, 'onBeforeUninstallingGadget', 'RemoveMenusByType');

        // Registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/Menu/default_group_id', '1');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed     True on success or Jaws_Error on failure
     */
    function UninstallGadget()
    {
        $tables = array('menus',
                        'menus_groups');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('MENU_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Menu/default_group_id');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            $result = $this->installSchema('0.7.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('insert.xml', '', '0.7.0.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $sql = '
                SELECT [id], [title], [url], [menu_position]
                FROM [[menu]]
                ORDER BY [menu_position]';
            $menus = $GLOBALS['db']->queryAll($sql);
            if (Jaws_Error::IsError($menus)) {
                return $menus;
            }

            foreach ($menus as $m_idx => $menu) {
                $this->InsertMenu(0, 1, 'url', $menu['title'], $menu['url'], 0, $m_idx + 1, 1);
                $pid = $GLOBALS['db']->lastInsertID('menus', 'id');
                if (Jaws_Error::IsError($pid) || empty($pid)) {
                    $pid = $m_idx + 1;
                }
                $sql = '
                    SELECT [id], [text], [url], [item_position]
                    FROM [[menu_item]]
                    WHERE [parent_id] = {parent_id}
                    ORDER BY [item_position]';
                $params = array();
                $params['parent_id'] = $menu['id'];
                $subMenus = $GLOBALS['db']->queryAll($sql, $params);
                if (Jaws_Error::IsError($subMenus)) {
                    return $subMenus;
                }

                foreach ($subMenus as $s_idx => $submenu) {
                    $this->InsertMenu($pid, 1, 'url', $submenu['text'], $submenu['url'], 0, $s_idx + 1, 1);
                }
            }

            $tables = array('menu',
                            'menu_item');
            foreach ($tables as $table) {
                $result = $GLOBALS['db']->dropTable($table);
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Menu/ManageMenus',  'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Menu/ManageGroups', 'true');

            // Registry keys.
            $GLOBALS['app']->Registry->NewKey('/gadgets/Menu/default_group_id', '1');
        }

        if (version_compare($old, '0.7.1', '<')) {
            //remove old event listener
            $GLOBALS['app']->loadClass('Listener', 'Jaws_EventListener');
            $GLOBALS['app']->Listener->DeleteListener($this->_Name);
            // Install listener for removing menu's item related to uninstalled gadget
            $GLOBALS['app']->Listener->NewListener($this->_Name, 'onBeforeUninstallingGadget', 'RemoveMenusByType');
        }

        $result = $this->installSchema('schema.xml', '', "0.7.0.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        return true;
    }

    /**
    * Insert a group
    *
    * @access  public
    * @param   string   $title
    * @param   string   $title_view
    * @param   bool     $visible        is visible
    * @return  bool     True on success or False on failure
    */
    function InsertGroup($title, $title_view, $visible)
    {
        $sql = 'SELECT COUNT([id]) FROM [[menus_groups]] WHERE [title] = {title}';
        $gc = $GLOBALS['db']->queryOne($sql, array('title' => $title));
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            INSERT INTO [[menus_groups]]
                ([title], [title_view], [visible])
            VALUES
                ({title}, {title_view}, {visible})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['title']       = $xss->parse($title);
        $params['title_view']  = $title_view;
        $params['visible']     = $visible;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $gid = $GLOBALS['db']->lastInsertID('menus_groups', 'id');
        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_CREATED'), RESPONSE_NOTICE, $gid);

        return true;
    }

    /**
    * Insert a menu
    *
    * @access  public
    * @param    int     $pid
    * @param    int     $gid        group ID
    * @param    string  $type
    * @param    string  $title
    * @param    string  $url
    * @param    string  $url_target
    * @param    string  $rank
    * @param    bool    $visible    is visible
    * @param    string  $image
    * @return   bool    True on success or False on failure
    */
    function InsertMenu($pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image)
    {
        $sql = '
            INSERT INTO [[menus]]
                ([pid], [gid], [menu_type], [title], [url], [url_target], [rank], [visible], [image])
            VALUES
                ({pid}, {gid}, {type}, {title}, {url}, {url_target}, {rank}, {visible}, {image})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['pid']         = $pid;
        $params['gid']         = $gid;
        $params['type']        = $type;
        $params['title']       = $xss->parse($title);
        $params['url']         = $xss->parse($url);
        $params['url_target']  = $url_target;
        $params['rank']        = $rank;
        $params['visible']     = $visible;
        if (empty($image)) {
            $params['image'] = null;
        } else {
            $image = preg_replace("/[^[:alnum:]_\.-]*/i", "", $image);
            $filename = Jaws_Utils::upload_tmp_dir(). '/'. $image;
            $params['image'] = array('type'=> 'blob', 'value' => 'File://' . $filename);
        }

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (isset($filename)) {
            Jaws_Utils::Delete($filename);
        }

        $mid = $GLOBALS['db']->lastInsertID('menus', 'id');
        $this->MoveMenu($mid, $gid, $gid, $pid, $pid, $rank, null);
        $GLOBALS['app']->Session->PushLastResponse($mid.'%%' . _t('MENU_NOTICE_MENU_CREATED'), RESPONSE_NOTICE);

        return true;
    }

    /**
    * Update a group
    *
    * @access  public
    * @param    int     $gid            group ID
    * @param    string  $title
    * @param    string  $title_view
    * @param    bool    $visible        is visible
    * @return   bool    True on success or False on failure
    */
    function UpdateGroup($gid, $title, $title_view, $visible)
    {
        $sql = '
            SELECT
                COUNT([id])
            FROM [[menus_groups]]
            WHERE
                [id] != {gid} AND [title] = {title}';

        $gc = $GLOBALS['db']->queryOne($sql, array('gid' => $gid, 'title' => $title));
        if (Jaws_Error::IsError($gc)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_DUPLICATE_GROUP_TITLE'), RESPONSE_ERROR);
            return false;
        }

        $sql = '
            UPDATE [[menus_groups]] SET
                [title]       = {title},
                [title_view]  = {title_view},
                [visible]     = {visible}
            WHERE [id] = {gid}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['gid']         = $gid;
        $params['title']       = $xss->parse($title);
        $params['title_view']  = $title_view;
        $params['visible'] = $visible;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_UPDATED'), RESPONSE_NOTICE);

        return true;
    }

    /**
    * Update a menu
    *
    * @access  public
    * @param    int     $mid        menu ID
    * @param    int     $pid
    * @param    int     $gid        group ID
    * @param    string  $type
    * @param    string  $title
    * @param    string  $url
    * @param    string  $url_target
    * @param    string  $rank
    * @param    bool    $visible    is visible
    * @param    string  $image
    * @return   bool    True on success or False on failure
    */
    function UpdateMenu($mid, $pid, $gid, $type, $title, $url, $url_target, $rank, $visible, $image)
    {
        $oldMenu = $this->GetMenu($mid);
        if (Jaws_Error::IsError($oldMenu)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GET_MENUS'), RESPONSE_ERROR);
            return false;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['mid']         = $mid;
        $params['pid']         = $pid;
        $params['gid']         = $gid;
        $params['type']        = $type;
        $params['title']       = $xss->parse($title);
        $params['url']         = $xss->parse($url);
        $params['url_target']  = $url_target;
        $params['rank']        = $rank;
        $params['visible']     = $visible;

        $sql = '
            UPDATE [[menus]] SET
                [pid]         = {pid},
                [gid]         = {gid},
                [menu_type]   = {type},
                [title]       = {title},
                [url]         = {url},
                [url_target]  = {url_target},
                [rank]        = {rank},
                [visible]     = {visible}';
        if ($image !== 'true') {
            $sql.= ', [image] = {image}';
            if (empty($image)) {
                $params['image'] = null;
            } else {
                $image = preg_replace("/[^[:alnum:]_\.-]*/i", "", $image);
                $filename = Jaws_Utils::upload_tmp_dir(). '/'. $image;
                $params['image'] = array('type'=> 'blob', 'value' => 'File://' . $filename);
            }
        }
        $sql .= ' WHERE [id] = {mid}';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (isset($filename)) {
            Jaws_Utils::Delete($filename);
        }

        $this->MoveMenu($mid, $gid, $oldMenu['gid'], $pid, $oldMenu['pid'], $rank, $oldMenu['rank']);
        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_MENU_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GROUP_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $group = $this->GetGroups($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $sql = 'DELETE FROM [[menus]] WHERE [gid] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $sql = 'DELETE FROM [[menus_groups]] WHERE [id] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_GROUP_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

    /**
     * Delete a menu
     *
     * @access  public
     * @param   int     $mid    menu ID
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteMenu($mid)
    {
        $menu = $this->GetMenu($mid);
        if (Jaws_Error::IsError($menu)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(isset($menu['id'])) {
            $sql  = 'SELECT [id] FROM [[menus]] WHERE [pid] = {mid}';
            $pids = $GLOBALS['db']->queryAll($sql, array('mid' => $mid));
            if (Jaws_Error::IsError($pids)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            foreach ($pids as $pid) {
                if (!$this->DeleteMenu($pid['id'])) {
                    return false;
                }
            }

            $this->MoveMenu($mid, $menu['gid'], $menu['gid'], $menu['pid'], $menu['pid'], 0xfff, $menu['rank']);
            $sql = 'DELETE FROM [[menus]] WHERE [id] = {mid}';
            $res = $GLOBALS['db']->query($sql, array('mid' => $mid));
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * Delete a all menu related with a gadget (type = %gadget%)
     *
     * @access  public
     * @param   string  $type
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function RemoveMenusByType($type)
    {
        $sql  = 'SELECT [id] FROM [[menus]] WHERE [menu_type] = {type}';
        $mids = $GLOBALS['db']->queryAll($sql, array('type' => $type));
        if (Jaws_Error::IsError($mids)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        foreach ($mids as $mid) {
            if (!$this->DeleteMenu($mid['id'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * function for change gid, pid and rank of menus
     *
     * @access  public
     * @param   int     $mid        menu ID
     * @param   int     $new_gid    new group ID
     * @param   int     $old_gid    old group ID
     * @param   int     $new_pid
     * @param   int     $old_pid
     * @param   string  $new_rank
     * @param   string  $old_rank
     * @return  bool    True on success or False on failure
     */
    function MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_rank, $old_rank)
    {
        if ($new_gid != $old_gid) {
            // set gid of submenu items
            $sub_menus = $this->GetLevelsMenus($mid);
            if (!Jaws_Error::IsError($sub_menus)) {
                foreach ($sub_menus as $menu) {
                    $sql = '
                        UPDATE [[menus]]
                        SET [gid]  = {gid}
                        WHERE [id] = {mid} OR [pid] = {mid}';
                    $params         = array();
                    $params['mid']  = $menu['id'];
                    $params['gid']  = $new_gid;
                    $res = $GLOBALS['db']->query($sql, $params);
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                        return false;
                    }
                }
            }
        }

        if (($new_pid != $old_pid) || ($new_gid != $old_gid)) {
            // resort menu items in old_pid
            $sql = '
                UPDATE [[menus]] SET
                    [rank] = [rank] - 1
                WHERE
                    [pid] = {pid}
                  AND
                    [gid] = {gid}
                  AND
                    [rank] > {rank}';

            $params         = array();
            $params['gid']  = $old_gid;
            $params['pid']  = $old_pid;
            $params['rank'] = $old_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        if (($new_pid != $old_pid) || ($new_gid != $old_gid)) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[menus]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {mid}
                  AND
                    [gid] = {gid}
                  AND
                    [pid] = {pid}
                  AND
                    [rank] >= {new_rank}';

            $params             = array();
            $params['mid']      = $mid;
            $params['gid']      = $new_gid;
            $params['pid']      = $new_pid;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif (empty($old_rank)) {
            $sql = '
                UPDATE [[menus]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {mid}
                  AND
                    [gid] = {gid}
                  AND
                    [pid] = {pid}
                  AND
                    [rank] >= {new_rank}';

            $params             = array();
            $params['mid']      = $mid;
            $params['gid']      = $new_gid;
            $params['pid']      = $new_pid;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank > $old_rank) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[menus]] SET
                    [rank] = [rank] - 1
                WHERE
                    [id] <> {mid}
                  AND
                    [gid] = {gid}
                  AND
                    [pid] = {pid}
                  AND
                    [rank] > {old_rank}
                  AND
                    [rank] <= {new_rank}';

            $params             = array();
            $params['mid']      = $mid;
            $params['gid']      = $new_gid;
            $params['pid']      = $new_pid;
            $params['old_rank'] = $old_rank;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank < $old_rank) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[menus]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {mid}
                  AND
                    [gid] = {gid}
                  AND
                    [pid] = {pid}
                  AND
                    [rank] >= {new_rank}
                  AND
                    [rank] < {old_rank}';

            $params             = array();
            $params['mid']      = $mid;
            $params['gid']      = $new_gid;
            $params['pid']      = $new_pid;
            $params['old_rank'] = $old_rank;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        //$GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_MENU_MOVED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * function for get menus tree
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid    group ID
     * @param   string  $excluded_mid
     * @param   array   $menu_str
     * @return  bool    True on success or False on failure
     */
    function GetParentMenus($pid, $gid, $excluded_mid, &$result, $menu_str = '')
    {
        $parents = $this->GetLevelsMenus($pid, $gid);
        if (empty($parents)) return false;
        foreach ($parents as $parent) {
            if ($parent['id'] == $excluded_mid) continue;
            $result[] = array('pid'=> $parent['id'],
                              'title'=> $menu_str . '\\' . $parent['title']);
            $this->GetParentMenus($parent['id'], $gid, $excluded_mid, $result, $menu_str . '\\' . $parent['title']);
        }
        return true;
    }

}
