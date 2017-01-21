<?php
/**
 * JSISBNValidator.php - Validate if the entry is an ISBN code
 *
 * FROM: http://regexplib.com/REDetails.aspx?regexp_id=463
 *
 * EXAMPLE:
 *  [ISBN 0 93028 923 4], [ISBN 1-56389-668-0], [ISBN 1-56389-016-X]
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSISBNValidator extends JSValidator
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

        $regexp = "/^ISBN\x20(?=.{13}$)\d{1,5}([- ])\d{1,7}\1\d{1,6}\1(\d|X)$/";
        $this->_code = "if (!form.".$this->_field.".value.match ({$regexp})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>