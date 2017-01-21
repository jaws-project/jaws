<?php
/**
 * JSSMTPValidator.php - Validate if the entry is a SMTP server (smtp.host.com)
 *
 * FROM: http://regexplib.com/REDetails.aspx?regexp_id=759
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSSMTPValidator extends JSValidator
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

        $regexp = "/^(smtp)\.([\w\-]+)\.[\w\-]{2,3}$/";
        $this->_code = "if (!form.".$this->_field.".value.match ({$regexp})) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>