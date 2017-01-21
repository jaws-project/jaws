<?php
/**
 * Combo.php - Combo Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('COMBO_REQ_PARAMS', 1);
class Combo extends Bin
{
    /**
     * Combo data. The options of the Combo
     *
     * @var    array $_options
     * @access private
     * @see    addOption()
     */
    var $_options;

    /**
     * Is multiple
     * @var    boolean $_multiple
     * @access private
     * @see setMultiple()
     */
    var $_multiple;

    /**
     * Gives the 'required' status
     *
     * @var      bool $_isRequired
     * @access   private
     * @see      SetRequired()
     */
    var $_isRequired = false;

    /**
     * Odd/Even row color
     *
     * @var     array  $_colors
     * @see     setOddClass(), setEvenClass()
     * @access  private
     */
    var $_option_class = array('even' => 'piwi_option_even', 'odd' => 'piwi_option_odd');

    /**
     * Public constructor
     *
     * @param string Name of the combo
     * @param string Title of the combo
     * @param array  Data that will be used in the combo
     * @access  public
     */
    function __construct($name, $title = '', $options = array())
    {
        $this->_name     = $name;
        $this->_title    = $title;
        $this->_multiple = false;
        if (is_array($options) && !empty($options)) {
            $this->addOptions($options);
        } else {
            $this->_options = array();
        }

        $oddClass = Piwi::getVarConf('CLASS_ODD');
        if (empty($oddClass)) {
            $oddClass = 'piwi_option_odd';
        }
        $this->setOddClass($oddClass);

        $evenClass = Piwi::getVarConf('CLASS_EVEN');
        if (empty($evenClass)) {
            $evenClass = 'piwi_option_even';
        }
        $this->setEvenClass($evenClass);

        $this->_availableEvents = array("onchange", "onclick", "ondblclick", "onmousedown",
                                        "onmouseup", "onmouseover", "onmousemove",
                                        "onmouseout", "onkeypress", "onkeydown", "onkeyup");
        parent::init();
    }

    /**
     * Set the odd class
     *
     * @param  string  $class  Class name
     * @access public
     */
    function setOddClass($class)
    {
        $this->_option_class['odd'] = $class;
    }

    /**
     * Set the even class
     *
     * @param  string  $class  Class name
     * @access public
     */
    function setEvenClass($class)
    {
        $this->_option_class['even'] = $class;
    }

    /**
     * Add options from a indexed array
     *
     * @access  public
     * @param   array    $data  Array with data (key and values)
     * @param   mixed    $values Default value
     *
     */
    function addOptions($data, $values = null)
    {
        if (!is_array($data)) {
            die('[PIWI] $data has to be a array');
        }

        foreach ($data as $value => $name) {
            $this->addOption($name, $value);
        }

        if (isset($values)) {
            $this->setDefault($values);
        }
    }

    
    /**
     * Add a new Option to the combo
     *
     * @param   string $text  The text of the option
     * @param   string $value The value of the option
     * @param   boolean $isdisabled Sometimes a option can be disabled by default
     * @param   string $class The class of the option
     * @param   string $style The style of the option
     *
     * @access  public
     */
    function addOption($text, $value, $isdisabled = false, $class = '', $style = '')
    {
        require_once PIWI_PATH . '/Widget/Bin/ComboOption.php';
        $this->_options[$value] = new ComboOption($value, $text, null, false, $isdisabled, $class, $style);
    }

    /**
     * Set a key as the selected one
     *
     * @param   string $default Set the default key as the selected one
     * @access  public
     */
    function setDefault($key)
    {
        if (!is_array($key)) {
            if (isset($this->_options[$key])) {
                $this->_options[$key]->select();
            } else {
                if (is_array ($this->_options)) {
                    foreach ($this->_options as $option) {
                        $value = $option->getValue();
                        if ($option->getText() === $key) {
                            $this->_options[$value]->select();
                        } else {
                            $this->_options[$value]->select(false);
                        }
                    }
                }
            }
        } else {
            // Is an array
            if (($this->_multiple) && (count($key) > 0)) {
                foreach ($key as $k) {
                    $this->setDefault($k);
                }
            }
        }
    }

    /**
     * Set the value of the widget
     *
     * @aram    string   $value  Widget value
     * @access   public
     */
    function setValue($value)
    {
        $this->setDefault($value);
    }

    /**
     * Set multiple flag
     *
     * @param   boolean $flag
     * @access  public
     */
    function setMultiple($flag)
    {
        $this->_multiple = $flag;
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
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        if (!$this->_isEnabled) {
            $this->_PiwiXML->addAttribute('enabled', 'false');
        } else {
            $this->_PiwiXML->addAttribute('enabled', 'true');
        }

        if (count($this->_options) > 0) {
            $this->_PiwiXML->openElement('options');
            foreach ($this->_options as $option) {
                $this->_PiwiXML->openElement('option', true);

                $this->_PiwiXML->addAttribute('value', $option->getValue());
                $this->_PiwiXML->addAttribute('label', $option->getText());

                $class = $option->getClass();
                if (!empty($class)) {
                    $this->_PiwiXML->addAttribute('class', $class);
                }

                $style = $option->getStyle();
                if (!empty($style)) {
                    $this->_PiwiXML->addAttribute('style', $style);
                }

                $disabled = $option->isDisabled();
                if ($disabled) {
                    $this->_PiwiXML->addAttribute('enabled', 'false');
                }

                $selected = $option->isSelected();
                if ($selected) {
                    $this->_PiwiXML->addAttribute('selected', 'true');
                } else {
                    $this->_PiwiXML->addAttribute('selected', 'false');
                }

                $this->_PiwiXML->addText('adios');
                $this->_PiwiXML->closeElement('option');
            }
            $this->_PiwiXML->closeElement('options');

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
        $this->_XHTML = '<select class="form-control"';
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();

        if ($this->_multiple) {
            $this->_XHTML .= ' multiple="multiple"';
        }

        if (!empty($this->_size)) {
            $this->_XHTML .= " size=\"".$this->_size."\"";
        }

        if (!$this->_isEnabled) {
            $this->_XHTML .= " disabled=\"disabled\"";
        }

        if ($this->_isRequired) {
            $this->_XHTML .= ' required="required"';
        }

        $this->_XHTML .= ">\n";

        if (count($this->_options) > 0) {
            $option_class = $this->_option_class['even'];
            $colorcounter = 0;

            foreach ($this->_options as $option) {
                $this->_XHTML .= " <option value=\"".$option->getValue()."\"";

                $class = $option->getClass();
                $class .= (!empty($class)? ' ' : '') . $option_class;
                $this->_XHTML .= ' class="' . $class . '"';

                $style = $option->getStyle();
                if (!empty($style)) {
                    if (substr($style, -1) != ";") {
                        $style .= ';';
                    }
                    $this->_XHTML .= ' style="' . $style . '"';
                }

                $disabled = $option->isDisabled();
                if ($disabled) {
                    $this->_XHTML .= " disabled=\"disabled\"";
                }

                $selected = $option->isSelected();
                if ($selected) {
                    $this->_XHTML .= " selected=\"selected\"";
                }

                $title = $option->getTitle();
                if (!empty($title)) {
                    $this->_XHTML .= " title=\"".$title."\"";
                }

                $this->_XHTML .= ">";
                $this->_XHTML .= $option->getText();
                $this->_XHTML .= "</option>\n";

                if ($colorcounter % 2 == 0) {
                    $option_class = $this->_option_class['odd'];
                } else {
                    $option_class = $this->_option_class['even'];
                }
                $colorcounter++;
            }
        }
        $this->_XHTML .= "</select>";
    }
}
?>
