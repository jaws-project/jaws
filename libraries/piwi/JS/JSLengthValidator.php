<?php
/**
 * JSLengthValidator.php - Validate if the entry has the length size or is between two numbers
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSLengthValidator extends JSValidator
{
    /**
     * Constructor
     *
     * @access public
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     * @param  int     $max    Max length
     * @param  int     $min    Min length (Default 0)
     */
    function __construct($field, $error, $max, $min = 0)
    {
        parent::__construct($field, $error);

        $this->_code = "if (!isValidLength(form.".$this->_field.".value, {$min}, {$max})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>