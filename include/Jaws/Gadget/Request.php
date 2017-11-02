<?php
/**
 * Jaws Gadget Request
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Request
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
     * Fetches the data, filters it and then it returns it.
     *
     * @access  public
     * @param   mixed   $key            The key being fetched
     * @param   mixed   $types          Which super global is being fetched from, it can be an array
     * @param   bool    $filter         Returns filtered data or not
     * @param   bool    $json_decode    Decode JSON data or not
     * @return  mixed   Returns string or an array depending on the key, otherwise Null if key not exist
     */
    function fetch($key, $types = '', $filter = true, $json_decode = false)
    {
        if ($this->gadget->name == $GLOBALS['app']->mainGadget) {
            return jaws()->request->fetch($key, $types, $filter, $json_decode);
        } else {
            return is_scalar($key)? null : array_fill_keys($key, null);
        }
    }

    /**
     * Fetches the filtered data with out filter
     *
     * @access  public
     * @param   string  $type   Which super global is being fetched from
     * @param   bool    $filter Returns filtered data or not
     * @return  array   Filtered Data array
     */
    function fetchAll($type = '', $filter = true)
    {
        if ($this->gadget->name == $GLOBALS['app']->mainGadget) {
            return jaws()->request->fetchAll($type, $filter);
        } else {
            return array();
        }
    }


    /**
     * Gets request method type
     *
     * @access  public
     * @return  string  Request method type(get/post)
     */
    function method()
    {
        return jaws()->request->method();
    }

}