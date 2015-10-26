<?php
/**
 * Enables gravatar [www.gravatar.com] support in Jaws.
 *
 * @category   Gadget
 * @package    Core
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gravatar
{
    /**
     * Gets gravatar avatar address
     *
     * @access  public
     * @param   string  $email  Email address
     * @param   int     $size   Avatar size
     * @return  string  Avatar image address
     */
    static function GetGravatar($email = '', $size = 48)
    {
        $theme = $GLOBALS['app']->GetTheme();
        if (file_exists($theme['path'] . 'default_avatar.png')) {
            $defaultImage = $theme['url'] . 'default_avatar.png';
        } else {
            $defaultImage = "gadgets/Users/Resources/images/photo{$size}px.png";
        }

        if (empty($email) || $GLOBALS['app']->Registry->fetch('use_gravatar', 'Settings') == 'no') {
            return $defaultImage;
        }

        $id = md5($email);
        $rating = $GLOBALS['app']->Registry->fetch('gravatar_rating', 'Settings');
        if (Jaws_Error::isError($rating)) {
            $rating = 'g';
        }

        if ($size > 128) {
            $size = 128;
        } elseif ($size < 0) {
            $size = 0;
        }

        $defaultImage = urlencode($GLOBALS['app']->getSiteURL('/'.$defaultImage));
        $gravatar = 'http://www.gravatar.com/avatar/'. md5(strtolower(trim($email)));
        $gravatar.= '?d=' . $defaultImage. '&amp;r='. $rating. '&amp;s=' . $size;

        return $gravatar;
    }

}