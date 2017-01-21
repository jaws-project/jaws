<?php
/**
 * JSComboValidator.php - Validate if a combo has at least one selected option
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSComboValidator extends JSValidator
{
    /**
     * Constructor
     *
     * @access public
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     */
    function __construct($field, $error)
    {
        parent::__construct($field, $error);

        $this->_code.= "if (form.".$this->_field.".type == \"select-one\" || ";
        $this->_code.= "form.".$this->_field.".type == \"select-multiple\" ||  ";
        $this->_code.= "form.".$this->_field.".type == \"select\") {\n";
        $this->_code.= "  if (form.".$this->_field.".selectedIndex == -1) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "  }\n";
        $this->_code.= "  if (form.".$this->_field.".options[".$this->_field.".selectedIndex].value == '') {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "  }\n";
        $this->_code.= "}\n\n";
    }
}
?>