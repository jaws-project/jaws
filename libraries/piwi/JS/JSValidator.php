<?php
/**
 * JSValidator.php - Validator class. All validators _MUST_ inhereit from this class
 *
 * A good idea is to read the next file to know how to use 'validate' functions:
 *   http://www.dithered.com/javascript/form_validation/usage.html
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
class JSValidator
{
    /**
     * Javascript code to run
     *
     * @var    string  $_Code
     * @see    GetCode ()
     * @access protected
     */
    var $_code;

    /**
     * Field to validate
     *
     * @var    string  $_Field
     * @see    SetField (), GetField ()
     * @access protected
     */
    var $_field;

    /**
     * Error message to display when the 'validator' fails
     *
     * @var    string  $_Error
     * @see    SetError (), GetError ()
     * @access protected
     */
    var $_error;

    /**
     * Public constructor
     *
     * @param  string  $field  Field to validate
     * @param  string  $error  Error to print
     * @access public
     */
    function __construct($field, $error)
    {
        if (empty($field)) {
            die("[PIWI] - Field name is required");
        }
        $this->setField($field);
        $this->setError($error);
        //$this->_code = '';
    }

    /**
     * Sets the current field name
     *
     * @param   string  $field Field name
     * @access  public
     */
    function setField($field)
    {
        $this->_field = $field;
    }

    /**
     * Gets the current field name
     *
     * @return  string  Current field name
     * @access  public
     */
    function getField()
    {
        return $this->_field;
    }

    /**
     * Sets the error to display
     *
     * @param   string  $error Error
     * @access  public
     */
    function setError($error)
    {
        $this->_error = str_replace("'", "\'", $error);
    }

    /**
     * Gets the error
     *
     * @return  string  Error
     * @access  public
     */
    function getError()
    {
        return $this->_error;
    }

    /**
     * Gets the JavaScript code to run
     *
     * @return  string  Javascript Code
     * @access  public
     */
    function getCode()
    {
        return $this->_code;
    }
}
?>