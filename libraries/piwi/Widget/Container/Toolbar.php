<?php
/**
 * Toolbar.php - Toolbar Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('TOOLBAR_REQ_PARAMS', 0);
class Toolbar extends Container
{
    /**
     * Public constructor
     *
     * @param  string $direction Direction of the toolbar. Must be horizontal or vertical
     * @param  string $spacing   Spacing of the container
     * @param  string $border    Border of the container
     * @access public
     */
    function Toolbar($direction = 'horizontal', $spacing = 0, $border = 0)
    {
        $this->_border    = $border;
        $this->_spacing   = $spacing;
        $this->_direction = $direction;
        $this->_name      = 'toolbar' . rand(1,100);

        if ($direction != 'horizontal' && $direction != 'vertical') {
            $this->_direction = 'horizontal';
        }

        parent::init();
    }

    /**
     * Add an item to the toolbar
     *
     * @param  object $widget The widget to pack
     * @access public
     */
    function add(&$widget)
    {
        //.. radio buttons or check buttons are so f* horrible that we should not use them here..
        if (!strpos($widget->getClassName(), 'buttons')) {
            $this->_items[] =& $widget;
        } else {
            die("Sorry, radio/check buttons can't be added to the toolbar");
        }
    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        if ($this->_direction == 'vertical') {
            $this->_PiwiXML->addAttribute('direction', 'vertical');
        } else {
            $this->_PiwiXML->addAttribute('direction', 'horizontal');
        }


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
     * Build the XHTML data
     *
     * @access  public
     */
    function buildXHTML()
    {
        if (count($this->_items) > 0) {
            $this->_XHTML = "<table cellpadding=\"0\"";

            $this->_XHTML.= $this->buildBasicXHTML();
            $this->_XHTML.= ">\n";
            $this->_JS =  '';

            if ($this->_direction == 'vertical') {
                foreach ($this->_items as $item) {
                    $this->_XHTML.= "<tr>\n";
                    $this->_XHTML.= " <td valign=\"top\">\n";
                    if (is_subclass_of($item, 'Bin')) {
                        $item->rebuildJS();
                    } else {
                        $item->rebuild();
                    }
                    $this->addJS($item->getJS());
                    $this->addFiles($item->getFiles());
                    $this->_XHTML.= $item->get(false);
                    $this->_XHTML.= " </td>\n";
                    $this->_XHTML.= "</tr>\n";
                }
            } else {
                $this->_XHTML.= "<tr>\n";
                foreach ($this->_items as $item) {
                    $this->_XHTML.= " <td valign=\"top\">\n";
                    if (is_subclass_of($item, 'Bin')) {
                        $item->rebuildJS();
                    } else {
                        $item->rebuild();
                    }
                    $this->addJS($item->getJS());
                    $this->addFiles($item->getFiles());
                    $this->_XHTML.= $item->get(false);
                    $this->_XHTML.= " </td>\n";
                }
                $this->_XHTML.= "</tr>\n";
            }
            $this->_XHTML.= "</table>";
        } else {
            $this->_XHTML = '';
        }
    }
}
?>
