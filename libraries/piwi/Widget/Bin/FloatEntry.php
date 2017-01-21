<?php
/**
 * FloatEntry.php - FloatEntry Class, the float entry
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Entry.php';

define('FLOATENTRY_REQ_PARAMS', 1);
class FloatEntry extends Entry
{
    /**
     * Public constructor
     *
     * @param    string Name of the entry
     * @param    string Value of the entry (optional)
     * @param    string Title of the entry (optional)
     * @access   public
     */
    function __construct($name, $value = '', $title = '')
    {
        $this->_name       = $name;
        $this->_value      = $value;
        $this->_title      = $title;
        $this->_isReadOnly = false;
        $this->_validate   = false;
        $this->_isRequired = false;
        $this->_type       = 'float';
        $this->_availableEvents = array ("tabindex", "accesskey", "onfocus", "onblur",
                                         "onselect", "onchange", "onclick", "ondblclick",
                                         "onmousedown", "onmouseup", "onmouseover", "onmousemove",
                                         "onmouseout", "onkeypress", "onkeydown", "onkeyup");
        parent::init();
    }

    /**
     * Set the type of the entry, override
     *
     * @param   string FloatEntry type
     * @access  public
     */
    function setType($type)
    {
        return;
    }
}
?>
