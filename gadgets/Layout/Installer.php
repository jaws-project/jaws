<?php
/**
 * Layout Installer
 *
 * @category    GadgetModel
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Installer extends Jaws_Gadget_Installer
{
    /**
     * Default ACL value of front-end gadget access
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = false;

    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('default_layout_type', 0, true),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'MainLayoutManage',
        'UserLayoutAccess',
        'UserLayoutManage',
        'UsersLayoutAccess',
        'UsersLayoutManage',
        'SwitchThemeManage',
    );

    /**
     * Installs the gadget
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

        // Insert default layout elements
        $elementModel = $this->gadget->model->loadAdmin('Elements');
        $elementModel->NewElement(
            'Layout', null, 'main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', null, '', 1
        );
        $elementModel->NewElement(
            'Layout', null, 'bar1', 'Users', 'LoginBox', null, 'Login', 1
        );

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Add listener for remove/publish layout elements related to given gadget
        $this->gadget->event->insert('UninstallGadget');
        $this->gadget->event->insert('EnableGadget');
        $this->gadget->event->insert('DisableGadget');
        // add login user event listeners
        $this->gadget->event->insert('LoginUser');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '2.0.0', '<')) {
            $result = $this->installSchema('2.0.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $this->gadget->acl->insert('ManageLayout');
            $this->gadget->acl->update('default', '', false);
        }

        if (version_compare($old, '3.0.0', '<')) {
            $result = $this->installSchema('3.0.0.xml', array(), '2.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.1.0', '<')) {
            $result = $this->installSchema('3.1.0.xml', array(), '3.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $lyTable = Jaws_ORM::getInstance()->table('layout');
            $result = $lyTable->update(array('layout' => 'Index.Dashboard'))->where('user', 0, '<>')->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            $result = $lyTable->update(array('layout' => 'Index'))->where('index', true)->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '4.0.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '3.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $lyTable = Jaws_ORM::getInstance()->table('layout');
            $result = $lyTable->update(
                array(
                    'theme'    => $this->gadget->theme,
                    'locality' => $this->gadget->locality
                )
            )->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '4.1.0', '<')) {
            // apply new changes in ACLs
            $this->gadget->acl->rename('ManageLayout', 'MainLayoutManage');
            $this->gadget->acl->rename('ManageThemes', 'SwitchThemeManage');
            $this->gadget->acl->insert('UserLayoutAccess',  '', 0);
            $this->gadget->acl->insert('UserLayoutManage',  '', 0);
            $this->gadget->acl->insert('UsersLayoutAccess', '', 0);
            $this->gadget->acl->insert('UsersLayoutManage', '', 0);

            // registry key
            $this->gadget->registry->insert('default_layout_type', 0, true);

            // add login user event listeners
            $this->gadget->event->insert('LoginUser');
        }

        return true;
    }

}