<?php
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

/**
 * Plugin to import gadget action
 *
 * @category   Plugin
 * @package    ActionImport
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ActionImport extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function ActionImport()
    {
        $this->_Name = 'ActionImport';
        $this->_Description = _t('PLUGINS_ACTIONIMPORT_DESCRIPTION');
        $this->_Example = '[import gadget="Blocks" action="Display" params="1"]';
        $this->_IsFriendly = false;
        $this->_Version = '0.1';
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        $blockPattern = '@\[(ActionImport|Import)\s*gadget="(.*?)"\s*action="(.*?)"\s*(params="(.*?)")?\]@ism';
        $html = preg_replace_callback($blockPattern, array(&$this, 'Prepare'), $html);
        return $html;
    }

    /**
     * The preg_replace call back function
     *
     * @access  private
     * @param   string  $matches    Matched strings from preg_replace_callback
     * @return  string  Gadget action output or plain text on errors
     */
    function Prepare($data)
    {
        $gadget = $data[2];
        $action = $data[3];
        $params = @$data[5];

        $layoutModel = $GLOBALS['app']->LoadGadget($gadget, 'LayoutHTML');
        if (!Jaws_Error::IsError($layoutModel) && method_exists($layoutModel, $action)) {
            return isset($params)? $layoutModel->$action($params) : $layoutModel->$action();
        }

        return $data[0];
    }

}