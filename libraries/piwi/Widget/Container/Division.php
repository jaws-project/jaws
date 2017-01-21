<?php
/**
 * Division.php - Division Class
 *
 * @version  $Id $
 * @author   Ali Fazelzadeh <afz@php.net>
 *
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Box.php';

define('DIV_REQ_PARAMS', 0);
class Division extends Box
{
    /**
     * Public constructor
     *
     * @param  int   $spacing The Spacing of the box
     * @param  int   $border  Border size
     * @access public
     */
    function __construct()
    {
        parent::init();
        $this->_class = 'piwi_division';

    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        $items =& $this->getItems();
        if (count($items) > 0) {
            $this->_PiwiXML->openElement('items');
            foreach ($items as $item) {
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
        $this->_XHTML  = "<div ";
        $this->_XHTML .= $this->buildBasicXHTML();
        if (!empty($this->_Width)) {
            $this->_XHTML .= " width = \"{$this->_Width}\"";
        }
        $this->_XHTML .= ">\n";

        foreach ($this->getItems() as $item) {
            if (is_subclass_of($item, 'Bin')) {
                $item->rebuildJS();
            } else {
                $item->rebuild();
            }
            $this->addJS($item->getJS());
            $this->addFiles($item->getFiles());

            if ($this->_useTitles) {
                switch ($item->getFamilyWidget()) {
                    case 'bin':
                        $title = $item->getTitle();
                        if ($item->getClassName() != 'button' && !empty($title)) {
                            if ($item->requiresTwoColons()) {
                                $title .= ':&nbsp;';
                            }
                            $this->_XHTML .= '<label for="' . $item->getID() . '">' . $title . '</label>';
                        }
                        $this->_XHTML .= $item->get(false);
                        break;

                    case 'container':
                        $this->_XHTML .= $item->getItemsWithTitles();
                        break;

                    case 'misc':
                        $this->_XHTML .= $item->get(true);
                        break;
                }
            } else {
                $this->_XHTML .= $item->get(false);
            }
        }

        $this->_XHTML .= "</div>";
    }

}