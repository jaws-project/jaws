<?php
/**
 * Jaws Gadget Plugin
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Plugin
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * constructor
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
     * Parses the input text
     *
     * @access  public
     * @param   string  $text       Text to parse
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Returns the parsed text
     */
    function parse($text, $reference, $action = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        $plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
            $plugins = array_filter(explode(',', $plugins));
            foreach ($plugins as $plugin) {
                $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
                if (!Jaws_Error::IsError($objPlugin)) {
                    $use_in = '*';
                    $use_in = $GLOBALS['app']->Registry->fetch('frontend_gadgets', $plugin);
                    if (!Jaws_Error::isError($use_in) &&
                       ($use_in == '*' || in_array($gadget, explode(',', $use_in)))
                    ) {
                        $text = $objPlugin->ParseText($text, $reference, $action, $gadget);
                    }
                }
            }
        }

        return Jaws_String::AutoParagraph($text);
    }


    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text       Text to parse
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Returns the parsed text
     */
    function parseAdmin($text, $reference, $action = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        $plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
            $plugins = array_filter(explode(',', $plugins));
            foreach ($plugins as $plugin) {
                $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
                if (!Jaws_Error::IsError($objPlugin)) {
                    $use_in = '*';
                    $use_in = $GLOBALS['app']->Registry->fetch('backend_gadgets', $plugin);
                    if (!Jaws_Error::isError($use_in) &&
                       ($use_in == '*' || in_array($gadget, explode(',', $use_in)))
                    ) {
                        $text = $objPlugin->ParseText($text, $reference, $action, $gadget);
                    }
                }
            }
        }

        return Jaws_String::AutoParagraph($text);
    }

}