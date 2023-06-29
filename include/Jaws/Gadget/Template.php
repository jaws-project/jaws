<?php
/**
 * Jaws Gadget Template
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Template extends Jaws_Gadget_Class
{
    /**
     * Loads the gadget template file in question
     *
     * @access  public
     * @param   string  $filename   Template file name
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @param   bool    $backend    Admin template file?
     * @return  object  Jaws_Template object
     */
    function &xLoad($filename, $options = array(), $backend = false)
    {
        $loadFromTheme = isset($options['loadFromTheme'])? $options['loadFromTheme'] : (JAWS_SCRIPT == 'index');
        if (isset($options['basePath'])) {
            $basePath = $options['basePath'];
        } else {
            $basePath = 'gadgets/'. $this->gadget->name. '/Templates'. ($backend? '/Admin': '');
        }

        unset($options['loadFromTheme']);
        $tpl = new Jaws_XTemplate($loadFromTheme);
        $tpl->parseFile($filename, $basePath);
        return $tpl;
    }

    /**
     * Loads the gadget template file in question
     *
     * @access  public
     * @param   string  $filename   Template file name
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @param   bool    $backend    Admin template file?
     * @return  object  Jaws_Template object
     */
    function &load($filename, $options = array(), $backend = false)
    {
        $filepath = dirname($filename);
        $filename = basename($filename);
        $loadFromTheme = isset($options['loadFromTheme'])? $options['loadFromTheme'] : (JAWS_SCRIPT == 'index');
        // if dirname returned dot ('.'), indicating no slashes in path(current directory)
        if ($filepath == '.') {
            $filepath = 'gadgets/'. $this->gadget->name. '/Templates/'. ($backend? 'Admin': '');
        }

        unset($options['loadFromTheme']);
        $tpl = new Jaws_Template($loadFromTheme);
        foreach ($options as $option => $value) {
            $tpl->$option = $value;
        }

        $tpl->Load($filename, $filepath);
        return $tpl;
    }

    /**
     * Loads the gadget template file in question
     *
     * @access  public
     * @param   string  $filename   Template file name
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @return  object  Jaws_Template object
     */
    function &loadAdmin($filename, $options = array())
    {
        return $this->load($filename, $options, true);
    }

    /**
     * Loads the gadget template file in question
     *
     * @access  public
     * @param   string  $filename   Template file name
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @return  object  Jaws_Template object
     */
    function &xloadAdmin($filename, $options = array())
    {
        return $this->xload($filename, $options, true);
    }

}