<?php
/**
 * TextArea.php - TextArea Class, the text entry
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('TEXTAREA_REQ_PARAMS', 1);
class TextArea extends Bin
{
    /**
     * Number of rows
     *
     * @var      string $_rows
     * @access   private
     * @see      setRows
     */
    var $_rows;

    /**
     * Number of columns
     *
     * @var      string $_columns
     * @access   private
     * @see      setColumns
     */
    var $_columns;

    /**
     * Readonly status
     *
     * @var      string $_isReadOnly
     * @access   private
     * @see      setReadOnly
     */
    var $_isReadOnly;

    /**
     * Is it required?
     *
     * @var      string $_isRequired
     * @access   private
     * @see      setRequired, isRequired
     */
    var $_isRequired;

    /**
     * Public constructor
     *
     * @param    string  Name of the entry
     * @param    string  Value of the entry (optional)
     * @param    string  Title of the textarea
     * @param    int     Number of rows
     * @param    int     Number of columns
     * @access   public
     */
    function TextArea($name, $value = '', $title = '', $rows = 0, $cols = 0)
    {
        $this->_name       = $name;
        $this->_value      = $value;
        $this->_title      = $title;
        $this->_rows       = $rows;
        $this->_cols       = $cols;
        $this->_isReadOnly = false;
        $this->_isEnabled  = true;
        $this->_isRequired = false;
        $this->_availableEvents = array("onfocus", "onblur", "onselect",
                                         "onchange", "onclick", "ondblclick",
                                         "onmousedown", "onmouseup", "onmouseover",
                                         "onmousemove", "onmouseout", "onkeypress", "onkeydown",
                                         "onkeyup");
        parent::init();
    }

    /**
     * Set the row size
     *
     * @param   int Rows
     * @access  public
     */
    function setRows($rows)
    {
        $this->_rows = $rows;
    }

    /**
     * Set the column size
     *
     * @param   int Columns
     * @access  public
     */
    function setColumns($columns)
    {
        $this->_cols = $columns;
    }

    /**
     * Set the readonly status
     *
     * @param   boolean status
     * @access  public
     */
    function setReadOnly($status)
    {
        $this->_isReadOnly = $status;
    }

    /**
     * Set the required status
     *
     * @param   boolean status
     * @access  public
     */
    function setRequired($status)
    {
        $this->_isRequired = $status;
    }

    /**
     * Get the required status
     *
     * @return  boolean
     * @access  public
     */
    function isRequired()
    {
        return $this->_isRequired;
    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $value = $this->_value;
        $this->_value = '';

        $this->buildBasicPiwiXML();

        $this->_PiwiXML->addAttribute('rows', $this->_rows);
        $this->_PiwiXML->addAttribute('cols', $this->_cols);

        if (!$this->_isEnabled) {
            $this->_PiwiXML->addAttribute('enabled', 'false');
        }

        if ($this->_isReadonly) {
            $this->_PiwiXML->addAttribute('readonly', 'true');
        }

        if ($this->_isRequired) {
            $this->_PiwiXML->addAttribute('required', 'true');
        }

        $this->_value = $value;
        if (!empty($this->_value)) {
            $this->_PiwiXML->addText($this->_value, true);
        }

        $this->buildXMLEvents();
        $this->_PiwiXML->closeElement($this->getClassName());
    }

    /**
     * Build the XHTML data
     *
     * @access  private
     */
    function buildXHTML()
    {
        $this->_XHTML  = '<textarea class="form-control"';
        if (!empty($this->_rows)) {
            $this->_XHTML .= " rows='{$this->_rows}'";
        }
        if (!empty($this->_cols)) {
            $this->_XHTML .= " cols='{$this->_cols}'";
        }

        if (!$this->_isEnabled) {
            $this->_XHTML .= ' disabled="disabled"';
        }

        if ($this->_isReadOnly) {
            $this->_XHTML .= ' readonly="readonly"';
        }

        //hide the value until we build the basic XHTML
        $value = str_replace(array('\\', "\r\n", "\n", "\r", '"', '</script>'),
                             array('\\\\', '\n', '\n', '\n', '\"', '\x3C/script\x3E'),
                             $this->_value);
        $this->_value = '';

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();

        $this->_XHTML .= '>';
        $this->_XHTML .= "</textarea>\n";
        $this->_XHTML .=  "<script type=\"text/javascript\">\n";
        $this->_XHTML .= "textarea = document.getElementById('{$this->_id}');\n";
        $this->_XHTML .= "if (textarea == null) {\n";
        $this->_XHTML .= "textarea = document.getElementsByName('{$this->_name}')[0];\n";
        $this->_XHTML .= "}\n";
        $this->_XHTML .= "textarea.value = \"".$value."\";\n";
        $this->_XHTML .= "</script>\n";
    }

}