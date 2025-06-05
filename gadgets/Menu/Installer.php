<?php
/**
 * Menu Installer
 *
 * @category    GadgetModel
 * @package     Menu
 */
class Menu_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('default_group_id', '1'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageMenus',
        'ManageGroups',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @param   string  $input_schema       Schema file path
     * @param   array   $input_variables    Schema variables
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install($input_schema = '', $input_variables = array())
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', array(), 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Add listener for remove/publish menu items related to given gadget
        $this->gadget->event->insert('UninstallGadget');

        // Add dynamic ACL for menu group
        $this->gadget->acl->insert('GroupAccess', 1, true);

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
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = Jaws::t('ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
            }
        }

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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('1.0.0.xml', array(), '0.7.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Update layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Menu', 'Display', 'Menu', 'Menu');
            }

            // Remove old event listener
            $this->gadget->event->delete();
            // Add listener for remove/publish menu items related to given gadget
            $this->gadget->event->insert('UninstallGadget');
            $this->gadget->event->insert('EnableGadget');
            $this->gadget->event->insert('DisableGadget');
        }

        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.1.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // set dynamic access ACL for groups
            $gModel = $this->gadget->model->load('Group');
            $groups = $gModel->GetGroups();
            foreach ($groups as $group) {
                $this->gadget->acl->insert('GroupAccess', $group['id'], true);
            }
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            $result = $this->installSchema('1.4.0.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            $objORM = Jaws_ORM::getInstance()->table('menus');
            $menus = $objORM->select('id:integer', 'published:boolean')->fetchAll();
            if (Jaws_Error::IsError($menus)) {
                return $menus;
            }
            $objORM->beginTransaction();
            foreach ($menus as $menu) {
                $result = $objORM->update(array('status' => (int)$menu['published']))->where('id', $menu['id'])->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
            $objORM->commit();
        }

        if (version_compare($old, '1.5.0', '<')) {
            $result = $this->installSchema('1.5.0.xml', array(), '1.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // not longer need listen on enable/disable gadgets
            $this->gadget->event->delete('EnableGadget');
            $this->gadget->event->delete('DisableGadget');
        }

        if (version_compare($old, '1.6.0', '<')) {
            $result = $this->installSchema('1.6.0.xml', array(), '1.5.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.7.0', '<')) {
            $result = $this->installSchema('1.7.0.xml', array(), '1.6.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.8.0', '<')) {
            $result = $this->installSchema('1.8.0.xml', array(), '1.7.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.9.0', '<')) {
            $result = $this->installSchema('1.9.0.xml', array(), '1.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.0.0', '<')) {
            $result = $this->installSchema('2.0.0..xml', array(), '1.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '2.1.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '2.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}