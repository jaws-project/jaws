<?php
/**
 * Layout Installer
 *
 * @category    GadgetModel
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
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
        $layoutModel  = $this->gadget->load('Model')->load('Model', 'Layout');
        $elementModel = $this->gadget->load('Model')->load('AdminModel', 'Elements');
        $result = $layoutModel->GetLayoutItems();
        if (!Jaws_Error::IsError($result) && empty($result)) {
            $elementModel->NewElement('main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', null, '', 1);
            $elementModel->NewElement('bar1', 'Users', 'LoginBox', null, 'Login', 1);
        }

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Add listener for remove/publish layout elements related to given gadget
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');

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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $layoutModel = $this->gadget->load('Model')->load('Model', 'Layout');
            $items = $layoutModel->GetLayoutItems();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            $lyTable = Jaws_ORM::getInstance()->table('layout');
            foreach ($items as $item) {
                preg_match_all('/^([a-z0-9]+)\((.*?)\)$/i', $item['gadget_action'], $matches);
                if (isset($matches[1][0]) && isset($matches[2][0])) {
                    $item['gadget_action'] = $matches[1][0];
                    $item['action_params'] = array_filter(explode(',', $matches[2][0]));
                }
                $item['action_params'] = serialize($item['action_params']);
                $result = $lyTable->update($item)->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            // Remove old event listener
            $GLOBALS['app']->Listener->DeleteListener($this->gadget->name);
            // Add listener for remove/publish layout elements related to given gadget
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');
        }

        return true;
    }

}