<?php
/**
 * Returns the URL of the given friend
 *
 * @category   Plugin
 * @package    FindFriend
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FindFriend extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function FindFriend()
    {
        $this->_Name = 'FindFriend';
        $this->_Description = _t('PLUGINS_FINDFRIEND_DESCRIPTION');
        $this->_Example = '[friend]pablo[/friend]</b></small>';
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
        if (file_exists(JAWS_PATH.'gadgets/Friends/Model.php') &&
            Jaws_Gadget::IsGadgetInstalled('Friends')) {
            require_once JAWS_PATH.'gadgets/Friends/Model.php';

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
            require_once JAWS_PATH.'gadgets/Friends/Model.php';

            $howMany = preg_match_all('#\[friend\](.*?)\[/friend\]#si', $html, $matches);
            for ($i = 0; $i < $howMany; $i++) {
                $match_text = $matches[1][$i];
                //How many?
                $friend =  FriendsModel::GetFriendByName($match_text);
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