<?php
/**
 * JSStrongPasswordValidator.php - Validate if the entry is a secure password.
 *
 * "Password expresion that requires one lower case letter, one upper
 *  case letter, one digit, 6-13 length, and no spaces.".
 *
 * FROM: http://regexplib.com/REDetails.aspx?regexp_id=157
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSStrongPasswordValidator extends JSValidator
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

        $regexp = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{4,8}$/";
        $this->_code = "if (!form.".$this->_field.".value.match ({$regexp})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>