<?php
/**
 * Toolbar.php - Toolbar Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 * @author   Ali Fazelzadeh <afz@php.net>
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
     * @access public
     */
    function Toolbar()
    {
        parent::init();
    }

    /**
     * Add an item to the toolbar
     *
     * @param  object $widget The widget to pack
     * @access public
     */
    function add(&$widget, $identifier = '')
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
            $this->_XHTML = "<div class=\"piwi_editor_toolbar\"";
            $this->_XHTML.= $this->buildBasicXHTML();
            $this->_XHTML.= ">\n";
            $this->_JS =  '';

            foreach ($this->_items as $item) {
                if (is_subclass_of($item, 'Bin')) {
                    $item->rebuildJS();
                } else {
                    $item->rebuild();
                }
                $this->addJS($item->getJS());
                $this->addFiles($item->getFiles());
                $this->_XHTML.= $item->get(false);
            }

            $this->_XHTML.= "</div>";
        } else {
            $this->_XHTML = '';
        }
    }

}