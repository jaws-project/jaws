<?php
/**
 * Replaces [a:Gadget:FastURL]Text[/a] with a proper link to the FastURL in Gadget
 *
 * @category   Plugin
 * @package    SmartBridge
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

class SmartBridge extends Jaws_Plugin
{
    /**
     * Approved gadgest for links
     *
     * @access  private
     * @var     array
     */
    var $_ApprovedGadgets = array();

    /**
     * Jaws gadgets that are enabled
     *
     * @access  private
     * @var     array
     */
    var $_EnabledGadgets  = array();

    /**
     * Main Constructor
     *
     * @access  public
     */
    function SmartBridge()
    {
        $this->_Name = 'SmartBridge';
        $this->_Description = _t('PLUGINS_SMARTBRIDGE_DESCRIPTION');
        $this->_Example = '[a:Blog:Remember_Me]Remember me[/a]';
        $this->_IsFriendly = true;
        $this->_Version = '0.2';

        $this->_ApprovedGadgets = array('Blog',
                                        'StaticPage',
                                        'Phoo');

        $eg = $GLOBALS['app']->Registry->get('/gadgets/enabled_items');
        if (Jaws_Error::isError($eg)) {
            $eg = array();
        }

        $this->_EnabledGadgets = explode(',', $eg);
    }

    /**
     * Overrides, Get the WebControl of this plugin
     *
     * @access  public
     * @return  object The HTML WebControl
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
     * A simple pares to findout if we want a complex parse
     *
     * @access  public
     * @param   string  $html   HTML to parse
     * @return  boolean
     */
    function NeedParsing($html)
    {
        if (stripos($html, '[/a]') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parse the text
     *
     * @access  public
     * @param   string  $html   Row HTML to parse
     * @return  string  Parsed HTML
     */
    function ParseText($html)
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
     * @return  string  Converted links or plain text on errors
     */
    function Prepare($matches)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $matches[1] = $xss->parse($matches[1]);
        $gadget = ucfirst(strtolower($matches[1]));
        if ($gadget == 'Staticpage' || $gadget == 'Page') {
            $gadget = 'StaticPage';
        }

        $link = $xss->filter($matches[2]);
        $linkText = isset($matches[3])? $matches[3] : $linkText;
        switch ($gadget) {
            case 'Blog':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $link));
                break;
            case 'Phoo':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewAlbum', array('id' => $link));
                break;
            case 'StaticPage':
                $mapURL = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Page', array('id' => $link));
                break;
        }

        $text = '<a href="'. $mapURL . '">' . $linkText . '</a>';
        return $text;
    }
}
