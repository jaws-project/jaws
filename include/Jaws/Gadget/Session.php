<?php
/**
 * Jaws Gadget Session
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2016 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Session
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
     * Fetch a session attribute
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns value of the attribute or Null if not exist
     */
    function fetch($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Session->GetAttribute("$gadget.Attributes.$name");
    }

    /**
     * Insert a session attribute
     *
     * @access  public
     * @param   string  $name       Session key name
     * @param   string  $value      Session key value
     * @param   bool    $trashed    Trashed attribute(eliminated end of current request)
     * @return  void
     */
    function insert($name, $value, $trashed = false)
    {
        $gadget = $this->gadget->name;
        return $GLOBALS['app']->Session->SetAttribute("$gadget.Attributes.$name", $value, $trashed);
    }

    /**
     * Update a session attribute
     *
     * @access  public
     * @param   string  $name       Session key name
     * @param   mixed   $value      Session key value
     * @param   bool    $trashed    Trashed attribute(eliminated end of current request)
     * @return  void
     */
    function update($name, $value, $trashed = false)
    {
        $gadget = $this->gadget->name;
        return $GLOBALS['app']->Session->SetAttribute("$gadget.Attributes.$name", $value, $trashed);
    }

    /**
     * Delete a session attribute
     *
     * @access  public
     * @param   string  $name       Session key name
     * @param   bool    $trashed    Trashed attribute(eliminated end of current request)
     * @return  bool    True
     */
    function delete($name, $trashed = false)
    {
        $gadget = $this->gadget->name;
        return $GLOBALS['app']->Session->DeleteAttribute("$gadget.Attributes.$name", $trashed);
    }

    /**
     * Push response data
     *
     * @access  public
     * @param   string  $text       Response text
     * @param   string  $resource   Response name
     * @param   string  $type       Response type
     * @param   mixed   $data       Response data
     * @return  void
     */
    function push($text, $resource = 'Resource', $type = RESPONSE_NOTICE, $data = null)
    {
        $gadget = $this->gadget->name;
        return $GLOBALS['app']->Session->PushResponse($text, "$gadget.Response.$resource", $type, $data);
    }

    /**
     * Returns the response data
     *
     * @access  public
     * @param   string  $resource   Resource's name
     * @param   bool    $remove     Optional remove popped response
     * @return  mixed   Response data, or Null if resource not found
     */
    function pop($resource = 'Resource', $remove = true)
    {
        $gadget = $this->gadget->name;
        return $GLOBALS['app']->Session->PopResponse("$gadget.Response.$resource", $remove);
    }

}