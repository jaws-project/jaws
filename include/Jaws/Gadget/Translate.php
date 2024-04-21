<?php
/**
 * Jaws Gadget Translate
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Translate extends Jaws_Gadget_Class
{
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
            strtoupper($key_name),
            $key_value,
            JAWS_COMPONENT_GADGET
        );
    }

}