<?php
/**
 * Button.php - Image Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('IMAGE_REQ_PARAMS', 1);
class Image extends Bin
{
    /**
     * Img SRC
     *
     * @var    string $_src
     * @access private
     * @see    setSrc
     */
    var $_src;

    /**
     * Img Border
     *
     * @var    string $_border
     * @access private
     * @see    setBorder
     */
    var $_border;

    /**
     * Img Alt text
     *
     * @var    string $_alt;
     * @access private
     * @see    setAlt
     */
    var $_alt;

    /**
     * Height of the image
     *
     * @var    string $_height;
     * @access private
     * @see    setHeight
     */
    var $_height;

    /**
     * Width of the image
     *
     * @var    string $_width;
     * @access private
     * @see    setWidth
     */
    var $_width;

    /**
     * Public constructor
     *
     * @param   string The Src of the image
     * @param   string Alternate text of the image
     * @param   string Border of the image
     * @access  public
     */
    function __construct($src, $alt = '', $border = '0', $width = null, $height = null)
    {
        $this->_src    = (substr($src,0,1) == '?' ||
                          substr($src,0,7) == 'http://' ||
                          substr($src,0,8) == 'https://')? $src : Piwi::getVarConf('LINK_PRIFIX') . $src;
        $this->_alt    = $alt;
        $this->_border = $border;
        $this->_width  = $width;
        $this->_height = $height;

        parent::init();

    }

    /**
     * Set the image height
     *
     * @access   public
     * @param    string Height of the image
     */
    function setHeight($height)
    {
        $this->_height = $height;
    }

    /**
     * Set the image width
     *
     * @access   public
     * @param    string Width of the image
     */
    function setWidth($width)
    {
        $this->_width = $width;
    }

    /**
     * Set the image border
     *
     * @access   public
     * @param    string Border of the image
     */
    function setBorder($border)
    {
        $this->_border = $border;
    }

    /**
     * Set the image alternate text
     *
     * @access   public
     * @param    string Alternate text
     */
    function setAlt($alt)
    {
        $this->_alt = $alt;
    }

    /**
     * Set the image src
     *
     * @access   public
     * @param    string src of the image
     */
    function setSrc($src)
    {
        $this->_src = (substr($src,0,1) == '?' ||
                       substr($src,0,7) == 'http://' ||
                       substr($src,0,8) == 'https://')? $src : Piwi::getVarConf('LINK_PRIFIX') . $src;
    }

    /**
     * Build the piwiXML data. Main class does nothing
     *
     * @access    public
     */
    function buildPiwiXML ()
    {
        $this->buildBasicPiwiXML();

        $this->_PiwiXML->addAttribute('src', $this->_src);

        if (!is_null($this->_height)) {
            $this->_PiwiXML->addAttribute('height', $this->_height . 'px');
        }

        if (!is_null($this->_width)) {
            $this->_PiwiXML->addAttribute('width', $this->_width . 'px');
        }

        $this->_PiwiXML->addAttribute('text', $this->_alt);
        $this->_PiwiXML->addAttribute('border', $this->_border);

        $this->buildXMLEvents();
        $this->_PiwiXML->closeElement('image');
    }

    /**
     * Construct the widget
     *
     * @access   private
     */
    function buildXHTML()
    {
        $this->_XHTML  = '<img';
        $this->_XHTML .= " src=\"".$this->_src."\"";
        if (!empty($this->_border)) {
            $this->_XHTML .= " border=\"".$this->_border."\"";
        }
        $this->_XHTML .= " alt=\"".$this->_alt."\"";

        if (!is_null($this->_height)) {
            $this->_XHTML .= " height=\"".$this->_height."\"";
        }

        if (!is_null($this->_width)) {
            $this->_XHTML .= " width=\"".$this->_width."\"";
        }

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML .= ' />';
    }

}
