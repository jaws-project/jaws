<?php
/**
 * Jaws Gadget Layout
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Layout
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
    function __construct($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Gets a layout variable
     *
     * @access  public
     * @param   string  $name       Key name
     * @param   string  $component  (Optional) Component name
     * @return  mixed   Returns value of the attribute or Null if not exist
     */
    function getVariable($name, $component = '')
    {
        $component = empty($component)? $this->gadget->name : $component;
        return $GLOBALS['app']->Layout->fetchVariable($name, $component);
    }

    /**
     * Sets a layout variable
     *
     * @access  public
     * @param   string  $name       Layout variable name
     * @param   string  $value      Layout variable value
     * @param   string  $component  (Optional) Component name
     * @return  void
     */
    function setVariable($name, $value, $component = '')
    {
        $component = empty($component)? $this->gadget->name : $component;
        return $GLOBALS['app']->Layout->setVariable($name, $value, $component);
    }

    /**
     * Delete a layout variable
     *
     * @access  public
     * @param   string  $name   Layout variable name
     * @return  bool    True
     */
    function deleteVariable($name)
    {
        $component = $this->gadget->name;
        return $GLOBALS['app']->Layout->deleteVariable($name, $component);
    }

}