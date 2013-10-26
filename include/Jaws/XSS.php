<?php
/**
 * XSS Prevention class
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     David Coallier <david@echolibre.com>
 * @copyright  2005-2013 Jaws Development Group
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
     *
     */
    static function filter($string, $noquotes = true)
    {
        return htmlspecialchars($string, $noquotes? ENT_QUOTES : ENT_NOQUOTES, 'UTF-8');
    }

    /**
     *
     */
    static function defilter($string, $noquotes = true)
    {
        return htmlspecialchars_decode($string, $noquotes? ENT_QUOTES : ENT_NOQUOTES);
    }

}