<?php
/**
 * PasswordEntry.php - PasswordEntry Class, the password entry
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Entry.php';

define('PASSWORDENTRY_REQ_PARAMS', 1);
class PasswordEntry extends Entry
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
        $this->_name  = $name;
        $this->_value = $value;
        $this->_title = $title;
        $this->_isReadOnly = false;
        $this->_type = 'password';
        $this->_availableEvents = array("tabindex", "accesskey", "onfocus", "onblur",
                                         "onselect", "onchange", "onclick", "ondblclick",
                                         "onmousedown", "onmouseup", "onmouseover", "onmousemove",
                                         "onmouseout", "onkeypress", "onkeydown", "onkeyup");
        parent::init();
    }

    /**
     * Set the Maxlength, override
     *
     * @param   int Length
     * @access  public
     */
    function setMaxLength($length)
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
     * Set the type of the entry, override
     *
     * @param   string PasswordEntry type
     * @access  public
     */
    function setType($type)
    {
        return;
    }
}
?>
