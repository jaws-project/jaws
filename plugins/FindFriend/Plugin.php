<?php
/**
 * Returns the URL of the given friend
 *
 * @category   Plugin
 * @package    FindFriend
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FindFriend_Plugin extends Jaws_Plugin
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
        if (file_exists(JAWS_PATH.'gadgets/Friends/Model.php') &&
            Jaws_Gadget::IsGadgetInstalled('Friends')) {
            $button =& Piwi::CreateWidget('Button', 'addfriend', '',
                            $GLOBALS['app']->getSiteURL('/plugins/FindFriend/images/stock-friends.png', true));
            $button->SetTitle(_t('PLUGINS_FINDFRIEND_ADD').' ALT+F');
            $button->AddEvent(ON_CLICK, "javascript: insertTags('$textarea','[friend]','[/friend]','".
                              _t('PLUGINS_FINDFRIEND_FRIEND')."');");
            $button->SetAccessKey('F');

            return $button;
        }

        return '';
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
        if (file_exists(JAWS_PATH.'gadgets/Friends/Model.php') &&
            Jaws_Gadget::IsGadgetInstalled('Friends')) {
            $howMany = preg_match_all('#\[friend\](.*?)\[/friend\]#si', $html, $matches);
            $objFriends = Jaws_Gadget::getInstance('Friends')->model->load('Friends');
            for ($i = 0; $i < $howMany; $i++) {
                $match_text = $matches[1][$i];
                //How many?
                $friend =  $objFriends->GetFriendByName($match_text);
                if (!Jaws_Error::IsError($friend)) {
                    $new_text = "<a href=\"".$friend['url']."\" rel=\"friend\">".$match_text."</a>";
                } else {
                    $new_text = $match_text;
                }
                $pattern = '#\[friend\]'.$match_text.'\[/friend\]#si';
                $html = preg_replace($pattern, $new_text, $html);
            }
        } else {
            //FIXME: Simon says we need another regexp here
            $html = str_replace('[friend]', '', $html);
            $html = str_replace('[/friend]', '', $html);
        }

        return $html;
    }

}