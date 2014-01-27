<?php
/**
 * Jaws BBCode plugin
 *
 * @category   Plugin
 * @package    BBCode
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BBCode_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version  = '0.1';
    var $_DefaultFrontendEnabled = true;

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $buttonbox =& Piwi::CreateWidget('Division');

        $bold =& Piwi::CreateWidget('Button', 'bold', '<strong>B</strong>');
        $bold->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[b]','[/b]','');");
        $bold->SetTitle(_t('PLUGINS_BBCODE_BOLD_SAMPLE'));

        $italic =& Piwi::CreateWidget('Button', 'italic', '<em>i</em>');
        $italic->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[i]','[/i]','');");
        $italic->SetTitle(_t('PLUGINS_BBCODE_ITALIC_SAMPLE'));

        $underline =& Piwi::CreateWidget('Button', 'underline', '<u>u</u>');
        $underline->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[u]','[/u]','');");
        $underline->SetTitle(_t('PLUGINS_BBCODE_UNDERLINE_SAMPLE'));

        $strike =& Piwi::CreateWidget('Button', 'strike', '<s>s</s>');
        $strike->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[s]','[/s]','');");
        $strike->SetTitle(_t('PLUGINS_BBCODE_STRIKE_SAMPLE'));

        $quote =& Piwi::CreateWidget('Button', 'quote', 'Quote');
        $quote->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[quote]','[/quote]','');");
        $quote->SetTitle(_t('PLUGINS_BBCODE_QUOTE_SAMPLE'));

        $code =& Piwi::CreateWidget('Button', 'code', 'Code');
        $code->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[code]','[/code]','');");
        $code->SetTitle(_t('PLUGINS_BBCODE_CODE_SAMPLE'));

        $image =& Piwi::CreateWidget('Button', 'image', 'Image');
        $image->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[img]','[/img]','');");
        $image->SetTitle(_t('PLUGINS_BBCODE_IMAGE_SAMPLE'));

        $url =& Piwi::CreateWidget('Button', 'url', 'URL');
        $url->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[url]','[/url]','');");
        $url->SetTitle(_t('PLUGINS_BBCODE_URL_SAMPLE'));

        $size =& Piwi::CreateWidget('Combo', 'size');
        $size->AddEvent(ON_CHANGE, "javascript: insertTags('$textarea', '[size='+this[this.selectedIndex].value+']','[/size]','');");
        $size->SetTitle(_t('PLUGINS_BBCODE_SIZE_SAMPLE'));
        $size->AddOption(_t('PLUGINS_BBCODE_SIZE_TINY'),    8);
        $size->AddOption(_t('PLUGINS_BBCODE_SIZE_SMALL'),   11);
        $size->AddOption(_t('PLUGINS_BBCODE_SIZE_NORMALL'), 13);
        $size->AddOption(_t('PLUGINS_BBCODE_SIZE_LARGE'),   16);
        $size->AddOption(_t('PLUGINS_BBCODE_SIZE_HUGE'),    18);
        $size->SetDefault(13);

        $color =& Piwi::CreateWidget('Combo', 'color');
        $color->AddEvent(ON_CHANGE, "javascript: insertTags('$textarea', '[color='+this[this.selectedIndex].value+']','[/color]','');");
        $color->SetTitle(_t('PLUGINS_BBCODE_COLOR_SAMPLE'));
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_000000'), '#000000');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_0000FF'), '#0000FF');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_00FFFF'), '#00FFFF');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_00FF00'), '#00FF00');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_FFFF00'), '#FFFF00');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_FF0000'), '#FF0000');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_FF00FF'), '#FF00FF');
        $color->AddOption(_t('PLUGINS_BBCODE_COLOR_FFFFFF'), '#FFFFFF');
        $color->SetDefault('#0000FF');

        $buttonbox->PackStart($bold);
        $buttonbox->PackStart($italic);
        $buttonbox->PackStart($underline);
        $buttonbox->PackStart($strike);
        $buttonbox->PackStart($quote);
        $buttonbox->PackStart($code);
        $buttonbox->PackStart($image);
        $buttonbox->PackStart($url);
        $buttonbox->PackStart($size);
        $buttonbox->PackStart($color);
        return $buttonbox;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        $tags = 'media|video|audio|size|url|img|code|noparse|list|color|left|center|justify|right|';
        $tags.= 'quote|table|tr|th|td|ul|ol|li|hr|b|i|s|u|h|\*';
        while (preg_match_all('#\[((\w+)|(\*))(.*?)\][\n]?(.+?)(?(3)(\[/\*\]|\n)|(\[/\1\]))#isu', $html, $matches)) {
            foreach ($matches[0] as $key => $match) {
                list($tag, $params, $innertext) = array(
                    $matches[1][$key],
                    $matches[4][$key],
                    $matches[5][$key]
                );

                $params = trim($params, '=');
                $params = explode(' =', $params);
                $first  = array_shift($params);
                switch ($tag) {
                    case 'h':
                        $first = ((int)$first == 0)? 3 : (int)$first;
                        $replacement = "<h$first>$innertext</h$first>";
                        break;

                    case 'hr':
                        $replacement = "<hr/>";
                        break;

                    case 'b':
                        $replacement = "<strong>$innertext</strong>";
                        break;

                    case 'i':
                        $replacement = "<em>$innertext</em>";
                        break;

                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                    case 's':
                    case 'u':
                    case 'ol':
                    case 'ul':
                    case 'li':
                    case 'tr':
                    case 'th':
                    case 'td':
                    case 'table':
                        $replacement = "<$tag>$innertext</$tag>";
                        break;

                    case 'code':
                    case 'noparse':
                        $replacement = '<pre><code>'. str_replace(
                                array('[', ']', '{', '}'),
                                array('&lsqb;', '&rsqb;', '&lcub;', '&rcub;'),
                                $innertext
                            ). '</code></pre>';
                        break;

                    case 'list':
                        $replacement = "<ul>$innertext</ul>";
                        break;

                    case '*':
                        $replacement = "<li>$innertext</li>";
                        break;

                    case 'size':
                        $replacement = "<span style=\"font-size:{$first}px;\">$innertext</span>";
                        break;

                    case 'color':
                        $replacement = "<span style=\"color:$first;\">$innertext</span>";
                        break;

                    case 'left':
                    case 'center':
                    case 'justify':
                    case 'right':
                        $replacement = "<div style=\"text-align:$tag;\">$innertext</div>";
                        break;

                    case 'quote':
                        $replacement = $first? "<cite>$first:</cite>" : '';
                        $replacement = "<blockquote>$replacement<div>$innertext</div></blockquote>";
                        break;

                    case 'url':
                        $replacement = '<a href="' . ($first? $first : $innertext) . "\" rel=\"nofollow\">$innertext</a>";
                        break;

                    case 'img':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $replacement = "<img src=\"$innertext\" ";
                        $replacement.= is_numeric($width)? "width=\"$width\" " : '';
                        $replacement.= is_numeric($height)? "height=\"$height\" " : '';
                        $replacement.= !empty($params)? "alt=\"$params[0]\" " : '';
                        $replacement.= '/>';
                        break;

                    case 'media':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $mSource = 'youtube';
                        if (!empty($params)) {
                            $mSource = preg_replace('/[^[:alnum:]_-]/', '', $params[0]);
                        }
                        $replacement = '';
                        $mSourcePath = JAWS_PATH. "plugins/BBCode/Templates/$mSource.html";
                        if (file_exists($mSourcePath)) {
                            $tpl = new Jaws_Template();
                            $tpl->Load("$mSource.html", 'plugins/BBCode/Templates/');
                            $tpl->SetBlock('media');
                            $tpl->SetVariable('vid', $innertext);
                            if (is_numeric($width)) {
                                $tpl->SetBlock('media/width');
                                $tpl->SetVariable('width', $width);
                                $tpl->ParseBlock('media/width');
                            }
                            if (is_numeric($height)) {
                                $tpl->SetBlock('media/height');
                                $tpl->SetVariable('height', $height);
                                $tpl->ParseBlock('media/height');
                            }
                            $tpl->ParseBlock('media');
                            $replacement = $tpl->Get();
                        }
                        break;

                    case 'video':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $size = is_numeric($width)? "width=\"$width\" " : '';
                        $size.= is_numeric($height)? "height=\"$height\" " : '';

                        $replacement = "<video $size controls>";
                        $replacement.= "<source src=\"$innertext.ogg\" type=\"video/ogg\">";
                        $replacement.= "<source src=\"$innertext.webm\" type=\"video/webm\">";
                        $replacement.= "<source src=\"$innertext.mp4\" type=\"video/mp4\">";
                        $replacement.= 'Your browser does not support the HTML5 video tag.';
                        $replacement.= '</video>';
                        break;

                    case 'audio':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $size = is_numeric($width)? "style='width:{$width}px;' " : '';

                        $replacement = "<audio $size". implode(' ', $params).' controls>';
                        $replacement.= "<source src=\"$innertext.ogg\" type=\"audio/ogg\">";
                        $replacement.= "<source src=\"$innertext.mp3\" type=\"audio/mpeg\">";
                        $replacement.= "<source src=\"$innertext.wav\" type=\"audio/wav\">";
                        $replacement.= 'Your browser does not support the HTML5 audio tag.';
                        $replacement.= '</audio>';
                        break;

                    default:
                        $replacement = str_replace(
                                array('[', ']', '{', '}'),
                                array('&lsqb;', '&rsqb;', '&lcub;', '&rcub;'),
                                $match
                            );
                }

                $html = str_replace($match, $replacement, $html);
            }
        }

        return $html;
    }

}