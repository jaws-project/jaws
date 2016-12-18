<?php
/**
 * RadioButtons.php - RadioButtons Class
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

define('RADIOBUTTONS_REQ_PARAMS', 1);
class RadioButtons extends Bin
{
    /**
     * Options used in the radio
     *
     * @var    array   $_options
     * @access private
     * @see    addOption()
     */
    var $_options;

    /**
     * Direction of the radio buttons
     *
     * @var    array   $_direction
     * @access public
     * @see    getDirection(), setDirection()
     */
    var $_direction;

    /**
     * Public constructor
     *
     * @param  string  $name      Name that will be used in every option
     * @param  string  $direction Direction of the combo (default: horizontal)
     * @param  string  $title     Title of the radio buttons
     * @access public
     */
    function RadioButtons($name, $direction = 'horizontal', $title = '')
    {
        $this->_name      = $name;
        $this->_direction = $direction;
        $this->_title     = $title;
        $this->_options   = array();

        if ($this->_direction != 'horizontal' && $this->_direction != 'vertical') {
            $this->_direction = 'horizontal';
        }

        parent::init();
    }

    /**
     * Add a new option
     *
     * @param  string  $text  The text of the option
     * @param  string  $value The value of the option
     * @param  boolean $isdisabled If the option should be disabled
     * @access public
     */
    function addOption($text, $value, $isdisabled = false)
    {
        $this->_options[$value] = new ComboOption($value, $text, null, false, $isdisabled);
    }

    /**
     * Set a key as the selected oen
     *
     * @param   string $default Default key
     * @access  public
     */
    function setDefault($default)
    {
        if (isset($this->_options[$default])) {
            $this->_options[$default]->select();
        } else {
            if (count($this->_options) > 0) {
                foreach ($this->_options as $option) {
                    $value = $option->getValue();
                    if ($option->getText() == $default) {
                        $this->_options[$value]->select();
                    } else {
                        $this->_options[$value]->select(false);
                    }
                }
            }
        }
    }

    /**
     * Set the direction
     *
     * @param  string  $direction Direction of the RadioButtons
     * @access public
     */
    function setDirection($direction)
    {
        $this->_direction = $direction;
    }

    /**
     * Get the direction
     *
     * @return  string The direction of the RadioButtons
     * @access  public
     */
    function getDirection()
    {
        return $this->_direction;
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
        $baseName = $this->_name;
        $this->_name .= "[]";

        foreach ($this->_options as $value => $option) {
            $item = '<input class="form-check-input" type="radio"';

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
            $item .= '/> ';

            $this->_XHTML .= '<label for="' . $baseName . '_' . $value . '">';
            $this->_XHTML .= $item . $option->getText() . '</label>';

            if ($this->_direction == 'vertical') {
                $this->_XHTML .= "<br />\n";
            } else {
                $this->_XHTML .= "\n";
            }
        }
    }

}