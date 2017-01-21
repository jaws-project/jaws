<?php
/**
 * JSPasswordMatchValidator.php - Validate if the entry is equal to another password
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSPasswordMatchValidator extends JSValidator
{
    /**
     * Constructor
     *
     * @access public
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     * @param  string  $comparefield Field to compare the password entry
     */
    function __construct($field, $error, $comparefield)
    {
        parent::__construct($field, $error);

        $this->_code = "if (form.".$this->_field.".value == form.".$comparefield.".value) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";

    }
}
?>