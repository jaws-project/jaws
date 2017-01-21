<?php
/**
 * ViewPort.php - ViewPort Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('VIEWPORT_REQ_PARAMS', 0);
class ViewPort extends Container
{
    /**
     * Width of the ViewPort
     *
     * @var    string  $_width
     * @access private
     * @see    SetWidth (), GetWidth ()
     */
    var $_width;

    /**
     * Height of the ViewPort
     *
     * @var    string  $_height
     * @access private
     * @see    SetHeight (), GetHeight ()
     */
    var $_height;

    /**
     * Public Constructor
     *
     * @param  string    $id     ID of the viewport
     * @param  string    $width  Width of the viewport
     * @param  string    $height Height of the Viewport
     * @access public
     */
    function __construct($id = 'viewport', $width = 300, $height = 150)
    {
        $this->_id = $id;
        $this->_width = $width;
        $this->_height = $height;

        if (empty ($this->_id)) {
            $this->_id = 'viewport_' . rand(1,100);
        }

        parent::init();
    }

    /**
     * Set the width
     *
     * @param  int   $width The Width
     * @access public
     */
    function setWidth($width)
    {
        $this->_width = $width;
    }

    /**
     * Get the Width
     *
     * @access public
     * @return int    Width of the box
     */
    function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set the height
     *
     * @param  int   $height The Height
     * @access public
     */
    function setHeight($height)
    {
        $this->_height = $height;
    }

    /**
     * Get the height
     *
     * @access public
     * @return string Height of the box
     */
    function getHeight()
    {
        return $this->_height;
    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        if (count($this->_items) > 0) {
            $this->_PiwiXML->openElement('items');
            foreach ($this->_items as $item) {
                $this->_PiwiXML->addXML($item->getPiwiXML(true));
            }
            $this->_PiwiXML->closeElement('items');
        }
        $this->_PiwiXML->closeElement($this->getClassName());
    }

    /**
     * Build XHTML data
     *
     * @access public
     */
    function buildXHTML()
    {
        $this->_XHTML = "<div";

        if (!empty($this->_style)) {
            $this->_style = "overflow: auto; width: ".$this->_width."px; height: ".$this->_height."px; ".$this->_style;
        } else {
            $this->_style = "overflow: auto; width: ".$this->_width."px; height: ".$this->_height."px;";
        }

        $this->_XHTML.= $this->buildBasicXHTML();
        $this->_XHTML.= ">\n";

        foreach ($this->_items as $item) {
            if (is_subclass_of($item, 'Bin')) {
                $item->rebuildJS();
            } else {
                $item->rebuild();
            }
            $this->addJS($item->getJS());
            $this->addFiles($item->getFiles());

            if ($this->_useTitles) {
                $familyWidget = $item->getFamilyWidget();
                if ($familyWidget == 'bin') {
                    $title = $item->getTitle();
                    if ($item->getClassName() != 'button' && !empty($title)) {
                        $this->_XHTML.= $title.":&nbsp;";
                    }
                    $this->_XHTML.= $item->get(false);
                } elseif ($familyWidget == 'container')
                    $this->_XHTML.= $item->getItemsWithTitles();
            } else {
                $this->_XHTML.= $item->get(false);
            }
        }

        $this->_XHTML.= "</div>\n";
    }
}
?>
