<?php
/**
 * Jaws Gadget Template
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Template
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object  $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Template($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the gadget template file in question
     *
     * @access  public
     * @param   bool    $backend    Admin template file?
     * @param   string  $filename   Template file name
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @return  object  Jaws_Template object
     */
    function &load($backend, $filename, $options = array())
    {
        $theme = $GLOBALS['app']->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $tpl = new Jaws_Template();
        $loadFromTheme = isset($options['loadFromTheme'])? $options['loadFromTheme'] : (JAWS_SCRIPT == 'index');
        if ($loadFromTheme && file_exists($theme['path']. $this->gadget->name. '/'. $filename)) {
            $filepath = $theme['path']. $this->gadget->name;
        } else {
            $filepath = 'gadgets/'. $this->gadget->name. '/Templates/'. ($backend? 'Admin': '');
        }

        // set options
        foreach ($options as $option => $value) {
            $tpl->$option = $value;
        }

        $tpl->Load($filename, $filepath);
        return $tpl;
    }

}