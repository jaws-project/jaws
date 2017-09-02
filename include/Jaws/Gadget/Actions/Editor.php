<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_Editor
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Prepares the jaws Editor
     *
     * @access  public
     * @param   string  $name   Name of the editor
     * @param   string  $value  Content of the editor
     * @param   int     $markup Markup language type
     * @return  object  The editor in /gadgets/Settings/editor
     */
    function load($name, $value = '', $markup = JAWS_MARKUP_BBCODE)
    {
        $editor = $GLOBALS['app']->GetEditor();
        if (!file_exists(JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php')) {
            $editor = 'TextArea';
        }

        $className = "Jaws_Widgets_$editor";
        return new $className($this->gadget->name, $name, $value, $markup);
    }

    /**
     * Prepares the jaws Editor
     *
     * @access  public
     * @param   string  $name   Name of the editor
     * @param   string  $value  Content of the editor
     * @param   int     $markup Markup language type
     * @param   bool    $filter  Convert special characters to HTML entities
     * @return  object  The editor in /gadgets/Settings/editor
     */
    function loadAdmin($name, $value = '', $markup = JAWS_MARKUP_HTML)
    {
        $editor = $GLOBALS['app']->GetEditor();
        if (!file_exists(JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php')) {
            $editor = 'TextArea';
        }

        $className = "Jaws_Widgets_$editor";
        return new $className($this->gadget->name, $name, $value, $markup);
    }

}