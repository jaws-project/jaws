<?php
/**
 * HBox.php - HBox Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Box.php';

define('HBOX_REQ_PARAMS', 0);
class HBox extends Box
{
    /**
     * Public constructor
     *
     * @param  int   $spacing The Spacing of the box
     * @param  int   $border  Border size
     * @access public
     */
    function __construct($spacing = 0, $border = 0)
    {
        $this->setBorder($border);
        $this->setName('hbox' . rand(1, 100));
        parent::init();
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
        $this->_XHTML  = "<table";
        $this->_XHTML .= $this->buildBasicXHTML();
        if (!empty($this->_Width)) {
            $this->_XHTML .= " width = \"{$this->_Width}\"";
        }
        $this->_XHTML .= ">\n";
        $this->_XHTML .= "<tr>\n";

        foreach ($this->getItems() as $item) {
            $this->_XHTML .= " <td valign=\"top\">\n";
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

            $this->_XHTML .= " </td>\n";
        }

        $this->_XHTML .= "</tr>\n";
        $this->_XHTML .= "</table>";
    }

}
