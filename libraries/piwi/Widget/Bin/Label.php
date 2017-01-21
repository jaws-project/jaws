<?php
/**
 * Label.php - Label Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 * @author   Ali Fazelzadeh <afz@php.net>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('LABEL_REQ_PARAMS', 2);
class Label extends Bin
{
    /**
     * Specifies which element a label is bound to
     *
     * @var      mixed $_bound
     * @access   private
     */
    var $_bound;

    /**
     * Public constructor
     *
     * @param    string $label Text to use
     * @param    mixed  $bound Bound to element object or ID
     * @access   public
     */
    function __construct($label, $bound = '')
    {
        $this->_value = $label;
        $this->_bound = $bound;
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
        if (!empty($this->_bound)) {
            $bound = is_object($this->_bound)? $this->_bound->getID() : $this->_bound;
            $this->_XHTML.= ' for="' . $bound.'"';
        }

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML.= '>';
        $this->_XHTML.= $this->_value . '</label>';
    }

}