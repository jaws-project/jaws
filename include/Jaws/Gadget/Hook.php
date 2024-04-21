<?php
/**
 * Jaws Gadget Hook
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Hook extends Jaws_Gadget_Class
{
    /**
     * Store hooks objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  private
     */
    private $objects = array();

    /**
     * Loads the gadget hook file class in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $hook  Hook name
     * @param   int     $error_level  Sets this error level if not exists
     * @return  mixed   Hook class object on successful, Jaws_Error otherwise
     */
    public function &load($hook, $error_level = JAWS_ERROR_NOTICE)
    {
        // filter non validate character
        $hook = preg_replace('/[^[:alnum:]_]/', '', $hook);

        if (!isset($this->objects[$hook])) {
            $classname = $this->gadget->name. '_Hooks_'. $hook;
            $file = ROOT_JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Hooks/$hook.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__, 404, $error_level);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
            }

            $this->objects[$hook] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded gadget hook: [$classname]");
        }

        return $this->objects[$hook];
    }


    /**
     * Get hook options
     *
     * @access  public
     * @return  mixed   Returns hook options
     */
    public function GetOptions()
    {
        return null;
    }

}