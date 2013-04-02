<?php
/**
 * Menu Installer
 *
 * @category    GadgetModel
 * @package     Menu
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Menu_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install()
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
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');

        // Registry keys
        $this->gadget->AddRegistry('default_group_id', '1');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed     True on success or Jaws_Error on failure
     */
    function Uninstall()
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
        $this->gadget->DelRegistry('default_group_id');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Upgrade($old, $new)
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

            $mnuModel = $this->gadget->load('Model')->load('AdminModel');
            foreach ($menus as $m_idx => $menu) {
                $mnuModel->InsertMenu(0, 1, 'url', $menu['title'], $menu['url'], 0, $m_idx + 1, 1);
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
                    $mnuModel->InsertMenu($pid, 1, 'url', $submenu['text'], $submenu['url'], 0, $s_idx + 1, 1);
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

            // Registry keys
            $this->gadget->AddRegistry('default_group_id', '1');
        }

        if (version_compare($old, '0.7.1', '<')) {
            //remove old event listener
            $GLOBALS['app']->loadClass('Listener', 'Jaws_EventListener');
            $GLOBALS['app']->Listener->DeleteListener($this->gadget->name);
            // Install listener for removing menu's item related to uninstalled gadget
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
        }

        $result = $this->installSchema('schema.xml', '', "0.7.0.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        return true;
    }

}