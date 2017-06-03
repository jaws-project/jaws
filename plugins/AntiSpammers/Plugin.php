<?php
/**
 * Replaces every email address with 'at' and 'dot' strings
 *
 * @category   Plugin
 * @package    AntiSpammers
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AntiSpammers_Plugin
{
    var $friendly = false; //no bbcode
    var $version = '0.3';

    /**
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
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
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
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
     * Performs the conversion
     *
     * @access  public
     * @param   array   $email   The Email address to be converted
     * @return  string  Converted email address
     */
    function ConvertMail($email)
    {
        $email     = $email[0];
        $atsdots   = array(chr(64), chr(46));
        $magicdots = array('_at_', '_dot_');

        return str_replace($atsdots, $magicdots, $email);
    }

}