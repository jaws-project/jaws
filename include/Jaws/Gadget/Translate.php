<?php
/**
 * Jaws Gadget Translate
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Translate
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
    function __construct($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Insert a new translation statement
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   string  $gadget     (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function insert($key_name, $key_value, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return Jaws_Translate::getInstance()->AddTranslation(
            $gadget,
            strtoupper($gadget.'_'.$key_name),
            $key_value,
            JAWS_COMPONENT_GADGET
        );
    }

}