<?php
/**
 * Replace every email addres with 'at' and 'dot' strings
 *
 * @category   Plugin
 * @package    AntiSpammers
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Plugin that replaces all the @ and dots (.) of an email with _AT_ and _DOT_ string
 *
 * @see Jaws_Plugin
 */
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

class AntiSpammers extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     */
    function AntiSpammers()
    {
        $this->_Name = 'AntiSpammers';
        $this->_Description = _t('PLUGINS_ANTISPAMMERS_DESCRIPTION');
        $this->_IsFriendly = false; //no bbcode
        $this->_Version = '0.3';
    }

    /**
     * Simple parses the text and decides if the real parse call should be done
     *
     * @access  public
     * @param   string  $html Html to simple parse
     * @return  boolean
     */
    function NeedsParsing($html)
    {
        if (strpos($html, '@') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html Html to Parse
     * @return  string
     */
    function ParseText($html)
    {
        if (!$this->NeedsParsing($html)) {
            return $html;
        }

        $emailPattern = '/([a-zA-Z0-9@%_.~#-\?&]+.\@[a-zA-Z0-9@%_.~#-\?&]+.)/';
        $html = preg_replace_callback($emailPattern,
                                          array(&$this, 'ConvertMail'),
                                          $html);

        return $html;
    }

    /**
     * Callback that replaces all the @ and dots for _at_ and _dot_ string
     *
     * @access  public
     * @param   array  $email The Email to edit
     * @return  string The new email
     */
    function ConvertMail($email)
    {
        $email     = $email[0];
        $atsdots   = array(chr(64), chr(46));
        $magicdots = array('_at_', '_dot_');

        return str_replace($atsdots, $magicdots, $email);
    }
}
?>
