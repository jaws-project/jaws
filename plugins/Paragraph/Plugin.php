<?php
/**
 * Jaws Paragraph plugin
 *
 * @category   Plugin
 * @package    Paragraph
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Paragraph_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version  = '1.0';
    var $_DefaultBackendEnabled  = true;
    var $_DefaultFrontendEnabled = true;

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $text       Text to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($text, $reference = 0, $action = '', $gadget = '')
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
                    $text .= $part;
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

}