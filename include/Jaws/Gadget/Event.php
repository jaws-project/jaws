<?php
/**
 * Jaws Gadget Event
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Event extends Jaws_Gadget_Class
{
    /**
     * Store events objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  private
     */
    private $objects = array();

    /**
     * Loads the gadget event file class in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $event  Event name
     * @param   int     $error_level  Sets this error level if not exists
     * @return  mixed   Event class object on successful, Jaws_Error otherwise
     */
    public function &load($event, $error_level = JAWS_ERROR_NOTICE)
    {
        // filter non validate character
        $event = preg_replace('/[^[:alnum:]_]/', '', $event);

        if (!isset($this->objects[$event])) {
            $classname = $this->gadget->name. '_Events_'. $event;
            $file = ROOT_JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Events/$event.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__, 404, $error_level);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
            }

            $this->objects[$event] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded gadget event: [$classname]");
        }

        return $this->objects[$event];
    }


    /**
     * Shouts a call to the listener object that will act immediately.
     *
     * @access  public
     * @param   string  $event      Event name
     * @param   mixed   $params     Event param(s)
     * @param   string  $gadget     If set, returns listener result of this gadget
     * @param   bool    $broadcast  Broadcast event to all listeners
     * @return  mixed   True if successfully, otherwise returns Jaws_Error
     */
    public function shout($event, $params = array(), $gadget = '', $broadcast = true)
    {
        return $this->app->listener->Shout($this->gadget->name, $event, $params, $gadget, $broadcast);
    }


    /**
     * Add a new listener and saves it in the DB
     *
     * @access  public
     * @param   string  $event  Event name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    True if listener was added, otherwise returns Jaws_Error
     */
    public function insert($event, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $this->app->listener->AddListener($gadget, $event);
    }


    /**
     * Deletes a shouter
     *
     * @access  public
     * @param   string  $event   Event name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    True if listener was deleted, otherwise returns Jaws_Error
     */
    public function delete($event = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $this->app->listener->DeleteListener($gadget, $event);
    }

}