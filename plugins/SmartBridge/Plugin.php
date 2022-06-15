<?php
/**
 * Replaces [a:Gadget:FastURL]Text[/a] with a proper link to the FastURL in Gadget
 *
 * @category   Plugin
 * @package    SmartBridge
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SmartBridge_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version  = '0.2';

    /**
     * Approved gadgets for links
     *
     * @var     array
     * @access  private
     */
    var $_ApprovedGadgets = array('Blog', 'StaticPage', 'Phoo');

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
                        $this->app->getSiteURL('/plugins/SmartBridge/images/smart-bridge-stock.png', true));
        $button->SetTitle($this->plugin::t('ADD').' ALT+B');
        $button->AddEvent(ON_CLICK, "javascript: insertTags('$textarea','[a:Gadget:FastURL]','[/a]','".
                          $this->plugin::t('SAMPLE')."');");
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
        // only approved gadgets
        if (!in_array($gadget, $this->_ApprovedGadgets)) {
            return $html;
        }

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
                $mapURL = $this->app->map->GetMappedURL('Blog', 'SingleView', array('id' => $link));
                break;
            case 'Phoo':
                $mapURL = $this->app->map->GetMappedURL('Phoo', 'Photos', array('album' => $link));
                break;
            case 'StaticPage':
                $mapURL = $this->app->map->GetMappedURL('StaticPage', 'Page', array('id' => $link));
                break;
        }

        $text = '<a href="'. $mapURL . '">' . $linkText . '</a>';
        return $text;
    }

}