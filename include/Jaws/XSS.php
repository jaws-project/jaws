<?php
/**
 * XSS Prevention class
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     David Coallier <davidc@jaws.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XSS
{
    var $safeXSS = null;

    /**
     * Parses the text
     *
     * @access  public
     * @param   string $string String to parse
     * @param   bool   $strict How strict we can be. True will be very strict (default), false
     *                         will allow some attributes (id) and tags (object, applet, embed)
     * @return  string The safe string
     */
    function parse($string, $strict = null)
    {
        //Create safehtml object
        if ($this->safeXSS === null) {
            require_once PEAR_PATH. 'HTML/Safe.php';
            $this->safeXSS = new HTML_Safe();
        }

        if (is_null($strict)) {
            $strict = ($GLOBALS['app']->Registry->Get('/gadgets/Policy/xss_parsing_level') == "paranoid")? true : false;
        }

        $string = $this->safeXSS->parse($string, $strict);
        $this->safeXSS->clear();
        return $string;
    }

    /**
     *
     */
    function filter($string, $noquotes = true)
    {
        $string = htmlspecialchars($string, $noquotes? ENT_QUOTES : ENT_NOQUOTES, 'UTF-8');
        return $string;
    }

    /**
     *
     */
    function defilter($string, $noquotes = true)
    {
        return htmlspecialchars_decode($string, $noquotes? ENT_QUOTES : ENT_NOQUOTES);
    }
}