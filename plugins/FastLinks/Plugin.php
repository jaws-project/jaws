<?php
/**
 * Replaces [email], [url] and other tags to their HTML syntax
 *
 * @category   Plugin
 * @package    FastLinks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FastLinks_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = '0.3';

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $buttonbox =& Piwi::CreateWidget('Division');

        $linkbutton =& Piwi::CreateWidget('Button', 'linkbutton', '',
                            $this->app->getSiteURL('/plugins/FastLinks/images/stock-fastlink.png', true));
        $linkbutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[url]','[/url]','".
                              $this->plugin::t('YOURLINK')."');");
        $linkbutton->SetTitle($this->plugin::t('ADD_SITE').' ALT+L');
        $linkbutton->SetAccessKey('L');

        $emailbutton =& Piwi::CreateWidget('Button', 'emailbutton', '',
                            $this->app->getSiteURL('/plugins/FastLinks/images/stock-fastemail.png', true));
        $emailbutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[email]','[/email]','".
                               $this->plugin::t('YOUREMAIL')."');");
        $emailbutton->SetTitle($this->plugin::t('ADD_EMAIL').' ALT+E');
        $emailbutton->SetAccessKey('E');

        $buttonbox->PackStart($linkbutton);
        $buttonbox->PackStart($emailbutton);

        return $buttonbox;
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
        $html =  preg_replace(array(
                                        "#\[email](.*?)\[/email]#si",
                                        "#\[email=('|\")(.*?)('|\")](.*?)\[/email]#si"
                                        ),
                                  array(
                                        "<a href=\"mailto:$1\">$1</a>",
                                        "<a href=\"mailto:$2\">$4</a>"
                                        ),
                                  $html);

        $html =  preg_replace(array(
                                        "#\[url=(?:'|\")([^\]]*?".$_SERVER['SERVER_NAME']."[^\]]*?)(?:'|\")\](.*?)\[/url\]#si",
                                        "#\[url\]([^\]]*?".$_SERVER['SERVER_NAME'].".*?)\[/url\]#si",
                                        "#\[url=(?:'|\")(.*?)(?:'|\")\](.*?)\[/url\]#si",
                                        "#\[url\](.*?)\[/url\]#si"
                                        ),
                                  array(
                                        "<a href=\"$1\">$2</a>",
                                        "<a href=\"$1\">$1</a>",
                                        "<a href=\"$1\">$2</a>",
                                        "<a href=\"$1\">$1</a>"
                                        ),
                                  $html);


        return $html;
    }

}