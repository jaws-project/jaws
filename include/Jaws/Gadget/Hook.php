<?php
/**
 * Jaws Gadget Hook
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Hook
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
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Hook($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the gadget hook file class in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $hook  Hook name
     * @return  mixed   Hook class object on successful, Jaws_Error otherwise
     */
    function &load($hook)
    {
        // filter non validate character
        $hook = preg_replace('/[^[:alnum:]_]/', '', $hook);

        if (!isset($this->gadget->hooks[$hook])) {
            $hook_class_name = $this->gadget->name. '_Hooks_'. $hook;
            $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Hooks/$hook.php";
            if (!@include_once($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            if (!Jaws::classExists($hook_class_name)) {
                return Jaws_Error::raiseError("Class [$hook_class_name] not exists!", __FUNCTION__);
            }

            $this->gadget->hooks[$hook] = new $hook_class_name($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget hook: [$hook_class_name]");
        }

        return $this->gadget->hooks[$hook];
    }

    /**
     * Get hook options
     *
     * @access  public
     * @return  mixed   Returns hook options
     */
    function GetOptions()
    {
        return null;
    }

}