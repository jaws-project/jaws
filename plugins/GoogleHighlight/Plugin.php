<?php
/**
 * Highlights words when google does a search in the page (s=foo)
 *
 * @category   Plugin
 * @package    GoogleHighlight
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class GoogleHighlight_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version = '0.1';

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return $html;
        }

        $referer = $_SERVER['HTTP_REFERER'];
        if (preg_match('|^http://(www)?\.?google.*|i', $referer)) {
            //Based on a wp-plugin
            $query_terms = preg_replace('/^.*q=([^&]+)&?.*$/i','$1', $referer);
            $query_terms = preg_replace('/\'|"/', '', $query_terms);
            $query_array = preg_split("/[\s,\+\.]+/", $query_terms);

            foreach ($query_array as $word) {
                if (!empty($word) && $word != ' ') {
                    $word = preg_quote($word, '/');

                    if (!preg_match('/<.+>/', $html)) {
                        $html = preg_replace('/(\b' . $word . '\b)/i',
                                                '<span class="google_highlight">$1</span>',
                                                $html);
                    } else {
                        $html = preg_replace('/(?<=>)([^<]+)?(\b' . $word . '\b)/i',
                                                '$1<span class="google_highlight">$2</span>',
                                                $html);
                    }
                }
            }
        }

        return $html;
    }

}