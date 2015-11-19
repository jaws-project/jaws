<?php
/**
 * CheckButtons.php - CheckButtons Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/ComboOption.php';

define('CHECKBUTTONS_REQ_PARAMS', 1);
class CheckButtons extends Bin
{
    /**
     * Options used
     *
     * @var    array   $_options
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
     * Direction of the check buttons
     *
     * @var    array   $_direction
     * @access public
     * @see    getDirection(), setDirection()
     */
    var $_direction;

    /**
     * Number of columns
     *
     * @var int $_columns
     * @access public
     * @see setColumns()
     */
    var $_columns;

    /**
     * Public constructor
     *
     * @param  string  $name      Name that will be used in every option
     * @param  string  $direction Direction of the combo (default: horizontal)
     * @param  string  $title     Title of the checkbuttons
     * @access public
     */
    function CheckButtons($name, $direction = 'horizontal', $title = '')
    {
        $this->_name      = $name;
        $this->_direction = $direction;
        $this->_title     = $title;
        $this->_options   = array();
        $this->_multiple  = true;

        if ($this->_direction != 'horizontal' && $this->_direction != 'vertical') {
            $this->_direction = 'horizontal';
        }
        $this->_columns = 0;

        parent::init();
    }

    /**
     * Add a new option
     *
     * @param  string  $text  The text of the option
     * @param  string  $value The value of the option
     * @param  boolean $selected If the option should be selected
     * @param  boolean $isdisabled If the option should be disabled
     * @access public
     */
    function AddOption($text, $value, $id = null, $selected = false, $isdisabled = false)
    {
        $this->_options[$value] = new ComboOption($value, $text, $id, $selected, $isdisabled);
    }

    /**
     * Set a key as the selected oen
     *
     * @param   string $default Default key
     * @access  public
     */
    function setDefault($default)
    {
        if (!is_array($default)) {
            if (isset ($this->_options[$default])) {
                $this->_options[$default]->select();
            } else {
                if (count($this->_options) > 0) {
                    foreach ($this->_options as $option) {
                        if ($option->getText() == $default) {
                            $this->_options[$option->getValue()]->select();
                        }
                    }
                }
            }
        } else {
            foreach ($default as $d) {
                $this->setDefault($d);
            }
        }
    }

    /**
     * Set the direction
     *
     * @param  string  $direction Direction of the CheckButtons
     * @access public
     */
    function setDirection($direction)
    {
        $this->_direction = $direction;
    }

    /**
     * Get the direction
     *
     * @return  string The direction of the CheckButtons
     * @access  public
     */
    function getDirection()
    {
        return $this->_direction;
    }
    
    /**
     * Set the number of columns
     *
     * @param  int  $columns Number of columns if <=1 then a single column is used.
     * @access public
     */
    function setColumns($columns) 
    {
        $this->_columns = (int)$columns;
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
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML ();
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
     * @access  public
     */
    function buildXHTML()
    {
        $this->_XHTML = '';
        $n_elements = count($this->_options);

        if ($n_elements == 0) {
            return;
        }

        $in_columns = ($this->_columns != 0);
        if ($in_columns) {
            $this->_XHTML = "<table" . $this->buildBasicXHTML() . $this->buildJSEvents() . ">\n";
            $table = array();
            $c = 0; // Element counter.
        }

        $baseName = $this->_name;
        if ($this->_multiple) {
            $this->_name .= "[]";
        }

        foreach ($this->_options as $value => $option) {
            $item = '<input type="checkbox"';

            $this->_id = $option->getID();
            if (empty($this->_id)) {
                $this->_id = $baseName . '_' . $value;
            }

            $this->_value = $value;
            $item .= $this->buildBasicXHTML();
            $item .= $this->buildJSEvents();
            $this->_value = '';

            $disabled = $option->isDisabled();
            if ($disabled) {
                $item .= ' disabled="disabled"';
            }

            if ($option->isSelected()) {
                $item .= ' checked="checked"';
            }

            $item .= ' />';
            $lblText = $option->getText();
            if (strlen($lblText)) {
                $item .= '<label for="' . $this->getID() . '">' . $lblText . '</label>';
            }

            if (!$in_columns) {
                $this->_XHTML .= $item;

                if ($this->_direction == 'vertical') {
                    $this->_XHTML .= "<br />\n";
                } else {
                    $this->_XHTML .= "\n";
                }
            } else {
                $table[(int)(floor($c/$this->_columns))][] = "<td>" . $item . "</td>\n";
                $c ++;
            }
        }

        if ($in_columns) {
            foreach ($table as $tr) {
                $this->_XHTML .= "<tr>\n";
                foreach ($tr as $td) {
                    $this->_XHTML .= $td;
                }
                if (count($tr) < $this->_columns) {
                    $needed = $this->_columns - count($tr);
                    for ($i = 0; $i < $needed; $i++) {
                        $this->_XHTML .= "<td></td>\n";
                    }
                }
                $this->_XHTML .= "</tr>\n";
            }
            $this->_XHTML .= "</table>\n";
        }
    }

}