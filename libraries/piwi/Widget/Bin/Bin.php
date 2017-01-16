<?php
/**
 * Bin.php - Bin Class for all bin widgets
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */

require_once PIWI_PATH . '/Widget/Widget.php';
require_once PIWI_PATH . '/JS/JSEnums.php';

class Bin extends Widget
{
    /**
     *
     * Comment the widget (usefull if you want to use it in a form)
     *
     * @var    string $_comment
     * @access private
     * @see    setComment(), getComment()
     */
    var $_comment;

    /**
     *
     * AccessKey for the widget
     *
     * @var    string $_accessKey
     * @access private
     * @see    setAccessKey(), getAccessKey()
     */
    var $_accessKey;

    /**
     *
     * Data attribute for the widget
     *
     * @var    array $_data
     * @access private
     * @see    setData()
     */
    var $_data = array();

    /**
     * JS events
     *
     * @var    array  $_rvents;
     * @access private
     * @see    addEvent()
     */
    var $_events;

    /**
     * Tells if the bin widget is enabled or not
     *
     * @var    boolean $_isEnabled
     * @access private
     * @see    setEnabled()
     */
    var $_isEnabled;

    /**
     * Is a 'list' that will have the avaiable gadgets
     * of a specific widget
     *
     * @var    array   $_availableEvents;
     * @access private
     * @see    addEvent()
     */
    var $_availableEvents;

    /**
     * Add two colons to the end of the title.
     *
     * For example: Your name: <Name Entry>
     *
     * @var    boolean $_twoColons
     * @access private
     * @see    enableTwoColons(), requiresTwoColons()
     */
    var $_twoColons;

    /**
     * Is a list where all validators should go
     *
     * @var    array   $_validators
     * @access private
     * @see    addValidator(), getValidators()
     */
    var $_validators;

    /**
     * Initializes the binary
     *
     * @access  private
     */
    function init()
    {
        $this->_familyWidget = 'bin';
        $this->_events       = array();
        $this->_validators   = array();
        $this->setEnabled(true);
        $this->enableTwoColons(true);
        parent::init();

        if (!empty($this->_title)) {
            $this->setTitle($this->_title);
        } else {
            $this->setTitle('');
        }

        $this->setComment($this->_comment);
    }

    /**
     * Enable or disable the use of two-colons
     *
     * @access    public
     * @param     boolean  $status True if two-colons should be added or false if not
     */
    function enableTwoColons($status = true)
    {
        $this->_twoColons = $status;
    }

    /**
     * Return the status of two-colons. To know if two colons should be added or not
     *
     * @access    public
     * @return    boolean  True if two-colons should be added or false if not
     */
    function requiresTwoColons()
    {
        return $this->_twoColons;
    }

    /**
     * Get the comment of the widget
     *
     * @access   public
     */
    function getComment()
    {
        return $this->_comment;
    }

    /**
     * Set the comment
     *
     * @access    public
     * @param     string Comment to use
     */
    function setComment($comment)
    {
        $this->_comment = $comment;
    }

    /**
     * Get the accesskey of the widget
     *
     * @access   public
     */
    function getAccessKey()
    {
        return $this->_accessKey;
    }

    /**
     * Set the comment
     *
     * @access    public
     * @param     string Key to Use
     */
    function setAccessKey($accesskey)
    {
        $this->_accessKey = $accesskey;
    }

    /**
     * Set data attributes
     *
     * @access    public
     * @param     string name
     * @param     string value
     */
    function setData($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Get the direction of the box
     * @return string Direction of the box
     */
    function getDirection()
    {
        return $this->_direction;
    }

    /**
     * Build the basic piwiXML data, adding bin params
     *
     * @access   private
     */
    function buildBasicPiwiXML()
    {
        parent::buildBasicPiwiXML();

        if (!empty($this->_title)) {
            $this->_PiwiXML->addAttribute('title', $this->_Title);
        }
    }

    /**
     * Build the basix XHTML data - Adding bin params
     *
     * @access   private
     * @return   string  XHTML data
     */
    function buildBasicXHTML()
    {
        $xhtml = '';
        if (!empty($this->_accessKey)) {
            $xhtml.= " accesskey=\"".$this->_accessKey."\"";
        }
        $xhtml.= parent::buildBasicXHTML();

        foreach ($this->_data as $name => $value) {
            $xhtml.= " data-{$name}=\"{$value}\"";
        }

        return $xhtml;
    }

    /**
     * Build the Events in a piwiXML style
     *
     * @access   private
     * @return   string  Events in piwiXML style
     */
    function buildXMLEvents()
    {
        if (is_array($this->_events) && count($this->_events) > 0) {
            $this->_PiwiXML->openElement('events');
            foreach ($this->_events as $event) {
                $code = $event->getCode();
                if (substr($code, -1) != ';') {
                    $code = $code . ';';
                }

                $this->_PiwiXML->openElement('event');
                $this->_PiwiXML->addAttribute('listener', $event->getID());

                if ($event->needsFile()) {
                    $this->_PiwiXML->addAttribute('src', $event->getSrc());
                }

                $this->_PiwiXML->addText($code, true);
                $this->_PiwiXML->closeElement('event');
            }
            $this->_PiwiXML->closeElement('events');
        }
    }

    /**
     * Build the JS events XHTML syntax.
     *
     * @access   private
     * @return   string the XHTML syntax of the JS Events
     */
    function buildJSEvents()
    {
        $events = array();
        $xhtml = '';
        if (is_array($this->_events)) {
            foreach ($this->_events as $event) {
                $code = $event->getCode();
                if (substr($code, -1) != ';') {
                    $code = $code . ';';
                }

                $id = $event->getID();
                if (in_array($id, array_keys($events))) {
                    $code = str_replace('javascript:', '', $code);
                    $events[$id].= ' '.$code;
                } else {
                    if (isset($events[$id])) {
                        $events[$id].= $code;
                    } else {
                        $events[$id] = $code;
                    }
                }

                if ($event->needsFile()) {
                    $this->addFile($event->getSrc());
                }
            }

            //Let's make the XHTML..
            foreach ($events as $jsid => $code) {
                $xhtml.= " ".$jsid."=\"".$code."\"";
            }
        }

        return $xhtml;
    }

    /**
     * Rebuild the JS of the widget
     *
     * @access   public
     */
    function rebuildJS()
    {
        $this->buildJSEvents();
    }

    /**
     * Set the enabled status
     *
     * @param    boolean the status value
     * @access   public
     */
    function setEnabled($status = true)
    {
        $this->_isEnabled = $status;
    }

    /**
     * Add a new Event
     *
     * @param    object The event to add
     * @access   public
     */
    function addEvent($event)
    {
        if (is_string($event) && func_num_args() == 2) {
            $action = func_get_arg(1);
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                if (in_array($event, $this->_availableEvents)) {
                    $this->_events[] = new JSEvent($event, $action);
                } else {
                    die("[PIWI] - Sorry but you are not permitted to use ".$event." in this widget");
                }
            } else {
                $this->_events[] = new JSEvent($event, $action);
            }
        } elseif (is_object($event) && strtolower(get_class($event)) == 'jsevent') {
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                $id = $event->getID();
                if (in_array($id, $this->_availableEvents)) {
                    $this->_events[] =& $event;
                } else {
                    die("[PIWI] - Sorry but you are not permitted to use ".$id." in this widget");
                }
            } else {
                $this->_events[] =& $event;
            }
        } else {
            die("[PIWI] - Events should be objects");
        }
    }

    /**
     * Add a new validator
     *
     * @param    JSValidator $validator Validator object
     * @access   public
     */
    function addValidator($validator)
    {
        if (is_string($validator)) {
            $file = PIWI_PATH . '/JS/' . $validator . '.php';
            if (file_exists($file)) {
                require_once $file;
            } else {
                die("[PIWI] - Piwi validator doesn't exists");
            }

            $parameter = "";
            $numargs = func_num_args();
            if ($numargs > 1) {
                $arg_list = func_get_args();
                $arg_count = func_num_args();
                for ($i = 1; $i < $arg_count; $i++) {
                    if (defined($arg_list[$i])) {
                        $arg_list[$i] = constant($arg_list[$i]);
                    }
                    $parameter .= "\$arg_list[$i]";
                    if ($i != ($arg_count-1)) {
                        $parameter .= ',';
                    }
                }
            }
            $validatorObj = null;
            eval("\$validatorObj = new \$validator($parameter);");
            $this->_validators[] = $validatorObj;
        } elseif (is_object($validator) && strtolower(get_parent_class($validator)) == 'jsvalidator') {
            $this->_validators[] = $validator;
        } else {
            die("[PIWI] - Validators should be objects and must inherit from JSValidator class");
        }
    }

    /**
     * Returns the list of validators
     *
     * @return   array   List of validators
     * @access   public
     */
    function getValidators()
    {
        return $this->_validators;
    }
}
?>