<?php
/**
 * Link.php - Link Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2006
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('LINK_REQ_PARAMS', 2);
class Link extends Bin
{
    /**
     * Link reference
     *
     * @var    string 
     * @access private
     * @see    setLink
     */
    var $_link;

    /**
     * Link target
     *
     * @var    string 
     * @access private
     * @see    setTarget
     */
    var $_target;

    /**
     * Link text
     *
     * @var    string 
     * @access private
     * @see    setText
     */
    var $_text;

    /**
     * Img SRC
     *
     * @var    string
     * @access private
     * @see    setImage
     */
    var $_image;

    /**
     * Hide the text an only use image?
     *
     * @var    string $_alt;
     * @access private
     * @see    setAlt
     */
    var $_hideText = false;

    /**
     * Public constructor
     *
     * @param   string $text  Link Text
     * @param   string $href  Link Reference
     * @param   string $image Link Image
     * @access  public
     */
    function __construct($text, $href, $image = '')
    {
        $this->_text  = $text;
        $this->_link  = $href;
        $this->_image = (substr($image,0,1) == '?' ||
                         substr($image,0,7) == 'http://' ||
                         substr($image,0,8) == 'https://')? $image : Piwi::getVarConf('LINK_PRIFIX') . $image;
        if (!empty($this->_image)) {
            $this->_hideText = true;
        }
        parent::init();
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
     * Set the image 
     *
     * @access   public
     * @param    string $image SRC of image (or STOCK)
     */
    function setImage($image)
    {
        $this->_image = (substr($image,0,1) == '?' ||
                         substr($image,0,7) == 'http://' ||
                         substr($image,0,8) == 'https://')? $image : Piwi::getVarConf('LINK_PRIFIX') . $image;
        if (!empty($this->_image)) {
            $this->_hideText = true;
        }
    }

    /**
     * Set the text link 
     *
     * @access   public
     * @param    string $text Text of link
     */
    function setText($text)
    {
        $this->_text = $text;
    }

    /**
     * Set the link reference 
     *
     * @access   public
     * @param    string $link  Link reference
     */
    function setLink($link)
    {
        $this->_link = $link;
    }

    /**
     * Set the link target 
     *
     * @access   public
     * @param    string $target Link target
     */
    function setTarget($target)
    {
        $this->_target = $target;
    }

    /**
     * Construct the widget
     *
     * @access   private
     */
    function buildXHTML()
    {
        if (empty($this->_link)) {
            $this->_XHTML = '<a href="javascript:void(0);" ';
        } else if (strpos($this->_link, 'javascript') === false) {
            $this->_XHTML = '<a href="' . $this->_link . '"';
        } else {
            $this->_XHTML = '<a href="javascript:void(0);" onclick="' . $this->_link . '"';
        }

        if (!empty($this->_target)) {
            $this->_XHTML.= " target=\"{$this->_target}\"";
        }

        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML.= '>';

        if (!empty($this->_image)) {
            $this->_XHTML.= '<img';
            $this->_XHTML.= ' src="'.$this->_image.'"';
            $this->_XHTML.= ' border="0"';
            $this->_XHTML.= ' alt="'.$this->_text.'"';
            $this->_XHTML.= ' title="'.$this->_text.'"';
            $this->_XHTML.= ' />';            
        }

        if (!$this->_hideText && !empty($this->_text)) {
            $this->_XHTML.= $this->_text;
        }

        $this->_XHTML.= '</a>';
    }

}