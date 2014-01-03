<?php
/**
 * XSS Prevention class
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     David Coallier <david@echolibre.com>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XSS
{
    /**
     * Parses the text
     *
     * @access  public
     * @param   string $string String to parse
     * @param   bool   $strict How strict we can be. True will be very strict (default), false
     *                         will allow some attributes (id) and tags (object, applet, embed)
     * @return  string The safe string
     */
    static function parse($string, $strict = null)
    {
        static $safe_xss;
        static $xss_parsing_level;
        if (!isset($safe_xss)) {
            $xss_parsing_level = $GLOBALS['app']->Registry->fetch('xss_parsing_level', 'Policy');

            //Create safehtml object
            require_once PEAR_PATH. 'HTML/Safe.php';
            $safe_xss = new HTML_Safe();
        }

        if (is_null($strict)) {
            $strict = ($xss_parsing_level == "paranoid");
        }

        $string = $safe_xss->parse($string, $strict);
        $safe_xss->clear();
        return $string;
    }


    /**
     * Convert special characters to HTML entities
     *
     * @access  public
     * @param   string  $string     The string being converted
     * @param   bool    $noquote    Will leave both double and single quotes unconverted
     * @return  string  The converted string
     */
    static function filter($string, $noquotes = false)
    {
        return htmlspecialchars($string, $noquotes? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }


    /**
     * Convert special HTML entities back to characters
     *
     * @access  public
     * @param   string  $string     The string to decode
     * @param   bool    $noquote    Will leave both double and single quotes unconverted
     * @return  string  Returns the decoded string
     */
    static function defilter($string, $noquotes = false)
    {
        return htmlspecialchars_decode($string, $noquotes? ENT_NOQUOTES : ENT_QUOTES);
    }

}