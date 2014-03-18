<?php
/**
 * Replaces emoticons with images
 *
 * @category   Plugin
 * @package    Emoticons
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emoticons_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = '0.5.1';
    var $_ImagePath = 'plugins/Emoticons/images';
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
        $iconsmap = array(
            ":-D"  => 'face-smile-big.png',   // :-D
            ":'("  => 'face-crying.png',      // :'(
            ":-("  => 'face-sad.png',         // :-(
            ":-)"  => 'face-smile.png',       // :-)
            ":-|"  => 'face-plain.png',       // :-|
            ":-P"  => 'tongue.png',           // :-P
            ":-/"  => 'unsure.png',           // :-/
            ";-)"  => 'face-wink.png',        // ;-)
            "B-)"  => 'face-glasses.png',     // B-)
            "O:-)" => 'face-angel.png',       // O:-)
            ":-*"  => 'face-kiss.png',        // :-*
            ":-O"  => 'face-surprise.png',    // :-O
            ">:-)" => 'face-devil-grin.png',  // >:-)
        );

        $combo =& Piwi::CreateWidget('Combo', 'emoticons');
        $combo->SetTitle(_t('PLUGINS_EMOTICONS_ADD'));
        $combo->AddEvent(ON_CHANGE, "javascript: insertTags('$textarea', this[this.selectedIndex].value,'','');");

        $theme = $GLOBALS['app']->GetTheme();
        $image_path = $theme['path'] . $this->_ImagePath;
        foreach ($iconsmap as $icon => $file) {
            $icons_file_url = $this->_ImagePath . '/' . $file;
            if (is_file($image_path . '/' . $file)) {
                $icons_file_url = $theme['url']. $icons_file_url;
            }
            $combo->AddOption($icon, $icon);
        }

        return $combo;
    }

    /**
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
     */
    function NeedsParsing($html)
    {
        if (preg_match("!(O|>)?(B|:|;)'?-?!Us", $html)) {
            return true;
        }

        return false;
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
        static $iconsmap;
        
        if (!$this->NeedsParsing($html)) {
            return $html;
        }

        if (!isset($iconsmap)) {
            $iconsmap = array('regexps' => array(
                                                 "!O(:|;)-?\)!Us",      // O:-) O:) O;) O;-)
                                                 "!>(:|;)-?(D|\))!Usi", // >:-D >;-D >:-) >;-) >:) >;) >:D >;D
                                                 "!(:|;)-?D!Usi",       // :-D ;-D :D ;D
                                                 "!:-(/|\\\)!Us",       // :-/ :-\
                                                 "!:'\(!Us",            // :'(
                                                 "!(:|;)-?P!Usi",       // :-P :P ;-P ;P
                                                 "!:-?\(!Us",           // :-( :(
                                                 "!:-?\|!Us",           // :-| :|
                                                 "!:-?\)!Us",           // :-) :)
                                                 "!;-?\)!Us",           // ;-) ;)
                                                 "!B-?\)!Us",           // B-) B)
                                                 "!(:|;|=)-?\*!Us",     // :* :-* ;* ;-* =*
                                                 "!:-?O!Usi",           // :-O :O
                                                 ),
                              'images'  => array(
                                                 'face-angel.png',
                                                 'face-devil-grin.png',
                                                 'face-smile-big.png',
                                                 'unsure.png',
                                                 'face-crying.png', 
                                                 'tongue.png', 
                                                 'face-sad.png',
                                                 'face-plain.png',
                                                 'face-smile.png',
                                                 'face-wink.png',
                                                 'face-glasses.png',
                                                 'face-kiss.png',
                                                 'face-surprise.png',
                                                 )
                          );
            $theme = $GLOBALS['app']->GetTheme();
            $image_path = $theme['path'] . $this->_ImagePath;
            for($i=0; $i<count($iconsmap['images']); $i++) {
                $icons_dir_url = '';
                if (is_file($image_path . '/' . $iconsmap['images'][$i])) {
                    $icons_dir_url = $theme['url'];
                }

                $text = '<img src="' . $icons_dir_url . $this->_ImagePath . '/' . $iconsmap['images'][$i] . '" '.
                    'border="0" alt="' . $iconsmap['images'][$i] .' " width="16" height="16" />';
                $iconsmap['images'][$i] = $text;
            }
        }
        
        //Get all tags and no tags :), ala WP style but in a clean way ;-)
        $htmlTags = preg_split("/(<.*>)/U", $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $newHTML  = '';
        foreach($htmlTags as $tagContent) {
            //If tagContent is a tag (starts with <) don't parse it
            if ((strlen($tagContent) > 0) && ($tagContent{0} != '<')) { 
                $tagContent = preg_replace($iconsmap['regexps'], $iconsmap['images'], $tagContent);
            }
            $newHTML .= $tagContent;
        }
        return $newHTML;
    }

}