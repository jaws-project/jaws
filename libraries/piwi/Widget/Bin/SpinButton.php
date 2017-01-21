<?php
/**
 * SpinButton.php - SpinButton Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/Combo.php';

define('SPINBUTTON_REQ_PARAMS', 1);
class SpinButton extends Combo
{
    /**
     * Size of the spin
     *
     * @var    array $_spinSize
     * @access private
     * @see    getSpinSize(), setSpinSize()
     */
    var $_spinSize;

    /**
     * Step
     *
     * @var int $_spinStep
     * @access private
     * @see setSpinStep(), getSpinStep()
     */
    var $_spinStep;

    /**
     * Public constructor
     *
     * @param   string Name of the spin button
     * @param   int    How many rows does the spin will have?
     * @param   string Title of the Spin
     * @access  public
     */
    function __construct($name, $size = 20, $title = '', $step = 1)
    {
        $this->_name     = $name;
        $this->_spinSize = $size;
        $this->_title    = $title;
        $this->_spinStep = $step;
        $this->_availableEvents = array ("onchange", "onclick", "ondblclick", "onmousedown",
                                        "onmouseup", "onmouseover", "onmousemove",
                                        "onmouseout", "onkeypress", "onkeydown", "onkeyup");

        $this->setSpinSize($this->_spinSize);
        parent::init();
    }

    /**
     * Set the spin size
     *
     * @access   public
     * @param    int    $size Spin Size
     */
    function setSpinSize($size = 20)
    {
        $this->_spinSize = $size;
        $this->_options = array();

        for ($i = 0; $i <= $this->_spinSize; $i+= $this->_spinStep) {
            $this->addOption($i, $i);
        }
    }

    /**
     * Get the spin size
     *
     * @access   public
     * @return   int    $size Spin Size
     */
    function getSpinSize()
    {
        return $this->_spinSize;
    }

    /**
     * Set the spin step
     *
     * @access   public
     * @param    int    $size Spin Step
     */
    function setSpinStep($step = 1)
    {
        $this->_spinStep = $step;
    }

    /**
     * Get the spin step
     *
     * @access   public
     * @return   int    $size Spin Step
     */
    function getSpinStep()
    {
        return $this->_spinStep;
    }
}
?>
