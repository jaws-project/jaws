<?php
/**
 * ComboOption.php - ComboOption class struct
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */

define('COMBOOPTION_REQ_PARAMS', 2);
class ComboOption
{
    /**
     * Will have the value of the Option
     *
     * @var     string $_value
     * @access  private
     */
    var $_value;

    /**
     * Will have the class of the Option
     *
     * @var     string $_class
     * @see     getClass()
     * @access  private
     */
    var $_class;

    /**
     * Will have the id of the Option
     *
     * @var     string $_id
     * @see     getID()
     * @access  private
     */
    var $_id;

    /**
     * Will have the style of the Option
     *
     * @var     string $_style
     * @see     getStyle()
     * @access  private
     */
    var $_style;

    /**
     * Will have the text of the Option
     *
     * @var     string $_style
     * @see     getText()
     * @access  private
     */
    var $_text;

    /**
     * The selected status of the option
     *
     * @var     boolean $_isSelected
     * @see     select(), isSelected()
     * @access  private
     */
    var $_isSelected;

    /**
     * The disabled status of the option
     *
     * @var     boolean $_isDisabled
     * @see     disable(), isDisabled()
     * @access  private
     */
    var $_isDisabled;

    /**
     *
     * Title of the option
     *
     * @var    string $_title
     * @access private
     * @see    SetTitle (), GetTitle ()
     */
    var $_title;

    /**
     * Private constructor
     *
     * @param   string  $value Value of the Option
     * @param   string  $text  Text of the Option
     * @param   boolean $isselected Selected status
     * @param   boolean $isdisabled Disabled status
     * @param   string  $class Class of the Option
     * @param   string  $style Style of the Option
     * @access  public
     */
    function __construct($value, $text, $id = null, $isselected = false, $isdisabled = false, $class = '', $style = '')
    {
        $this->_value      = $value;
        $this->_text       = $text;
        $this->_id         = $id;
        $this->_isSelected = $isselected;
        $this->_isDisabled = $isdisabled;
        $this->_class      = $class;
        $this->_style      = $style;
        $this->_title      = null;
    }

    /**
     * Disable the ComboOption
     *
     * @param   boolean $status Selected status
     * @access  public
     */
    function select($status = true)
    {
        $this->_isSelected = $status;
    }

    /**
     * Disable the ComboOption
     *
     * @param   boolean $status Disabled status
     * @access  public
     */
    function disable($status = true)
    {
        $this->_isDisabled = $status;
    }

    /**
     * Return the value of the ComboOption
     *
     * @access  public
     * @return  string  The Value of the ComboOption
     */
    function getValue()
    {
        return $this->_value;
    }

    /**
     * Return the text of the ComboOption
     *
     * @access  public
     * @return  string  The Text of the ComboOption
     */
    function getText()
    {
        return $this->_text;
    }

    /**
     * Return the style of the ComboOption
     *
     * @access  public
     * @return  string  The Style of the ComboOption
     */
    function getStyle()
    {
        return $this->_style;
    }

    /**
     * Return the class of the ComboOption
     *
     * @access  public
     * @return  string  The Class of the ComboOption
     */
    function getClass()
    {
        return $this->_class;
    }

    /**
     * Return the id of the ComboOption
     *
     * @access  public
     * @return  string  The ID of the ComboOption
     */
    function getID()
    {
        return $this->_id;
    }

    /**
     * Return the disabled status
     *
     * @access  public
     * @return  boolean  The Disabled status
     */
    function isDisabled()
    {
        return $this->_isDisabled;
    }

    /**
     * Return the disabled status
     *
     * @access  public
     * @return  boolean  The Selected status
     */
    function isSelected()
    {
        return $this->_isSelected;
    }

    /**
     * Return class name 'combooption'
     *
     * @access public
     */
    function getClassName()
    {
        return 'combooption';
    }

    /**
     * Get the title of the combo option
     *
     * @access   public
     */
    function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set the title
     *
     * @access    public
     * @param string Title to use
     */
    function setTitle($title)
    {
        $this->_title = $title;
    }

}