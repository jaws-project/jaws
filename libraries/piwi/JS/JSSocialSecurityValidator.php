<?php
/**
 * JSSocialSecurityValidator.php - Validate if the entry is a social security number (US).
 *
 * Example:
 * [053-27-0293], [770-29-2012], [063-71-9123]
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSSocialSecurityValidator extends JSValidator
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

        $regexp = "/^(?=((0[1-9]0)|([1-7][1-7]\d)|(00[1-9])|(0[1-9][1-9]))-(?=(([1-9]0)|(0[1-9])|([1-9][1-9]))-(?=((\d{3}[1-9])$|([1-9]\d{3})$|(\d[1-9]\d{2})$|(\d{2}[1-9]\d)$))))/";
        $this->_code = "if (!form.".$this->_field.".value.match ({$regexp})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>