<?php
/**
 * Jaws Gadget Event
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Event
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
    function Jaws_Gadget_Event($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the gadget event file class in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $event  Event name
     * @return  mixed   Event class object on successful, Jaws_Error otherwise
     */
    function &loadEvent($event)
    {
        // filter non validate character
        $event = preg_replace('/[^[:alnum:]_]/', '', $event);

        if (!isset($this->gadget->events[$event])) {
            $event_class_name = $this->gadget->name. '_Events_'. $event;
            $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Events/$event.php";
            if (!@include_once($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            if (!Jaws::classExists($event_class_name)) {
                return Jaws_Error::raiseError("Class [$event_class_name] not exists!", __FUNCTION__);
            }

            $this->gadget->events[$event] = new $event_class_name($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget event: [$event_class_name]");
        }

        return $this->gadget->events[$event];
    }

}