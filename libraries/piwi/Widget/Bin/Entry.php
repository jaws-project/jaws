<?php
/*
 * Entry.php - Entry Class, the text entry
 *
 * @version  $Id: $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('ENTRY_REQ_PARAMS', 1);
class Entry extends Bin
{
    /**
     * Max Length of the entry
     *
     * @var      string $_MaxLength
     * @access   private
     * @see      SetMaxLength ()
     */
    var $_maxLength;

    /**
     * Gives the 'readonly' status
     *
     * @var      bool $_isReadOnly
     * @access   private
     * @see      SetReadOnly ()
     */
    var $_isReadOnly = false;

    /**
     * Gives the 'required' status
     *
     * @var      bool $_isRequired
     * @access   private
     * @see      SetRequired()
     */
    var $_isRequired = false;

    /**
     * Gives the html5 'pattern' attribute
     *
     * @var      string $_pattern
     * @access   private
     * @see      SetPattern()
     */
    var $_pattern = false;

    /**
     * Gives the type of the entry. By default is: any
     *
     * @var      string $_Type
     * @access   private
     * @see      SetType ()
     */
    var $_type;

    /**
     * HTML autocomplete attribute
     *
     * @var      string $_autocomplete
     * @access   private
     * @see      setAutoComplete()
     */
    var $_autocomplete = '';

    /**
     *
     * @var    string $_validate
     * @access private
     */
    var $_validate;

    /**
     * Public constructor
     *
     * @param    string Name of the entry
     * @param    string Value of the entry (optional)
     * @param    string Title of the entry (optional)
     * @param    int    Length of the field (optional)
     * @param   boolean Set the readonly status (optional)
     * @access   public
     */
    function __construct($name, $value = '', $title = '', $length = '', $status = false)
    {
        $this->_name       = $name;
        $this->_value      = $value;
        $this->_title      = $title;
        $this->_maxLength  = $length;
        $this->_isReadOnly = $status;
        $this->_type       = 'any';
        $this->_availableEvents = array("tabindex", "accesskey", "onfocus", "onblur",
                                        "onselect", "onchange", "onclick", "ondblclick",
                                        "onmousedown", "onmouseup", "onmouseover", "onmousemove",
                                        "onmouseout", "onkeypress", "onkeydown", "onkeyup");
        parent::init();
    }

    /**
     * Set the Maxlength
     *
     * @param   int Length
     * @access  public
     */
    function setMaxLength($length)
    {
        $this->_maxLength = $length;
    }

    /**
     * Set the readonly status
     *
     * @param   boolean status
     * @access  public
     */
    function setReadOnly($status = true)
    {
        $this->_isReadOnly = $status;
    }

    /**
     * Set the required status
     *
     * @param   boolean status
     * @access  public
     */
    function setRequired($status = true)
    {
        $this->_isRequired = $status;
    }

    /**
     * Set the html5 pattern attribute
     *
     * @param   string pattern
     * @access  public
     */
    function SetPattern($pattern = '')
    {
        $this->_pattern = $pattern;
    }

    /*
     * Set the type of the entry
     *
     * @param   string Entry type
     * @access  public
     */
    function setType($type)
    {
        if (in_array($type, array('text', 'any', 'file', 'password', 'hidden'))) {
            $this->_type = $type;
        } else {
            $this->_type = 'any';
        }
    }

    /*
     * Set HTML autocomplete attribute
     *
     * @param   string  $state
     * @access  public
     */
    function setAutoComplete($state)
    {
        $this->_autocomplete = $state? 'on' : 'off';
    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        //Write type, only if we are the Entry class, if not, there's no case.
        $classname = $this->getClassName();
        if ($classname == 'entry') {
            if (!empty($this->_type)) {
                $this->_PiwiXML->addAttribute('type', $this->_type);
            } else {
                $this->_PiwiXML->addAttribute('type', 'any');
            }
        }

        if (!$this->_isEnabled) {
            $this->_PiwiXML->addAttribute('enabled', 'false');
        }

        if ($this->_isReadOnly) {
            $this->_PiwiXML->addAttribute('readonly', 'true');
        }

        if (!empty($this->_maxLength) && is_numeric($this->_maxLength)) {
            $this->_PiwiXML->addAttribute('maxlength', $this->_maxLength);
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
        $this->_XHTML = '<input';
        if ($this->_type == 'password') {
            $this->_XHTML .= ' class="form-control '.$this->_class.'" type="password"';
        } elseif ($this->_type == 'hidden') {
            $this->_XHTML .= ' type="hidden"';
        } elseif ($this->_type == 'file') {
            $this->_XHTML .= ' class="form-control-file '.$this->_class.'" type="file"';
        } else {
            $this->_XHTML .= ' class="form-control '.$this->_class.'" type="text"';
            if ($this->_isReadOnly) {
                $this->_XHTML .= ' readonly="readonly"';
            }
        }

        if (!$this->_isEnabled) {
            $this->_XHTML .= ' disabled="disabled"';
        }

        if (!empty($this->_size)) {
            $this->_XHTML .= " size=\"".$this->_size."\"";
        }

        if (!empty($this->_maxLength) && is_numeric($this->_maxLength)) {
            $this->_XHTML .= " maxlength=\"".$this->_maxLength."\"";
        }

        if ($this->_isRequired) {
            $this->_XHTML .= ' required="required"';
        }

        if ($this->_pattern) {
            $this->_XHTML .= ' pattern="'. $this->_pattern . '"';
        }


        if (!empty($this->_autocomplete)) {
        $this->_XHTML .= " autocomplete='{$this->_autocomplete}'";
        }

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();

        $this->_XHTML.= ' />';
    }

}