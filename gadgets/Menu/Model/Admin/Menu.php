<?php
/**
 * Menu Gadget
 *
 * @category    GadgetModel
 * @package     Menu
 */
class Menu_Model_Admin_Menu extends Jaws_Gadget_Model
{
    /**
     * Inserta a new menu
     *
     * @access  public
     * @param   array   $mData  Menu data attributes
     * @return  bool    True on success or False on failure
     */
    function InsertMenu($mData)
    {
        $mData['order']  = (int)$mData['order'];
        $mData['status'] = (int)$mData['status'];
        if (empty($mData['image'])) {
            $mData['image']  = null;
        } else {
            $image = preg_replace("/[^[:alnum:]_\.\-]*/i", "", $mData['image']);
            $filename = Jaws_Utils::upload_tmp_dir(). '/'. $image;
            $mData['image'] = array('File://' . $filename, 'blob');
        }

        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $mid = $menusTable->insert($mData)->exec();

        if (Jaws_Error::IsError($mid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (isset($filename)) {
            Jaws_Utils::Delete($filename);
        }

        $this->MoveMenu($mid, $mData['gid'], $mData['gid'], $mData['pid'], $mData['pid'], $mData['order'], null);
        $GLOBALS['app']->Session->PushLastResponse($mid.'%%' . _t('MENU_NOTICE_MENU_CREATED'), RESPONSE_NOTICE);

        return true;
    }

    /**
     * Updates the menu
     *
     * @access  public
     * @param   int     $mid    Menu ID
     * @param   array   $mData  Menu data attributes
     * @return  bool    True on success or False on failure
     */
    function UpdateMenu($mid, $mData)
    {
        $model = $this->gadget->model->load('Menu');
        $oldMenu = $model->GetMenu($mid);
        if (Jaws_Error::IsError($oldMenu)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('MENU_ERROR_GET_MENUS'), RESPONSE_ERROR);
            return false;
        }

        $mData['order']  = (int)$mData['order'];
        $mData['status'] = (int)$mData['status'];
        if ($mData['image'] !== 'true') {
            if (empty($mData['image'])) {
                $mData['image'] = null;
            } else {
                $image = preg_replace("/[^[:alnum:]_\.\-]*/i", "", $mData['image']);
                $filename = Jaws_Utils::upload_tmp_dir(). '/'. $image;
                $mData['image'] = array('File://' . $filename, 'blob');
            }
        } else {
            unset($mData['image']);
        }

        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $res = $menusTable->update($mData)->where('id', $mid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (isset($filename)) {
            Jaws_Utils::Delete($filename);
        }

        $this->MoveMenu(
            $mid,
            $mData['gid'],
            $oldMenu['gid'],
            $mData['pid'],
            $oldMenu['pid'],
            $mData['order'],
            $oldMenu['order']
        );
        $GLOBALS['app']->Session->PushLastResponse(_t('MENU_NOTICE_MENU_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the menu
     *
     * @access  public
     * @param   int     $mid    menu ID
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteMenu($mid)
    {
        $model = $this->gadget->model->load('Menu');
        $menu = $model->GetMenu($mid);
        if (Jaws_Error::IsError($menu)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(isset($menu['id'])) {
            $menusTable = Jaws_ORM::getInstance()->table('menus');
            $pids = $menusTable->select('id')->where('pid', $mid)->fetchAll();
            if (Jaws_Error::IsError($pids)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            foreach ($pids as $pid) {
                if (!$this->DeleteMenu($pid['id'])) {
                    return false;
                }
            }

            $this->MoveMenu($mid, $menu['gid'], $menu['gid'], $menu['pid'], $menu['pid'], 0xfff, $menu['order']);
            $res = $menusTable->delete()->where('id', $mid)->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * Delete all menu related the gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function DeleteGadgetMenus($gadget)
    {
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $mids = $menusTable->select('id')->where('type', $gadget)->fetchAll();
        if (Jaws_Error::IsError($mids)) {
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
     * function for change gid, pid and order of menus
     *
     * @access  public
     * @param   int     $mid        menu ID
     * @param   int     $new_gid    new group ID
     * @param   int     $old_gid    old group ID
     * @param   int     $new_pid
     * @param   int     $old_pid
     * @param   string  $new_order
     * @param   string  $old_order
     * @return  bool    True on success or False on failure
     */
    function MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_order, $old_order)
    {
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        if ($new_gid != $old_gid) {
            // set gid of submenu items
            $model = $this->gadget->model->load('Menu');
            $sub_menus = $model->GetLevelsMenus($mid);
            if (!Jaws_Error::IsError($sub_menus)) {
                foreach ($sub_menus as $menu) {
                    $menusTable->update(array('gid' => $new_gid))->where('id', $menu['id'])->or();
                    $res = $menusTable->where('pid', $menu['id'])->exec();
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                        return false;
                    }
                }
            }
        }

        if (($new_pid != $old_pid) || ($new_gid != $old_gid)) {
            // resort menu items in old_pid
            $res = $menusTable->update(
                array(
                    'order' => $menusTable->expr('order - ?', 1)
                )
            )->where('pid', $old_pid)->and()->where('gid', $old_gid)->and()->where('order', $old_order, '>')->exec();

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            // resort menu items in new_pid
            $menusTable->update(
                array(
                    'order' => $menusTable->expr('order + ?', 1)
                )
            )->where('id', $mid, '<>')->and()->where('gid', $new_gid)->and()->where('pid', $new_pid);
            $res = $menusTable->and()->where('order', $new_order, '>=')->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif (empty($old_order)) {
            $menusTable->update(
                array(
                    'order' => $menusTable->expr('order + ?', 1)
                )
            )->where('id', $mid, '<>')->and()->where('gid', $new_gid)->and()->where('pid', $new_pid);
            $res = $menusTable->and()->where('order', $new_order, '>=')->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_order > $old_order) {
            // resort menu items in new_pid
            $menusTable->update(
                array(
                    'order' => $menusTable->expr('order - ?', 1)
                )
            )->where('id', $mid, '<>')->and()->where('gid', $new_gid)->and()->where('pid', $new_pid);
            $res = $menusTable->and()->where('order', $old_order, '>')->and()->where('order', $new_order, '<=')->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_order < $old_order) {
            // resort menu items in new_pid
            $menusTable->update(
                array(
                    'order' => $menusTable->expr('order + ?', 1)
                )
            )->where('id', $mid, '<>')->and()->where('gid', $new_gid)->and()->where('pid', $new_pid);
            $res = $menusTable->and()->where('order', $new_order, '>=')->and()->where('order', $old_order, '<')->exec();
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
     * @param   int     $gid            Group ID
     * @param   string  $excluded_mid
     * @param   string  $result         Result reference
     * @param   array   $menu_str
     * @return  bool    True on success or False on failure
     */
    function GetParentMenus($pid, $gid, $excluded_mid, &$result, $menu_str = '')
    {
        $model = $this->gadget->model->load('Menu');
        $parents = $model->GetLevelsMenus($pid, $gid);
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
