<?php
/**
 * JSPasswordValidator.php - Validate if the entry is ready to be a password
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSPasswordValidator extends JSValidator
{
    /**
     * Constructor
     *
     * @access public
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     * @param  int     $min    Min number of characters
     * @param  int     $max    Max number of characters (default 100)
     */
    function __construct($field, $error, $min, $max = 100)
    {
        parent::__construct($field, $error);

        $this->_code = "if (form.".$this->_field.".value == '') {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
        $this->_code.= "if (!isValidLength(form.".$this->_field.".value, {$min}, {$max})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";

    }
}
?>