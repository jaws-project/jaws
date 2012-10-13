<?php
/**
 * Jaws TinyMCE Wrapper (uses JS and disable plugins)
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';
class Jaws_Widgets_TinyMCE extends Container
{
    /**
     * @access  public
     * @var     object
     */
    var $TextArea;

    /**
     * @access  private
     * @var     object
     */
    var $_Name;

    /**
     * @access  private
     * @var     object
     */
    var $_Class;

    /**
     * @access  private
     * @var     object
     * @see     function  GetValue
     */
    var $_Value;

    /**
     * @access  private
     * @var     object
     */
    var $_Container;

    /**
     * @access  private
     * @var     Label
     * @see     function  GetLabel
     * @see     function  SetLabel
     */
    var $_Label;

    /**
     * @access  private
     * @var     string
     */
    var $_Gadget;

    /**
     * for info see:
     * http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/mode
     * @access  private
     */
    var $_Mode = 'textareas';

    /**
     * @access  private
     * @var     string
     */
    var $_Theme = 'advanced';

    /**
     * Width of the editor
     * examples: 100%, 600
     *
     * @var mixed
     */
    var $_Width = '100%';

    /**
     * TinyMCE base actions
     *
     * @access  private
     */
    var $_BaseToolbar = array(
        'bold,italic,strikethrough,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,|,fontselect,fontsizeselect,|,code',
        'ltr,rtl,|,cut,copy,paste,pastetext,pasteword,|,styleprops,attribs,|,forecolor,backcolor,|,hr,|,link,unlink,image,|,undo,redo,|,cleanup',
    );

    /**
     * TinyMCE ompatibile browsers
     *
     * @access  private
     */
    var $_Browsers = array('msie', 'gecko', 'opera', 'safari');

    /**
     * for info see:
     * http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/extended_valid_elements
     *
     * @access  private
     */
    var $_ExtendedValidElements =
        'iframe[class|id|marginheight|marginwidth|align|frameborder=0|scrolling|align|name|src|height|width]';

    /**
     * for info see:
     * http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/invalid_elements
     *
     * @access  private
     */
    var $_InvalidElements = '';

    /**
     * Main Constructor
     *
     * @access  public
     * @param   string $gadget
     * @param   string $name
     * @param   string $value
     * @param   string $label
     * @return  void
     */
    function Jaws_Widgets_TinyMCE($gadget, $name, $value = '', $label = '')
    {
        require_once JAWS_PATH . 'include/Jaws/String.php';
        //$value = Jaws_String::AutoParagraph($value);
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $this->_Value, '', '14');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);
        $this->setClass($name);

        $this->_Container =& Piwi::CreateWidget('VBox');
        parent::init();
    }

    function buildXHTML()
    {
        static $alreadyLoaded;
        $alreadyLoaded = isset($alreadyLoaded)? true : false;

        $plugins = array();
        $lang = $GLOBALS['app']->GetLanguage();
        $pluginDir = JAWS_PATH . 'libraries/tinymce/plugins/';
        if (is_dir($pluginDir)) {
            $dirs = scandir($pluginDir);
            foreach($dirs as $dir) {
                if ($dir{0} != '.' && is_dir($pluginDir.$dir)) {
                    $plugins[] = $dir;
                }
            }
        }
        $plugins = implode($plugins, ',');

        $toolbars   = $this->_BaseToolbar;
        $toolbars[] = $GLOBALS['app']->Registry->Get('/config/editor_tinymce_toolbar');

        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }
        $this->_Container->PackStart($this->TextArea);
        $this->_Container->SetWidth($this->_Width);
        $this->_XHTML .= $this->_Container->Get();

        if (!$alreadyLoaded) {
            $this->_XHTML.= '<script language="javascript" type="text/javascript" src="'.
                        $GLOBALS['app']->getSiteURL('/libraries/tinymce/tiny_mce.js', true).'"></script>'."\n";
            $this->_XHTML.= '<script language="javascript" type="text/javascript" src="'.
                        $GLOBALS['app']->getSiteURL('/libraries/tinymce/jawsMCEWrapper.js', true).'"></script>'."\n";
        }

        $this->_XHTML.= "<script type=\"text/javascript\">\n";

        $ibrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
            $ibrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo';
        }

        $fbrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('FileBrowser')) {
            $fbrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile';
        }

        $this->_XHTML.= "function jaws_filebrowser_callback(field_name, url, type, win) {\n";
        $this->_XHTML.= "var browser = (type === 'image')? '$ibrowser' : '$fbrowser';\n";
        $this->_XHTML.= "if (browser != '') {\n";
        $this->_XHTML.= "tinyMCE.activeEditor.windowManager.open({\n";
        $this->_XHTML.= "   file : browser,\n";
        $this->_XHTML.= "   title : 'My File Browser',\n";
        $this->_XHTML.= "   width : 640,\n";
        $this->_XHTML.= "   height : 480,\n";
        $this->_XHTML.= "   resizable : 'yes',\n";
        $this->_XHTML.= "   scrollbars : 'yes',\n";
        $this->_XHTML.= "   inline : 'yes',\n";
        $this->_XHTML.= "   close_previous : 'no'\n";
        $this->_XHTML.= "}, {\n";
        $this->_XHTML.= "   window : win,\n";
        $this->_XHTML.= "   input : field_name\n";
        $this->_XHTML.= "});\n";
        $this->_XHTML.= "}\n";
        $this->_XHTML.= "return false;\n";
        $this->_XHTML.= "}\n";

        $this->_XHTML.= "tinyMCE.init({\n";
        $this->_XHTML.= "mode : '{$this->_Mode}',\n";
        $this->_XHTML.= "language :'{$lang}',\n";
        $this->_XHTML.= "theme : '{$this->_Theme}',\n";
        $this->_XHTML.= "plugins : '{$plugins}',\n";
        
        foreach ($toolbars as $key => $toolbar) {
            $index = $key + 1;
            $this->_XHTML.= "theme_{$this->_Theme}_buttons{$index} : '$toolbar',\n";
        }
        $index = $index + 1;
        $this->_XHTML.= "theme_{$this->_Theme}_buttons{$index} : '',\n";
        $this->_XHTML.= "template_external_list_url : '".
                        $GLOBALS['app']->getSiteURL('/libraries/tinymce/templates.js', true).
                        "',\n";
        $this->_XHTML.= "theme_{$this->_Theme}_toolbar_location : 'top',\n";
        $this->_XHTML.= "theme_{$this->_Theme}_toolbar_align : 'center',\n";
        $this->_XHTML.= "theme_{$this->_Theme}_path_location : 'bottom',\n";
        $this->_XHTML.= "theme_{$this->_Theme}_resizing : true,\n";
        $this->_XHTML.= "theme_{$this->_Theme}_resize_horizontal : false,\n";
        $this->_XHTML.= "browsers : '" . implode($this->_Browsers, ',') . "',\n";
        $this->_XHTML.= "directionality : '"._t('GLOBAL_LANG_DIRECTION')."',\n";
        $this->_XHTML.= "tab_focus : ':prev,:next',\n";
        $this->_XHTML.= "dialog_type : 'window',\n";
        $this->_XHTML.= "entity_encoding : 'raw',\n";
        $this->_XHTML.= "relative_urls : true,\n";
        $this->_XHTML.= "remove_script_host : false,\n";
        $this->_XHTML.= "force_p_newlines : true,\n";
        $this->_XHTML.= "force_br_newlines : false,\n";
        $this->_XHTML.= "convert_newlines_to_brs : false,\n";
        $this->_XHTML.= "remove_linebreaks : true,\n";
        $this->_XHTML.= "nowrap : false,\n";
        $this->_XHTML.= "apply_source_formatting : true,\n";
        //$this->_XHTML.= "save_callback : 'jaws_save_callback',\n";
        //$this->_XHTML.= "cleanup_callback : 'myCustomCleanup',\n";
        $this->_XHTML.= "file_browser_callback : 'jaws_filebrowser_callback',\n";
        if ('rtl' == _t('GLOBAL_LANG_DIRECTION')) {
            $this->_XHTML.= "content_css : \"gadgets/ControlPanel/resources/tinymce.rtl.css\",\n";
        } else {
            $this->_XHTML.= "content_css : \"gadgets/ControlPanel/resources/tinymce.css\",\n";
        }
        $this->_XHTML.= "extended_valid_elements : '" . $this->_ExtendedValidElements . "',\n";
        $this->_XHTML.= "invalid_elements : '" . $this->_InvalidElements . "',\n";
        $this->_XHTML.= "editor_selector : '{$this->_Class}'\n";
        $this->_XHTML.= "});\n";
        $this->_XHTML.= "</script>\n";
    }

    /**
     * Sets the ID for the widget int he first call and in next calls it places the id
     * in the TextArea
     *
     * @access  public
     * @param   string   $id  Widget ID
     * @return  void
     */
    function setID($id)
    {
        static $containerID;
        if (!isset($containerID)) {
            parent::setID($id);
            $containerID = $this->getID();
        } else {
            $this->TextArea->setID($id);
        }
    }

    /**
     * Set the className of the TextArea
     *
     * @access  public
     * @param   string $class
     * @return  void
     */
    function setClass($class)
    {
        $this->_Class = $class;
        $this->TextArea->setClass($class);
    }

    /**
     * Sets the label displayed with the textarea
     *
     * @access  public
     * @param   string $label The label to display.
     * @return  void
     */
    function SetLabel($label)
    {
        $this->_Label->SetValue($label);
    }

    /**
     * Set the TinyMCE theme
     *
     * @access  public
     * @param   string $theme
     * @return  void
     */
    function setTheme($theme)
    {
        $this->_Theme = $theme;
    }

    function compactFile($content)
    {
        //FROM WP
        $content = preg_replace("!(^|\s+)//.*$!m", "", $content);
        $content = preg_replace("!/\*.*?\*/!s", "", $content);
        $content = preg_replace("!^\t+!m", "", $content);
        $content = str_replace("\r", "", $content);
        $content = preg_replace("!(^|{|}|;|:|\))\n!m", '\\1', $content);

        return $content;
    }

    /**
     * @param   array $width
     * @return  void
     */
    function setWidth($width)
    {
        $this->_Width = $width;
    }

}