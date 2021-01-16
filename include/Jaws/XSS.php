<?php
/**
 * XSS Prevention class
 *
 * @category    JawsType
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      David Coallier <david@echolibre.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XSS
{
    /**
     * Allowed HTML tags
     *
     * @var     array
     * @access  private
     */
    private $allowed_tags = array(
        'html', 'body', 'br', 'a', 'img', 'ol', 'ul', 'li', 'blockquote', 'cite', 'code', 'div', 'p',
        'pre', 'span', 'del', 'ins', 'strong', 'b', 'mark', 'i', 's', 'u', 'em', 'strike', 'table',
        'tbody', 'thead', 'tfoot', 'th', 'tr', 'td', 'font', 'center'
    );

    /**
     * Allowed HTML tag attributes
     *
     * @var array
     * @access  private
     */
    private $allowed_attributes = array(
        'href', 'src', 'alt', 'title', 'style', 'class', 'dir',
        'height', 'width', 'rowspan', 'colspan', 'align', 'valign',
        'rows', 'cols', 'color', 'bgcolor', 'border'
    );

    /**
     *  URL based HTML tag attributes
     *
     * @var     array
     * @access  private
     */
    private $urlbased_attributes = array('href', 'src');

    /**
     * Allowed URL pattern
     *
     * @var     string
     * @access  private
     */
    private $allowed_url_pattern = "@(^[(http|https|ftp)://]?)(?!javascript:)([^\\\\[:space:]\"]+)$@iu";

    /**
     * Allowed style pattern
     *
     * @var     string
     * @access  private
     */
    private $allowed_style_pattern = array(
        '/^(',
        '(\s*(background\-)?color\s*:\s*#[0-9A-Fa-f]+[\s|;]*)',
        '|',
        '((\s*(width)|(height)|(margin\-left)|(margin\-right)|(font\-size))\s*:\s*\d+((px)|(em)|(pt))?[\s|;]*)',
        '|',
        '((\s*(text\-align))\s*:\s*((left)|(right)|(center)|(justify))?[\s|;]*)',
        ')+$/',
    );

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    private function __construct()
    {
        // join pattern parts together
        $this->allowed_style_pattern = implode('', $this->allowed_style_pattern);
    }

    /**
     * Creates the Jaws_Request instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance()
    {
        static $objRequest;
        if (!isset($objRequest)) {
            $objRequest = new Jaws_XSS();
        }

        return $objRequest;
    }

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
            $xss_parsing_level = Jaws::getInstance()->registry->fetch('xss_parsing_level', 'Policy');

            //Create safe html object
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
     * striping XSS
     *
     * @access  private
     * @param   object  DOMDocument object
     * @return  void
     */
    private function stripXSS(&$hDoc)
    {
        $i = 0;
        do {
            $node = $hDoc->childNodes->item($i);
            if ($node->hasChildNodes()) {
                $this->stripXSS($node);
            }

            // removing XML node
            if ($node->nodeType === XML_PI_NODE) {
                $hDoc->removeChild($node);
                continue;
            }

            if ($node->nodeType === XML_ELEMENT_NODE) {
                if (!in_array($node->tagName, $this->allowed_tags)) {
                    $hDoc->removeChild($node);
                    continue;
                }
                // parsing tag attributes
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attr) {
                        // removing not allowed attributes
                        if (!in_array($attr->name, $this->allowed_attributes)) {
                            $node->removeAttributeNode($attr);
                        } elseif (in_array($attr->name, $this->urlbased_attributes)) {
                            // removing dangerous url based attributes
                            if (!preg_match($this->allowed_url_pattern, $attr->value)) {
                                $node->removeAttributeNode($attr);
                            }
                        } elseif ($attr->name === 'style') {
                            // removing dangerous style attribute
                            if (!preg_match($this->allowed_style_pattern, $attr->value)) {
                                $node->removeAttributeNode($attr);
                            }
                        }
                    }
                }
            }

            $i++;
        } while ($i < $hDoc->childNodes->length);
    }

    /**
     * Parses the text
     *
     * @access  public
     * @param   string $string String to parse
     * @param   bool   $strict How strict we can be. True will be very strict (default), false
     *                         will allow some attributes (id) and tags (object, applet, embed)
     * @return  string The safe string
     */
    function strip($text)
    {
        $result = '';
        $hDoc = new DOMDocument();
        libxml_use_internal_errors(true);
        if ($hDoc->loadHTML('<?xml encoding="UTF-8">' . $text, LIBXML_COMPACT | LIBXML_NOERROR | LIBXML_NOWARNING))
        {
            $this->stripXSS($hDoc);
            $result = $hDoc->saveHTML($hDoc->documentElement);
        }

        libxml_clear_errors();
        return $result;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @access  public
     * @param   string  $string     The string being converted
     * @param   bool    $noquotes   Will leave both double and single quotes unconverted
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
     * @param   bool    $noquotes   Will leave both double and single quotes unconverted
     * @return  string  Returns the decoded string
     */
    static function defilter($string, $noquotes = false)
    {
        return htmlspecialchars_decode($string, $noquotes? ENT_NOQUOTES : ENT_QUOTES);
    }

    /**
     * Convert special characters to HTML entities
     *
     * @access  public
     * @param   string  $string     The string to decode
     * @param   bool    $noquotes   Will leave both double and single quotes unconverted
     * @return  string  Returns the decoded string
     */
    static function refilter($string, $noquotes = false)
    {
        return self::filter(self::defilter($string, $noquotes), $noquotes);
    }

    /**
     * Filter URL
     *
     * @access  public
     * @param   string  $url                URL
     * @param   bool    $deny_remote_url    Deny remote URL
     * @param   bool    $urlencoded         URL is encoded?
     * @return  string  Returns filtered URL
     */
    static function filterURL($url, $urlencoded = false, $deny_remote_url = false)
    {
        // parse & encode given url
        if (false === $parsedURL = parse_url(htmlspecialchars_decode($url))) {
            return '';
        }

        foreach ($parsedURL as $part => $value) {
            if ($deny_remote_url &&
                in_array($part, array('schema', 'host', 'port'))
            ) {
                $parsedURL[$part] = null;
                continue;
            }

            if (in_array($part, array('host', 'path', 'query', 'fragment'))) {
                if ($urlencoded) {
                    // for security reason we must re-encode url
                    $parsedURL[$part] = implode(
                        '/',
                        array_map('rawurlencode', array_map('rawurldecode', explode('/', $value)))
                    );
                } else {
                    $parsedURL[$part] = implode('/', array_map('rawurlencode', explode('/', $value)));
                }
            }
        }

        // prevent encode ,|=|&
        return str_replace(array('%2C', '%3D', '%26'), array(',', '=', '&'), build_url($parsedURL));
    }

    /**
     * Filter back URL
     *
     * @access  public
     * @param   string  $url    URL
     * @return  string  Returns defilter URL
     */
    static function defilterURL($url)
    {
        return rawurldecode(str_replace(array(',', '=', '&'), array('%2C', '%3D', '%26'), $url));
    }

}