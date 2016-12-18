<?php
/*
 * Button.php - Button Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/Image.php';
require_once PIWI_PATH . '/Widget/Bin/ImageStocks.php';

define('BUTTON_REQ_PARAMS', 2);
class Button extends Bin
{
    /*
     * Stock that we will be used in the button
     *
     * @var      string $_Stock
     * @access   private
     * @see      SetStock ()
     */
    var $_stock;


    /*
     * Determinates if the button is a submit button or not
     *
     * @var      boolean $_IsSubmit
     * @access   private
     * @see      SetSubmit ()
     */
    var $_isSubmit;

    /*
     * Determinates if the button is a reset button or not
     *
     * @var      boolean $_IsReset
     * @access   private
     * @see      SetReset ()
     */
    var $_isReset;


    /*
     * Public constructor
     *
     * @param    string Name of the button
     * @param    string Value of the button
     * @param    string Stock that button wil use
     * @access   public
     */
    function Button($name, $value, $stock = '')
    {
        $this->_name     = $name;
        $this->_value    = $value;
        $this->_title    = '';
        $this->_isSubmit = false;
        $this->_isReset  = false;

        $this->_availableEvents = array("onfocus", "onblur", "onclick",
                                         "ondblclick", "onmousedown", "onmouseup",
                                         "onmouseover", "onmousemove", "onmouseout",
                                         "onkeypress", "onkeydown", "onkeyup");

        if (!empty($stock)) {
            $this->SetStock($stock);
        }

        parent::init();
    }


    /*
     * Set the submit status
     *
     * @access   public
     * @param    boolean the status value
     */
    function setSubmit($status = true)
    {
        $this->_isSubmit = $status;
    }

    /*
     * Set the reset status
     *
     * @access   public
     * @param    boolean the status value
     */
    function setReset($status = true)
    {
        $this->_isReset = $status;
    }

    /*
     * Set the stock image
     *
     * @access   public
     * @param    string the stock image
     */
    function setStock($stock)
    {
        if (!is_object ($stock)) {
            $this->_stock = new Image($stock);
            $this->_stock->setID($this->_name.'_stockimage');
        } else {
            $this->_stock = $stock;
        }
    }

    /*
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        if (!empty($this->_name)) {
            $this->_PiwiXML->addAttribute('label', $this->_name);
        }

        if ($this->_isSubmit) {
            $this->_PiwiXML->addAttribute('type', 'submit');
        } elseif ($this->_isReset) {
            $this->_PiwiXML->addAttribute('type', 'reset');
        } else {
            $this->_PiwiXML->addAttribute('type', 'normal');
        }

        if (!$this->_isEnabled) {
            $this->_PiwiXML->addAttribute('enabled', 'false');
        } else {
            $this->_PiwiXML->addAttribute('enabled', 'true');
        }

        if (!empty($this->_stock)) {
            $this->_PiwiXML->OpenElement('stock');
            $this->_PiwiXML->AddXML($this->_stock->GetPiwiXML(true));
            $this->_PiwiXML->CloseElement('stock');
        }

        $this->buildXMLEvents();
        $this->_PiwiXML->closeElement($this->getClassName());
    }

    /*
     * Construct the widget
     *
     * @access   private
     */
    function buildXHTML()
    {
        $this->_XHTML = '<button class="btn"';
        if ($this->_isSubmit) {
            $this->_XHTML .= " type=\"submit\"";
        } elseif ($this->_isReset) {
            $this->_XHTML .= " type=\"reset\"";
        } else {
            $this->_XHTML .= " type=\"button\"";
        }

        $this->_XHTML .= $this->buildBasicXHTML();

        if (!$this->_isEnabled) {
            $this->_XHTML .= " disabled=\"disabled\"";
        }

        $this->_XHTML .= $this->buildJSEvents();

        $this->_XHTML .= ">";

        if (!empty($this->_stock)) {
	    $this->_stock->setWidth(16);
  	    $this->_stock->setHeight(16);
            $this->_XHTML .= $this->_stock->get();
        }

        if (!empty($this->_stock) && !empty($this->_value)) {
            $this->_XHTML .= "&nbsp;";
        }

        if (!empty($this->_value)) {
            $this->_XHTML .= $this->_value;
        }

        $this->_XHTML .= "</button>\n";
    }
}
?>
