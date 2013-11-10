<?php
/**
 * Jaws Gadget Hook
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Hook
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * Store hooks objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  private
     */
    private $objects = array();


    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
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
    public function &load($hook)
    {
        // filter non validate character
        $hook = preg_replace('/[^[:alnum:]_]/', '', $hook);

        if (!isset($this->objects[$hook])) {
            $classname = $this->gadget->name. '_Hooks_'. $hook;
            $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Hooks/$hook.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
            }

            $this->objects[$hook] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget hook: [$classname]");
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