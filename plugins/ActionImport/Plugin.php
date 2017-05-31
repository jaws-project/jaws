<?php
/**
 * Plugin to import gadget action
 *
 * @category   Plugin
 * @package    ActionImport
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ActionImport_Plugin
{
    var $friendly = false;
    var $version = '0.1';

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
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

        $actions = Jaws_Gadget::getInstance($gadget)->action->fetchAll('index');
        if (in_array($action, array_keys($actions)) &&
            isset($actions[$action]['normal']) && $actions[$action]['normal'])
        {
            $objAction = Jaws_Gadget::getInstance($gadget)->action->load($actions[$action]['file']);
            return isset($params)? $objAction->$action($params) : $objAction->$action();
        }

        return $data[0];
    }

}