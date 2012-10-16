<?php
/**
 * Replaces [email], [url] and other tags to their HTML syntax
 *
 * @category   Plugin
 * @package    FastLinks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FastLinks extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function FastLinks()
    {
        $this->_Name = 'FastLinks';
        $this->_Description = _t('PLUGINS_FASTLINKS_DESCRIPTION');
        $this->_Example = '[email="user@jaws.com.mx"]MyFriend[/email]<br />' . "\n".
            '[email]user@jaws.com.mx[/email]<br />' . "\n".
            '[url]http://www.jaws-project.com[/url]<br />' . "\n".
            '[url="http://www.jaws-project.com"]Jaws Site[/url]' . "\n";
        $this->_IsFriendly = true;
        $this->_Version = '0.3';
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
        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetSpacing(0);

        $linkbutton =& Piwi::CreateWidget('Button', 'linkbutton', '',
                            $GLOBALS['app']->getSiteURL('/plugins/FastLinks/images/stock-fastlink.png', true));
        $linkbutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[url]','[/url]','".
                              _t('PLUGINS_FASTLINKS_YOURLINK')."');");
        $linkbutton->SetTitle(_t('PLUGINS_FASTLINKS_ADD_SITE').' ALT+L');
        $linkbutton->SetAccessKey('L');

        $emailbutton =& Piwi::CreateWidget('Button', 'emailbutton', '',
                            $GLOBALS['app']->getSiteURL('/plugins/FastLinks/images/stock-fastemail.png', true));
        $emailbutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[email]','[/email]','".
                               _t('PLUGINS_FASTLINKS_YOUREMAIL')."');");
        $emailbutton->SetTitle(_t('PLUGINS_FASTLINKS_ADD_EMAIL').' ALT+E');
        $emailbutton->SetAccessKey('E');

        $buttonbox->PackStart($linkbutton);
        $buttonbox->PackStart($emailbutton);

        return $buttonbox;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
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