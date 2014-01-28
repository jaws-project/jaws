<?php
/**
 * Class to deal with strings
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_String
{
    /**
     * Return the string with the valid HTML tags
     * Of course this doesn't strip out id="" class="" and similar
     *
     * @param   string  $string  Input string with lot of HTML tags
     * @return  string  Clean string, with just some allowed tags
     * @access  public
     */
    function WithHTML($string)
    {
        $allowed_tags = '<a><b><i><u><br><pre>';
        $string = strip_tags($string, $allowed_tags);
        return $string;
    }

    /**
     * Parses the text, adding paragraph tags when is needed
     *
     * @param   string  $text  Text to parse
     * @return  string  The parsed text
     * @access  public
     * Based on: http://photomatt.net/scripts/autop and other sites that are using autop
     */
    static function AutoParagraph(&$text)
    {
        // cross-platform newlines
        $text = preg_replace("/(\r\n|\r)/", "\n", $text);

        // All blocks level tags
        $blocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|code|select|';
        $blocks.= 'option|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|noscript|legend|section|';
        $blocks.= 'article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
        $parts = preg_split(
            '@(</?(?:pre|code|script|style|object|iframe|!--)[^>]*>|<!--.*?-->)@i',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        $ignore = false;
        $ignoretag = '';
        $text = '';
        foreach ($parts as $i => $part) {
            if ($i % 2) {
                // skip comments
                $comment = (substr($part, 0, 4) == '<!--');
                if ($comment) {
                    $output .= $part;
                    continue;
                }
                // Opening or closing tag?
                $open = ($part[1] != '/');
                list($tag) = preg_split('/[ >]/', substr($part, 2 - $open), 2);
                if (!$ignore) {
                    if ($open) {
                        $ignore = true;
                        $ignoretag = $tag;
                    }
                } elseif (!$open && $ignoretag == $tag) { // Only allow a matching tag to close it.
                    $ignore = false;
                    $ignoretag = '';
                }
            } elseif (!$ignore) {
                $part = preg_replace('|<br />\s*<br />|', "\n\n", $part);
                // Space things out a little
                //$part = preg_replace('!(<' . $blocks . '[^>]*>)!', "\n$1", $part);
                // Space things out a little
                //$part = preg_replace('!(</' . $blocks . '>)!', "$1\n", $part);
                // take care of duplicates
                //$part = preg_replace("/\n\n+/", "\n\n", $part);
                // make paragraphs, including one at the end
                //$part = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $part);
                // problem with nested lists
                $part = preg_replace("|<p>(<li.+?)</p>|", "$1", $part);
                $part = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $part);
                $part = str_replace('</blockquote></p>', '</p></blockquote>', $part);
                // under certain strange conditions it could create a P of entirely whitespace
                $part = preg_replace('|<p>[\s,\t,\xa0]*</p>\n*|u', '', $part);
                $part = preg_replace('!<p>\s*(</?' . $blocks . '[^>]*>)!', "<p>$1", $part);
                $part = preg_replace('!(</?' . $blocks . '[^>]*>)\s*</p>!', "$1</p>", $part);
                // make line breaks
                $part = preg_replace('|(?<!<br />)\n|', "<br />\n", $part);
                $part = preg_replace('!(</?' . $blocks . '[^>]*>)\s*<br />!', "$1", $part);
                $part = preg_replace(
                    '!(<br />)?(\s*</?(?:p|li|div|dl|dd|dt|th|pre|tr|td|ul|ol)[^>]*>\s*)(<br />)?!',
                    '$2',
                    $part
                );
                $part = preg_replace('/&([^#])(?![A-Za-z0-9]{1,8};)/', '&amp;$1', $part);
            }
            $text .= $part;
        }

        return $text;
    }

    /**
     * Cleans all the accents and converts all those 'funny' unicode
     * chars to readable ones.
     *
     * For example it will convert: Þormar to Thormar.
     *
     * @access  public
     * @param   string  $str   String to clean
     * @return  string  Clean string
     */
    function clean($str)
    {
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(198) => 'AE',  //capital AE
                                 Jaws_UTF8::chr(208) => 'DH',  //capital eth
                                 Jaws_UTF8::chr(216) => 'OE',  //capital O with Stroke
                                 Jaws_UTF8::chr(222) => 'Th', //capital thorn
                                 Jaws_UTF8::chr(223) => 'ss',   //sharp s
                                 Jaws_UTF8::chr(230) => 'ae',  //lower AE
                                 Jaws_UTF8::chr(240) => 'dh',  //lower eth
                                 Jaws_UTF8::chr(248) => 'oe',  //lower O with Stroke
                                 Jaws_UTF8::chr(254) => 'th', //lower thorn
                                 Jaws_UTF8::chr(255) => 'y',   //y umlaut
                                 Jaws_UTF8::chr(253) => 'y',   //y acute
                                 Jaws_UTF8::chr(231) => 'c',   //c cedilla
                                 Jaws_UTF8::chr(199) => 'C',   //capital c cedilla
                                 Jaws_UTF8::chr(181) => 'u',   //Micro sign
                                 Jaws_UTF8::chr(241) => 'n',   //n tilde
                                 Jaws_UTF8::chr(209) => 'n',   //capital n tilde
                                 Jaws_UTF8::chr(248) => 'o',   //o slash
                                 )
                     );
        //Letter A
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(192) => 'A', Jaws_UTF8::chr(193) => 'A', Jaws_UTF8::chr(194) => 'A',
                                 Jaws_UTF8::chr(195) => 'A', Jaws_UTF8::chr(196) => 'A', Jaws_UTF8::chr(197) => 'A'));
        //Letter E
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(200) => 'E', Jaws_UTF8::chr(201) => 'E', Jaws_UTF8::chr(202) => 'E',
                                 Jaws_UTF8::chr(203) => 'E'));
        //Letter I
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(204) => 'I', Jaws_UTF8::chr(205) => 'I', Jaws_UTF8::chr(206) => 'I',
                                 Jaws_UTF8::chr(207) => 'I'));
        //Letter O
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(210) => 'O', Jaws_UTF8::chr(211) => 'O', Jaws_UTF8::chr(212) => 'O',
                                 Jaws_UTF8::chr(213) => 'O', Jaws_UTF8::chr(214) => 'O', Jaws_UTF8::chr(216) => 'O'));
        //Letter U
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(217) => 'U', Jaws_UTF8::chr(218) => 'U', Jaws_UTF8::chr(219) => 'U',
                                 Jaws_UTF8::chr(220) => 'U'));
        //Letter a
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(224) => 'a', Jaws_UTF8::chr(225) => 'a', Jaws_UTF8::chr(226) => 'a',
                                 Jaws_UTF8::chr(227) => 'a', Jaws_UTF8::chr(228) => 'a', Jaws_UTF8::chr(229) => 'a'));
        //Letter e
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(232) => 'e', Jaws_UTF8::chr(233) => 'e', Jaws_UTF8::chr(234) => 'e',
                                 Jaws_UTF8::chr(235) => 'e'));
        //Letter i
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(236) => 'i', Jaws_UTF8::chr(237) => 'i', Jaws_UTF8::chr(238) => 'i',
                                 Jaws_UTF8::chr(239) => 'i'));
        //Letter o
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(242) => 'o', Jaws_UTF8::chr(243) => 'o', Jaws_UTF8::chr(244) => 'o',
                                 Jaws_UTF8::chr(245) => 'o', Jaws_UTF8::chr(246) => 'o', Jaws_UTF8::chr(248) => 'o'));
        //Letter u
        $str = strtr($str, array(
                                 Jaws_UTF8::chr(249) => 'u', Jaws_UTF8::chr(250) => 'u', Jaws_UTF8::chr(251) => 'u',
                                 Jaws_UTF8::chr(252) => 'u'));
        return $str;
    }

}
