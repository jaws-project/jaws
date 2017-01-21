<?php
/*
 * HiddenEntry.php - HiddenEntry Class, the hidden entry
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Entry.php';

define('HIDDENENTRY_REQ_PARAMS', 1);
class HiddenEntry extends Entry
{
    /**
     * Public constructor
     *
     * @param    string Name of the entry
     * @param    string Value of the entry (optional)
     * @access   public
     */
    function __construct($name, $value = '')
    {
        $this->_name  = $name;
        $this->_value = $value;
        $this->_title = '';
        $this->_isReadOnly = false;
        $this->_validate = false;
        $this->_isRequired = false;
        $this->_type = 'hidden';
        $this->_availableEvents = array ("tabindex", "accesskey", "onfocus", "onblur",
                                         "onselect", "onchange", "onclick", "ondblclick",
                                         "onmousedown", "onmouseup", "onmouseover", "onmousemove",
                                         "onmouseout", "onkeypress", "onkeydown", "onkeyup");
        parent::init();
    }

    /**
     * Set the validate status, override
     *
     * @param   boolean status
     * @access  public
     */
    function setValidation($status = true)
    {
        return;
    }

    /**
     * Set the readonly status, override
     *
     * @param   boolean status
     * @access  public
     */
    function setReadOnly($status = true)
    {
        return;
    }

    /**
     * Set the readonly status, override
     *
     * @param   boolean status
     * @access  public
     */
    function setRequired($status = true)
    {
        return;
    }

    /**
     * Set the type of the entry, override
     *
     * @param   string HiddenEntry type
     * @access  public
     */
    function setType($type)
    {
        return;
    }
}
?>
