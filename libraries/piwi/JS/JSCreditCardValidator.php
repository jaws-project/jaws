<?php
/**
 * JSCreditCardValidator.php - Validate if the entry is a credit card
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/JS/JSValidator.php';

class JSCreditCardValidator extends JSValidator
{
    /**
     * Constructor
     *
     * @access public
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     * @param  string  $cardtype CardType field to use as reference
     */
    function __construct($field, $error, $cardtype)
    {
        parent::_construct($field, $error, $cardtype);

        if (empty($cardtype)) {
            die("[PIWI] - CreditCard Validator requires the credit card field name as third argument");
         }

        $this->_code = "if (!isValidCreditCard(form.".$this->_field.".value, form.".$cardtype.".value) {\n";
        $this->_code.= "   alert ('".$this->_error."');\n";
        $this->_code.= "   form.".$this->_field.".focus ();\n";
        $this->_code.= "   return false;\n";
        $this->_code.= "}\n\n";
    }
}
?>