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
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Insert default layout elements
        $layoutModel = $this->gadget->load('Model')->load('AdminModel');
        $result = $layoutModel->GetLayoutItems();
        if (!Jaws_Error::IsError($result) && empty($result)) {
            $layoutModel->NewElement('main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', '', '', 1);
            $layoutModel->NewElement('bar1', 'Users', 'LoginBox', '', 'Login', 1);
        }

        // Add listener for remove/publish layout elements related to given gadget
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');

        // registry keys
        $this->gadget->registry->insert('pluggable', 'false');
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

            $layoutModel = $this->gadget->load('Model')->load('AdminModel');
            $items = $layoutModel->GetLayoutItems();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            $sql = '
                UPDATE [[layout]] SET
                    [gadget_action] = {gadget_action},
                    [action_params] = {action_params}
                WHERE [id] = {id}';

            foreach ($items as $item) {
                preg_match_all('/^([a-z0-9]+)\((.*?)\)$/i', $item['gadget_action'], $matches);
                if (isset($matches[1][0]) && isset($matches[2][0])) {
                    $item['gadget_action'] = $matches[1][0];
                    $item['action_params'] = array_filter(explode(',', $matches[2][0]));
                }
                $item['action_params'] = serialize($item['action_params']);
                $result = $GLOBALS['db']->query($sql, $item);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            // Add listener for remove/publish layout elements related to given gadget
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'EnableGadget');
            $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'DisableGadget');
        }

        return true;
    }

}