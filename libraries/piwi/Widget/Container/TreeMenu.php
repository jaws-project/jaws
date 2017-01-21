<?php
/**
 * TreeMenu.php - TreeMenu Class. Works very similar like MenuBar
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';
require_once PIWI_PATH . '/Widget/Container/MenuBar.php';
require_once PIWI_PATH . '/Widget/Container/MenuItem.php';

define('TREEMENU_REQ_PARAMS', 0);
class TreeMenu extends MenuBar
{
    /**
     * Root text
     *
     * @var    string
     * @access private
     * @see    setRootText
     */
    var $_rootText = '';

    /**
     * Images directory
     *
     * @var    string
     * @access private
     * @see    setImagesDirectory
     */
    var $_imagesDirectory = '';

    /**
     * Public constructor
     *
     * @param    string   $toptext Root text
     * @param    string   $id      The ID of the div that will have the treemenu
     * @access   public
     */
    function __construct($toptext, $id = 'treemenu')
    {
        $this->_rootText = $toptext;
        $this->_paths = array();
        $this->_class = 'DynamicTree';
        $this->_imagesDirectory = PIWI_URL . 'piwidata/art/treemenu/';
        $this->_id = $id;
        if (empty($this->_id)) {
            die("TreeMenu should have an id!");
        }

        parent::init();
    }

    /**
     * Set Root text
     *
     * @param   string  $text  Root Text
     * @access  public
     */
    function setRootText($text)
    {
        $this->_rootText = $text;
    }

    /**
     * Set Images directory. The directory should have the following files:
     *
     *  "branch": "tree-branch.png"
     *  "doc": "tree-doc.png"
     *  "folder": "tree-folder.png"
     *  "folderOpen": "tree-folder-open.png"
     *  "leaf": "tree-leaf.png"
     *  "leafEnd": "tree-leaf-end.png"
     *  "node": "tree-node.png"
     *  "nodeEnd": "tree-node-end.png"
     *  "nodeOpen": "tree-node-open.png"
     *  "nodeOpenEnd": "tree-node-open-end.png"
     *
     * @param   string  $dir Images directory, can also be an URL
     * @access  public
     */
    function setImagesDirectory($dir)
    {
        $this->_imagesDirectory = $dir;
    }

    /**
     * Read the childs menuitems of a menuitem
     *
     * @param    array    $items  Items to read
     * @access   private
     */
    function getChildItems($childs, $spacing = '    ')
    {
        $sspacing = $spacing;
        $dspacing = $spacing.''.$spacing;


        foreach ($childs as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();

            $realText = "";
            if (!empty($action)) {
                $realText.= "<a href=\"".$action."\">";
            }

            if (!empty($icon)) {
                $realText.= "<img src=\"".$icon."\" alt=\"".$text."\" />&nbsp;";
            }

            $realText.= $text;
            if (!empty($action)) {
                $realText.= "</a>";
            }

            if ($text != '-') {
                $items = $o->getItems();
                if (count($items) > 0) {
                    $xhtml.= "".$sspacing."<div class=\"folder\">".$realText."\n";
                    $xhtml.= $this->getChildItems($items, $dspacing);
                    $xhtml.= "".$sspacing."</div>\n";
                } else {
                    $xhtml.= $sspacing."<div class=\"doc\">".$realText."</div>\n";
                }
            }
        }
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
            $this->_PiwiXML->openElement('treemenu');
        }

        foreach ($childs as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();

            if ($text != '-') {
                $items = $o->getItems();
                if (count($items) > 0) {
                    $this->_PiwiXML->openElement('folder');
                    $this->_PiwiXML->addAttribute('label', $text);

                    if (!empty($action)) {
                        $this->_PiwiXML->addAttribute('action', $action);
                    }

                    if (!empty($icon)) {
                        $this->_PiwiXML->addAttribute('icon', $icon);
                    }

                    $this->getXmlChildItems($items, true);
                    $this->_PiwiXML->closeElement('folder');
                } else {
                    $this->_PiwiXML->openElement('doc', true);
                    $this->_PiwiXML->addAttribute('label', $text);

                    if (!empty($action)) {
                        $this->_PiwiXML->addAttribute('action', $action);
                    }

                    if (!empty($icon)) {
                        $this->_PiwiXML->addAttribute('icon', $icon);
                    }

                    $this->_PiwiXML->closeElement('doc');
                }
            } else {
                $this->_PiwiXML->openElement('treemenuseparator', true);
                $this->_PiwiXML->closeElement('treemenuseparator');
            }
        }

        if (!$for_top_level) {
            $this->_PiwiXML->closeElement('treemenu');
        }
    }

    /**
     * Build the piwiXML data.
     *
     * Based in XUL menus
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        $this->buildBasicPiwiXML();

        $this->_PiwiXML->addAttribute('name', $this->_id);
        foreach ($this->_Items as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();
            $this->_PiwiXML->openElement('folder');

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
            $this->_PiwiXML->closeElement('folder');
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
        $this->addFile(PIWI_URL . 'piwidata/js/mygosumenu/1.5/DynamicTree.js');

        if (substr($this->_imagesDirectory, -1, 1) != '/') {
            $this->_imagesDirectory = $this->_imagesDirectory.'/';
        }

        $this->_XHTML = "<div class=\"".$this->_class."\">\n";
        $this->_XHTML.= " <div class=\"top\">".$this->_rootText."</div>\n";
        $this->_XHTML.= " <div class=\"wrap\" id=\"".$this->_id."\">\n";
        $spacing = "    ";
        foreach ($this->_Items as $o) {
            $action = $o->getAction();
            $text   = $o->getValue();
            $icon   = $o->getIcon();
            $realText = '';
            if (!empty($action)) {
                $realText.= "<a href=\"".$action."\">";
            }

            if (!empty($icon)) {
                $realText.= "<img src=\"".$icon."\" alt=\"".$text."\" />&nbsp;";
            }

            $realText.= $text;
            if (!empty($action)) {
                $realText.= "</a>";
            }

            $items = $o->getItems();
            if (count($items > 0)) {
                $this->_XHTML.= "  <div class=\"folder\">".$realText."\n";
                $this->_XHTML.= $this->getChildItems($items, $spacing);
                $this->_XHTML.= "  </div>\n";
            } else {
                $this->_XHTML.= "  <div class=\"doc\">".$realText."</div>\n";
            }
        }
        $this->_XHTML.= " </div>\n";
        $this->_XHTML.= "</div>\n";
        $this->_XHTML.= "<script type=\"text/javascript\">\n";
        $this->_XHTML.= "var ".$this->_id." = new DynamicTree(\"".$this->_id."\");\n";
        $this->_XHTML.= $this->_id.".path = \"".$this->_imagesDirectory."\";\n";
        $this->_XHTML.= $this->_id.".foldersAsLinks = false;\n";
        $this->_XHTML.= $this->_id.".init();\n";
        $this->_XHTML.= "</script>\n";
    }
}
?>
