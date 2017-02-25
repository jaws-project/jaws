<?php
/**
 * Replaces [a:Gadget:FastURL]Text[/a] with a proper link to the FastURL in Gadget
 *
 * @category   Plugin
 * @package    SmartBridge
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SmartBridge_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version  = '0.2';

    /**
     * Approved gadgest for links
     *
     * @var     array
     * @access  private
     */
    var $_ApprovedGadgets = array('Blog', 'StaticPage', 'Phoo');

    /**
     * Jaws gadgets that are enabled
     *
     * @var     array
     * @access  private
     */
    var $_EnabledGadgets  = array();

    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct($plugin)
    {
        parent::__construct($plugin);
        $eg = $GLOBALS['app']->Registry->fetch('gadgets_enabled_items');
        if (Jaws_Error::isError($eg)) {
            $eg = array();
        }

        $this->_EnabledGadgets = explode(',', $eg);
    }

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $button =& Piwi::CreateWidget('Button', 'addbridge', '',
                        $GLOBALS['app']->getSiteURL('/plugins/SmartBridge/images/smart-bridge-stock.png', true));
        $button->SetTitle(_t('PLUGINS_SMARTBRIDGE_ADD').' ALT+B');
        $button->AddEvent(ON_CLICK, "javascript: insertTags('$textarea','[a:Gadget:FastURL]','[/a]','".
                          _t('PLUGINS_SMARTBRIDGE_SAMPLE')."');");
        $button->SetAccessKey('B');
        
        return $button;
    }

    /**
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
     */
    function NeedParsing($html)
    {
        if (stripos($html, '[/a]') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
    {
        if (!$this->NeedParsing($html)) {
            return $html;
        }

        $html = preg_replace_callback('#\[a\](.*?):(.*?)\[/a\]#si',
                                          array(&$this, 'Prepare'),
                                          $html);

        $html = preg_replace_callback('#\[a:(.*?):(.*?)\](.*?)\[/a\]#si',
                                          array(&$this, 'Prepare'),
                                          $html);

        return $html;
    }

    /**
     * The preg_replace call back function
     *
     * @access  private
     * @param   string  $matches    Matched strings from preg_replace_callback
     * @return  string  Gadget action output
     */
    function Prepare($matches)
    {
        $matches[1] = Jaws_XSS::filter($matches[1]);
        $gadget = ucfirst(strtolower($matches[1]));
        if ($gadget == 'Staticpage' || $gadget == 'Page') {
            $gadget = 'StaticPage';
        }

        $link = Jaws_XSS::filter($matches[2]);
        $linkText = isset($matches[3])? $matches[3] : $linkText;
        switch ($gadget) {
            case 'Blog':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $link));
                break;
            case 'Phoo':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('Phoo', 'Photos', array('album' => $link));
                break;
            case 'StaticPage':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Page', array('id' => $link));
                break;
        }

        $text = '<a href="'. $mapURL . '">' . $linkText . '</a>';
        return $text;
    }

}