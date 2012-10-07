<?php
/**
 * Label.php - Label Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('LABEL_REQ_PARAMS', 2);
class Label extends Bin
{
    /**
     * Object that Label is pointing
     *
     * @var      object $_Object
     * @access   private
     */
    var $_object;

    /**
     * Public constructor
     *
     * @param    string  $label Text to use
     * @param    object  $obj   Object to use
     * @access   public
     */
    function Label($label, $obj)
    {
        $this->_text   = $label;
        $this->_object = $obj;
        parent::init();
    }

    /**
     * Build XHTML data
     *
     * @access   public
     */
    function buildXHTML()
    {
        $this->_XHTML = '<label';
        if (is_object($this->_object)) {
            $this->_XHTML.= ' for="' . $this->_object->getID().'"';
        }
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML.= '>';
        $this->_XHTML.= $this->_text . '</label>';
    }

}