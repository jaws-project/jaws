<?php
/**
 * MenuBar.php - MenuBar Class
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';
require_once PIWI_PATH . '/Widget/Container/MenuItem.php';

define('MENUBAR_REQ_PARAMS', 0);
class MenuBar extends Container
{
    /**
     * Public constructor
     *
     * @param    string   $id   The ID of the div that will have the menubar.
     *                          if you are using another menubar, you should give it another ID and
     *                          another CSS sheet!
     * @access   public
     */
    function __construct($id = 'menubar')
    {
        $this->_id    = $id;
        $this->_paths = array();
        $this->_class = 'ddmx';
        if (empty($this->_id)) {
            die("Menubar should have an id!");
        }

        parent::init();
    }

    /**
     * Add a MenuItem
     *
     * @param    string   $path  Path of the menuitem
     * @param    string   $url   What to do?
     * @param    string   $icon  The icon
     * @access   public
     */
    function add($path, $url = '', $icon = '')
    {
        array_push($this->_paths, $path);
        $explodedPath = explode('/',$path);
        $numElements = count($explodedPath);
        $i = 0;
        foreach ($explodedPath as $item) {
            if ($numElements == 1) {
                if (!isset($this->_items[$item])) {
                    $this->_items[$item] = new MenuItem($item, $url, $icon);
                }
            }

            if ($i == $numElements-1) {
                if (!empty($explodedPath[$i-1])) {
                    $this->addTo($explodedPath[$i-1], $this->_items, $path, $item, $url, $icon);
                }
            }
            $i++;
        }
    }

    /**
     * Add a menu item to a certain menupath
     *
     * @param    string   $findme Text to find
     * @param    array    $items  Items to read
     * @param    string   $title  Text of the item
     * @param    string   $url    URL of the item
     * @param    string   $icon   Icon of the item
     * @access   public
     */
    function addTo($findme, $items, $path, $title, $url, $icon)
    {
        $i = 0;
        $founded = false;
        foreach ($items as $key => $item) {
            if ($item->getValue() == $findme) {
                $items[$key]->add($title, $url, $icon);
                $founded = true;
                break;
            }
            $i++;
        }

        if (!$founded) {
            foreach ($items as $item) {
                //sometimes the computers are SOOO stupid that they confuse an array with a scalar :-P
                if (is_array($path)) {
                    $path = implode('/', $path);
                }

                $path = explode('/', $path);
                //Are we in this path?...
                if ($path[0] == $item->getValue()) {
                    if (count($item->getItems()) > 0) {
                        unset($path[0]);
                        $path = implode('/', $path);
                        if ($this->addTo($findme, $item->getItems(), $path, $title, $url, $icon)) {
                            $founded = true;
                            break;
                        } else {
                            $founded = false;
                        }
                    }
                } else {
                    $founded = false;
                }
            }
        }

        return $founded;
    }

    /**
     * Read the childs menuitems of a menuitem
     *
     * @param    array    $items  Items to read
     * @access   private
     */
    function getChildItems ($childs, $spacing = '    ')
    {
        $sspacing = $spacing;
        $dspacing = $spacing."".$spacing;

        $xhtml = "\n".$sspacing."<div class=\"section\">\n";

        foreach ($childs as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();

            if ($text != '-') {
                $items = $o->getItems();
                if (count($items) > 0) {
                    if (!empty($action)) {
                        $xhtml.= $sspacing."<a class=\"item2 arrow\" href=\"".$action."\">";
                    } else {
                        $xhtml.= $sspacing."<a class=\"item2 arrow\" href=\"javascript:void(0);\">";
                    }

                    if (!empty ($icon)) {
                        $xhtml.= "<img src=\"".$icon."\" alt=\"".$text."\" />&nbsp;";
                    }

                    $xhtml.= $text."</a>";
                    if (count($items) > 0) {
                        $xhtml.= $this->getChildItems($items, $dspacing);
                    } else {
                        $xhtml.= "\n";
                    }
                } else {
                    if (!empty($action)) {
                        $xhtml.= $sspacing."<a class=\"item2\" href=\"".$action."\">";
                    } else {
                        $xhtml.= $sspacing."<a class=\"item2\" href=\"javascript:void(0);\">";
                    }

                    if (!empty($icon)) {
                        $xhtml.= "<img src=\"".$icon."\" alt=\"".$text."\" />&nbsp;";
                    }
                    $xhtml.= $text."</a>";
                    if (count($items) > 0) {
                        $xhtml.= $this->getChildItems($items, $dspacing);
                    } else {
                        $xhtml.= "\n";
                    }
                }
            }
        }
        $xhtml.= $spacing."</div>\n";

        return $xhtml;
    }

    /**
     * Get piwiXML Items
     *
     * @param     array   $childs Childs to read and add
     * @param     boolean $for_top_level The child items are for a top_level menu?   *
     * @access    private
     */
    function getXmlChildItems($childs, $for_top_level = false)
    {
        if (!$for_top_level) {
            $this->_PiwiXML->openElement('menu');
        }

        foreach ($childs as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();

            if ($text != '-') {
                $items = $o->getItems();
                if (count($items) > 0) {
                    $this->_PiwiXML->openElement('menu');
                    $this->_PiwiXML->addAttribute('label', $text);

                    if (!empty($action)) {
                        $this->_PiwiXML->addAttribute('action', $action);
                    }

                    if (!empty($icon)) {
                        $this->_PiwiXML->addAttribute('icon', $icon);
                    }

                    $this->getXmlChildItems($items, true);
                    $this->_PiwiXML->closeElement('menu');
                } else {
                    $this->_PiwiXML->openElement('menuitem', true);
                    $this->_PiwiXML->addAttribute('label', $text);

                    if (!empty($action)) {
                        $this->_PiwiXML->addAttribute('action', $action);
                    }

                    if (!empty($icon)) {
                        $this->_PiwiXML->addAttribute('icon', $icon);
                    }

                    $this->_PiwiXML->closeElement('menuitem');
                }
            } else {
                $this->_PiwiXML->openElement('menuseparator', true);
                $this->_PiwiXML->closeElement('menuseparator');
            }
        }

        if (!$for_top_level) {
            $this->_PiwiXML->closeElement('menu');
        }
    }

    /**
     * Build the piwiXML data.
     *
     * Based in XUL menus
     *
     * @access    public
     */
    function buildPiwiXML ()
    {
        $this->buildBasicPiwiXML();

        $this->_PiwiXML->addAttribute('name', $this->_id);
        foreach ($this->_items as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();
            $this->_PiwiXML->openElement('menu');

            if (!empty($action)) {
                $this->_PiwiXML->addAttribute('action', $action);
            }

            if (!empty($icon)) {
                $this->_PiwiXML->addAttribute('icon', $icon);
            }
            $this->_PiwiXML->addAttribute('label', $text);

            $items = $o->getItems();
            if (count($items) > 0) {
                $this->getXmlChildItems($items, true);
            }
            $this->_PiwiXML->closeElement('menu');
        }
        $this->_PiwiXML->closeElement($this->getClassName());
    }

    /**
     * Build the XHTML and JS Menu
     *
     * @access   public
     */
    function buildXHTML()
    {
        $this->addFile(PIWI_URL . 'piwidata/js/mygosumenu/ie5.js');
        $this->addFile(PIWI_URL . 'piwidata/js/mygosumenu/1.1/DropDownMenuX.js');

        $this->_XHTML = "<table id=\"".$this->_id."\" class=\"".$this->_class."\">\n";
        $this->_XHTML.=  "<tr>\n";
        $spacing = '    ';
        foreach ($this->_items as $o) {
            $this->_XHTML.= "  <td>\n";
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();
            if (!empty($action)) {
                $this->_XHTML.= $spacing."<a class=\"item1\" href=\"".$action."\">";
            } else {
                $this->_XHTML.= $spacing."<a href=\"javascript:void(0);\" class=\"item1\">";
            }

            if (!empty($icon)) {
                $this->_XHTML.= "<img src=\"".$icon."\" alt=\"".$text."\" />&nbsp;";
            }
            $this->_XHTML.= $text;
            $this->_XHTML.= "</a>";

            $items = $o->getItems();
            if (count($items) > 0) {
                $this->_XHTML.= $this->getChildItems($items, $spacing);
            }
            $this->_XHTML.= "  </td>\n";
        }
        $this->_XHTML.= "</tr>\n";
        $this->_XHTML.= "</table>\n";
        $this->_XHTML.= "<script type=\"text/javascript\">\n";
        $this->_XHTML.= "var ".$this->_id."_menu = new DropDownMenuX('".$this->_id."');\n";
        $this->_XHTML.= $this->_id."_menu.delay.show = 0;\n";
        $this->_XHTML.= $this->_id."_menu.delay.hide = 400;\n";
        $this->_XHTML.= $this->_id."_menu.position.levelX.left = 2;\n";
        $this->_XHTML.= $this->_id."_menu.init();\n";
        $this->_XHTML.= "</script>\n";
    }
}
?>
