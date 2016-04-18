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
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageLayout',
        'ManageThemes',
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
        $layoutModel  = $this->gadget->model->load('Layout');
        $elementModel = $this->gadget->model->loadAdmin('Elements');
        $result = $layoutModel->GetLayoutItems();
        if (!Jaws_Error::IsError($result) && empty($result)) {
            $elementModel->NewElement(
                'Layout', null, 'main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', null, '', 1
            );
            $elementModel->NewElement(
                'Layout', null, 'bar1', 'Users', 'LoginBox', null, 'Login', 1
            );
        }

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
            $result = $this->installSchema('2.0.0.xml', '', '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $this->gadget->acl->insert('ManageLayout');
            $this->gadget->acl->update('default', '', false);
        }

        if (version_compare($old, '3.0.0', '<')) {
            $result = $this->installSchema('3.0.0.xml', '', '2.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '3.1.0', '<')) {
            $result = $this->installSchema('3.1.0.xml', '', '3.0.0.xml');
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
            $result = $this->installSchema('schema.xml', '', '3.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}