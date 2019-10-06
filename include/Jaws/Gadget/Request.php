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
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

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
        $this->app = Jaws::getInstance();
    }

    /**
     * Fetches the data, filters it and then it returns it.
     *
     * @access  public
     * @param   mixed   $key            The key being fetched
     * @param   mixed   $method         Which super global is being fetched from, it can be an array
     * @param   bool    $filter         Returns filtered data or not
     * @param   bool    $xss_strip      Returns stripped html data tags/attributes
     * @param   bool    $json_decode    Decode JSON data or not
     * @return  mixed   Returns string or an array depending on the key, otherwise Null if key not exist
     */
    function fetch($key, $method = '', $filter = true, $xss_strip = false, $json_decode = false)
    {
        if ($this->gadget->name == $this->app->mainGadget) {
            return jaws()->request->fetch($key, $method, $filter, $xss_strip, $json_decode);
        } else {
            return is_scalar($key)? null : array_fill_keys($key, null);
        }
    }

    /**
     * Fetches the filtered data with out filter
     *
     * @access  public
     * @param   string  $method     Which super global is being fetched from
     * @param   bool    $filter     Returns filtered data
     * @param   bool    $xss_strip  Returns stripped html data tags/attributes
     * @return  array   Filtered Data array
     */
    function fetchAll($method = '', $filter = true, $xss_strip = false, $type_validate = true)
    {
        if ($this->gadget->name == $this->app->mainGadget) {
            return jaws()->request->fetchAll($method, $filter, $xss_strip, $type_validate);
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