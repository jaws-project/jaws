<?php
/**
 * Jaws BBCode plugin
 *
 * @category   Plugin
 * @package    BBCode
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BBCode_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version  = '0.1';
    var $frontendEnabled = true;

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
        $bold->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[b]','[/b]','');");
        $bold->SetTitle($this->plugin::t('BOLD_SAMPLE'));
        $bold->SetId('');
        $bold->SetName('');

        $italic =& Piwi::CreateWidget('Button', 'italic', '<em>i</em>');
        $italic->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[i]','[/i]','');");
        $italic->SetTitle($this->plugin::t('ITALIC_SAMPLE'));
        $italic->SetId('');
        $italic->SetName('');

        $underline =& Piwi::CreateWidget('Button', 'underline', '<u>u</u>');
        $underline->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[u]','[/u]','');");
        $underline->SetTitle($this->plugin::t('UNDERLINE_SAMPLE'));
        $underline->SetId('');
        $underline->SetName('');

        $strike =& Piwi::CreateWidget('Button', 'strike', '<s>s</s>');
        $strike->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[s]','[/s]','');");
        $strike->SetTitle($this->plugin::t('STRIKE_SAMPLE'));
        $strike->SetId('');
        $strike->SetName('');

        $quote =& Piwi::CreateWidget('Button', 'quote', 'Quote');
        $quote->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[quote]','[/quote]','');");
        $quote->SetTitle($this->plugin::t('QUOTE_SAMPLE'));
        $quote->SetId('');
        $quote->SetName('');

        $code =& Piwi::CreateWidget('Button', 'code', 'Code');
        $code->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[code]','[/code]','');");
        $code->SetTitle($this->plugin::t('CODE_SAMPLE'));
        $code->SetId('');
        $code->SetName('');

        $image =& Piwi::CreateWidget('Button', 'image', 'Image');
        $image->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[img]','[/img]','');");
        $image->SetTitle($this->plugin::t('IMAGE_SAMPLE'));
        $image->SetId('');
        $image->SetName('');

        $url =& Piwi::CreateWidget('Button', 'url', 'URL');
        $url->AddEvent(ON_CLICK, "javascript:insertTags('$textarea', '[url]','[/url]','');");
        $url->SetTitle($this->plugin::t('URL_SAMPLE'));
        $url->SetId('');
        $url->SetName('');

        $size =& Piwi::CreateWidget('Combo', 'size');
        $size->AddEvent(
            ON_CHANGE,
            "javascript:insertTags('$textarea', '[size='+this[this.selectedIndex].value+']','[/size]','');"
        );
        $size->SetTitle($this->plugin::t('SIZE_SAMPLE'));
        $size->AddOption($this->plugin::t('SIZE_TINY'),    8);
        $size->AddOption($this->plugin::t('SIZE_SMALL'),   11);
        $size->AddOption($this->plugin::t('SIZE_NORMALL'), 13);
        $size->AddOption($this->plugin::t('SIZE_LARGE'),   16);
        $size->AddOption($this->plugin::t('SIZE_HUGE'),    18);
        $size->SetDefault(13);
        $size->SetId('');
        $size->SetName('');

        $color =& Piwi::CreateWidget('Combo', 'color');
        $color->AddEvent(
            ON_CHANGE,
            "javascript:insertTags('$textarea', '[color='+this[this.selectedIndex].value+']','[/color]','');"
        );
        $color->SetTitle($this->plugin::t('COLOR_SAMPLE'));
        $color->AddOption($this->plugin::t('COLOR_000000'), '#000000');
        $color->AddOption($this->plugin::t('COLOR_0000FF'), '#0000FF');
        $color->AddOption($this->plugin::t('COLOR_00FFFF'), '#00FFFF');
        $color->AddOption($this->plugin::t('COLOR_00FF00'), '#00FF00');
        $color->AddOption($this->plugin::t('COLOR_FFFF00'), '#FFFF00');
        $color->AddOption($this->plugin::t('COLOR_FF0000'), '#FF0000');
        $color->AddOption($this->plugin::t('COLOR_FF00FF'), '#FF00FF');
        $color->AddOption($this->plugin::t('COLOR_FFFFFF'), '#FFFFFF');
        $color->SetDefault('#0000FF');
        $color->SetId('');
        $color->SetName('');

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
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
    {
        while (preg_match_all('#\[(\w+|\*)[=]?(.*?)\][\n]?(.+?)\[/\1\]#isu', $html, $matches)) {
            foreach ($matches[0] as $key => $match) {
                list($tag, $params, $innertext) = array(
                    $matches[1][$key],
                    $matches[2][$key],
                    $matches[3][$key]
                );

                $params = explode(' ', $params);
                $first  = array_shift($params);
                $params = implode(' ', $params);
                switch ($tag) {
                    case 'dir':
                        $replacement = "<div dir=\"$first\">$innertext</div>";
                        break;

                    case 'indent':
                        $first = floatval($first);
                        $style = 'margin-'. (!empty($params)? $params : 'left');
                        $replacement = "<div style=\"$style:{$first}em;\">$innertext</div>";
                        break;

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
                    case 'li':
                    case 'tr':
                    case 'th':
                    case 'td':
                    case 'sup':
                    case 'sub':
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
                        $tag = empty($first)? 'ul' : 'ol';
                    case 'ul':
                    case 'ol':
                        switch ($first) {
                            case '1':
                                $replacement = "<ol style=\"list-style-type:decimal;\">$innertext</ol>";
                                break;
                            case 'a':
                                $replacement = "<ol style=\"list-style-type:lower-alpha;\">$innertext</ol>";
                                break;
                            case 'A':
                                $replacement = "<ol style=\"list-style-type:upper-alpha;\">$innertext</ol>";
                                break;
                            case 'i':
                                $replacement = "<ol style=\"list-style-type:lower-roman;\">$innertext</ol>";
                                break;
                            case 'I':
                                $replacement = "<ol style=\"list-style-type:upper-roman;\">$innertext</ol>";
                                break;
                            default:
                                $replacement = "<$tag>$innertext</$tag>";
                        }
                        break;

                    case '*':
                        $replacement = "<li>$innertext</li>";
                        break;

                    case 'size':
                        $first.= is_numeric($first)? 'px' : '';
                        $replacement = "<span style=\"font-size:{$first};\">$innertext</span>";
                        break;

                    case 'color':
                        $replacement = "<span style=\"color:$first;\">$innertext</span>";
                        break;

                    case 'bgcolor':
                        $replacement = "<span style=\"background-color:$first;\">$innertext</span>";
                        break;

                    case 'align':
                        $replacement = "<div style=\"text-align:$first;\">$innertext</div>";
                        break;

                    case 'quote':
                        $replacement = $first? "<cite>$first:</cite>" : '';
                        $replacement = "<blockquote>$replacement<div>$innertext</div></blockquote>";
                        break;

                    case 'url':
                        $replacement = '<a href="'.
                            ($first? $first : $innertext).
                            "\" rel=\"nofollow\">$innertext</a>";
                        break;

                    case 'email':
                        $replacement = '<a href="mailto:'.
                            ($first? $first : $innertext).
                            "\">$innertext</a>";
                        break;

                    case 'img':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $replacement = "<img src=\"$innertext\" ";
                        $replacement.= is_numeric($width)? "width=\"$width\" " : '';
                        $replacement.= is_numeric($height)? "height=\"$height\" " : '';
                        $replacement.= !empty($params)? "alt=\"$params\" " : '';
                        $replacement.= '/>';
                        break;

                    case 'media':
                        @list($width, $height) = preg_split('#x#i', $first);
                        $mSource = 'youtube';
                        if (!empty($params)) {
                            $mSource = preg_replace('/[^[:alnum:]_-]/', '', $params);
                        }
                        $replacement = '';
                        $mSourcePath = ROOT_JAWS_PATH. "plugins/BBCode/Templates/$mSource.html";
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

                        $replacement = "<audio $size". $params. ' controls>';
                        $replacement.= "<source src=\"$innertext.ogg\" type=\"audio/ogg\">";
                        $replacement.= "<source src=\"$innertext.mp3\" type=\"audio/mpeg\">";
                        $replacement.= "<source src=\"$innertext.wav\" type=\"audio/wav\">";
                        $replacement.= 'Your browser does not support the HTML5 audio tag.';
                        $replacement.= '</audio>';
                        break;

                    default:
                        $replacement = str_replace(array('[', ']'), array('&lsqb;', '&rsqb;'), $match);
                }

                $html = str_replace($match, $replacement, $html);
            }
        }

        return $html;
    }

}