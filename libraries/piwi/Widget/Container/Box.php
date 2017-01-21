<?php
/**
 * Box.php - Box Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

class Box extends Container
{
    /**
     * Width of the table
     *
     * @var     string
     * @access  private
     * @see     setWidth(), getWidth()
     */
    var $_width = '';

    /**
     * Creates the box
     *
     * @access public
     */
    function __construct()
    {
        $this->setDirection('');
    }

    /**
     * Set the width
     *
     * @param  straing $width
     * @access public
     */
    function setWidth($width)
    {
        $this->_width = $width;
    }

    /**
     * Get the width
     *
     * @access public
     */
    function getWidth()
    {
        return $this->_width;
    }

    /**
     * Pack a widget to the start of the box
     *
     * @param  object $widget The widget to pack
     * @access public
     */
    function packStart(&$widget)
    {
        $this->_items[] =& $widget;
        //array_push($this->_items, &$widget);
    }

    /**
     * Pack a widget to the end of the box
     *
     * @param  object $widget The widget to pack
     * @access public
     */
    function packEnd(&$widget)
    {
        $total = count($this->_items);
        $this->_items[$total + 1] = $widget;
    }

}