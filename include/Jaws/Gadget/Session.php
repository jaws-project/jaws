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
    function Jaws_Gadget_Session($gadget)
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

}