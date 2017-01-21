<?php
/**
 * IFrame.php - IFrame Class
 *
 * @version  $Id $
 * @author   Mohsen Khahani <mkhahani@gmail.com>
 *
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('IFRAME_REQ_PARAMS', 1);
class IFrame extends Bin
{
    /**
     * IFrame SRC
     *
     * @var    string $_src
     * @access private
     * @see    setSrc
     */
    var $_src;

    /**
     * IFrame Border
     *
     * @var    string $_border
     * @access private
     * @see    setBorder
     */
    var $_border;

    /**
     * IFrame Scrolling type
     *
     * @var    string $_scrolling;
     * @access private
     * @see    setScrolling
     */
    var $_scrolling;

    /**
     * Height of the iframe
     *
     * @var    string $_height;
     * @access private
     * @see    setHeight
     */
    var $_height;

    /**
     * Width of the iframe
     *
     * @var    string $_width;
     * @access private
     * @see    setWidth
     */
    var $_width;

    /**
     * Public constructor
     *
     * @param   string name
     * @param   string src
     * @param   int frameborder {0, 1}  optional)
     * @param   string scrolling type {auto, horizontal, vertical} (optional)
     * @param   int width (optional)
     * @param   int height (optional)
     * @access  public
     */
    function __construct($name, $src, $border = 0, $scrolling = 'auto', $width = null, $height = null)
    {
        $this->_name        = $name;
        $this->_src         = (substr($src,0,1) == '?' ||
                               substr($src,0,7) == 'http://' ||
                               substr($src,0,8) == 'https://')? $src : Piwi::getVarConf('LINK_PRIFIX') . $src;
        $this->_border      = $border;
        $this->_scrolling   = $scrolling;
        $this->_width       = $width;
        $this->_height      = $height;

        parent::init();

    }

    /**
     * Sets the iframe height
     *
     * @access   public
     * @param    int Height of the iframe
     */
    function setHeight($height)
    {
        $this->_height = $height;
    }

    /**
     * Sets the iframe width
     *
     * @access   public
     * @param    int Width of the iframe
     */
    function setWidth($width)
    {
        $this->_width = $width;
    }

    /**
     * Sets the iframe frameborder
     *
     * @access   public
     * @param    int frameborde of the iframe
     */
    function setBorder($border)
    {
        $this->_border = $border;
    }

    /**
     * Sets the iframe scrolling type
     *
     * @access   public
     * @param    string scrolling
     */
    function setScrolling($scrolling)
    {
        $this->_scrolling = $scrolling;
    }

    /**
     * Sets the iframe src
     *
     * @access   public
     * @param    string src of the iframe
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

        $this->_PiwiXML->addAttribute('frameborder', $this->_border);
        $this->_PiwiXML->addAttribute('scrolling', $this->_scrolling);

        $this->buildXMLEvents();
        $this->_PiwiXML->closeElement('iframe');
    }

    /**
     * Construct the widget
     *
     * @access   private
     */
    function buildXHTML()
    {
        $this->_XHTML  = '<iframe';
        $this->_XHTML .= " id=\"".$this->getID()."\"";
        $this->_XHTML .= " src=\"".$this->_src."\"";
        $this->_XHTML .= " frameborder=\"".$this->_border."\"";
        $this->_XHTML .= " scrolling=\"".$this->_scrolling."\"";

        if (!is_null($this->_height)) {
            $this->_XHTML .= " height=\"".$this->_height."\"";
        }

        if (!is_null($this->_width)) {
            $this->_XHTML .= " width=\"".$this->_width."\"";
        }

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML .= '></iframe>';
    }
}