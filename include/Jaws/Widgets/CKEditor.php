<?php
/**
 * Jaws CKEditor Wrapper
 *
 * @category   Widget
 * @package    Core
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2011-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';
class Jaws_Widgets_CKEditor extends Container
{
    /**
     * @access  private
     * @var     object
     */
    var $_ToolbarControl;

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
     */
    var $_Container;

    /**
     * @access  private
     * @var     object
     * @see     function  GetValue
     */
    var $_Value;

    /**
     * @access  private
     * @var     string
     */
    var $_Gadget;

    /**
     * @access  private
     * @var     Label
     * @see     function  GetLabel
     * @see     function  SetLabel
     */
    var $_Label;

    /**
     * Path to CKEditor relative to the document root.
     *
     * @var string
     */
    var $_BasePath;

    /**
     * Width of the CKEditor.
     * Examples: 100%, 600
     *
     * @var mixed
     */
    var $_Width = '100%';

    /**
     * Height of the CKEditor.
     * Examples: 400, 50%
     *
     * @var mixed
     */
    var $_Height = '200';

    /**
     * This is where additional configuration can be passed.
     * Example:
     * $oCKEditor->Config['EnterMode'] = 'br';
     *
     * @var array
     */
    var $_Config;

    /**
     * @access  private
     * @var     string = {default}
     */
    var $_Theme = 'default';

    /**
     * @access  private
     * @var     string = {kama,office2003,v2}
     */
    var $_Skin = 'kama';

    /**
     * CKEditor base toolbar{Basic, Full, Array of items}
     *
     * @access  private
     */
    var $_BaseToolbar = array(
        array('name' => 'document',
              'items' => array('Source', '-', 'NewPage', 'DocProps', 'Preview', 'Print',
                               '-', 'Templates')),
        array('name' => 'clipboard',
              'items' => array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord',
                               '-', 'Undo', 'Redo')),
        array('name' => 'editing',
              'items' => array('Find', 'Replace', '-', 'SelectAll')),
        array('name' => 'forms',
              'items' => array('Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select',
                               'Button', 'ImageButton', 'HiddenField')),
        array('name' => 'basicstyles',
              'items' => array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript',
                               '-', 'RemoveFormat')),
        array('name' => 'paragraph',
              'items' => array('NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-',
                               'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter',
                               'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl')),
        array('name' => 'links',
              'items' => array('Link', 'Unlink', 'Anchor')),
        array('name' => 'insert',
              'items' => array('Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar',
                               'PageBreak')),
        array('name' => 'colors',
              'items' => array('TextColor', 'BGColor')),
        array('name' => 'styles',
              'items' => array('Styles', 'Format', 'Font', 'FontSize')),
        array('name' => 'tools',
              'items' => array('Maximize', 'ShowBlocks'))
    );

    /**
     * @access  private
     * @var     string
     */
    var $_RemovePlugins;

    /**
     * @access  private
     * @var     string
     */
    var $_Language;

    /**
     * @access  private
     * @var     string
     */
    var $_Direction;

    /**
     * Tells if the bin widget is enabled or not
     *
     * @var     bool    $_IsEnabled
     * @access  private
     */
    var $_IsEnabled = true;

    /**
     * Tells if the bin widget is resizable or not
     *
     * @var     bool    $_IsResizable
     * @access  private
     */
    var $_IsResizable = true;

    /**
     * Default plugins
     *
     * @var     array $_DefaultPlugins
     * @access  private
     */
    var $_DefaultPlugins = array(
        'autogrow', 'clipboard', 'colordialog', 'dialog', 'div', 'docprops',
        'find', 'flash', 'forms', 'image', 'link', 'liststyle', 'pagebreak',
        'pastefromword', 'pastetext', 'preview', 'showblocks', 'smiley',
        'specialchar', 'styles', 'stylesheetparser', 'table', 'tableresize',
        'tabletools', 'templates', 'uicolor'
    );

    /**
     * Main Constructor
     *
     * @access  public
     *
     */
    function Jaws_Widgets_CKEditor($gadget, $name, $value = '', $label = '')
    {
        require_once JAWS_PATH . 'include/Jaws/String.php';
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name = $name;
        $this->_Value = $value;
        $this->_Gadget = $gadget;

        $this->TextArea =& Piwi::CreateWidget('TextArea', $this->_Name, $this->_Value);
        $this->TextArea->setClass($name);
        $this->TextArea->setID($this->_Name);
        $this->TextArea->setName($this->_Name);
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);

        $this->_BasePath = 'libraries/ckeditor/';
        $this->_Language = $GLOBALS['app']->GetLanguage();
        $this->_Direction = _t('GLOBAL_LANG_DIRECTION');

        $this->_Container =& Piwi::CreateWidget('VBox');
        parent::init();
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  string  XHTML
     */
    function buildXHTML()
    {
        $extraPlugins = array();
        static $alreadyLoaded;
        $alreadyLoaded = isset($alreadyLoaded) ? true : false;

        if (!$alreadyLoaded) {
            $this->_XHTML .= '<script language="javascript" type="text/javascript" src="'.
                $GLOBALS['app']->getSiteURL('/libraries/ckeditor/ckeditor.js', true). '"></script>'. "\n";
        }

        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }
        $this->_Container->PackStart($this->TextArea);
        $this->_Container->SetWidth($this->_Width);
        $this->_XHTML .= $this->_Container->Get();

        $pluginDir = JAWS_PATH . 'libraries/ckeditor/plugins/';
        if (is_dir($pluginDir)) {
            $dirs = scandir($pluginDir);
            foreach ($dirs as $dir) {
                if ($dir{0} != '.' && is_dir($pluginDir . $dir)) {
                    if (!in_array($dir, $this->_DefaultPlugins)) {
                        $extraPlugins[] = $dir;
                    }
                }
            }
        }

        $extraToolbars = $GLOBALS['app']->Registry->Get('/config/editor_ckeditor_toolbar');
        $extraToolbars = array_filter(explode('|', $extraToolbars));
        foreach ($extraToolbars as $key => $items) {
            $items = array_values(array_filter(array_map('trim', explode(',', $items))));
            if (!empty($items)) {
                $this->_BaseToolbar[] = array('name'  => "extra$key",
                                              'items' => $items);
            }
        }

        // CKEditor configuration
        $this->_Config = array();
        $this->_Config['contentsLangDirection'] = $this->_Direction;
        $this->_Config['language'] = $this->_Language;
        $this->_Config['AutoDetectLanguage'] = false;
        $this->_Config['height'] = $this->_Height;
        $this->_Config['width'] = $this->_Width;
        $this->_Config['skin'] = $this->_Skin;
        $this->_Config['theme'] = $this->_Theme;
        $this->_Config['readOnly'] = !$this->_IsEnabled;
        $this->_Config['resize_enabled'] = $this->_IsResizable;
        $this->_Config['toolbar'] = $this->_BaseToolbar;

        if(!empty($extraPlugins)) {
            $this->_Config['extraPlugins'] = implode(',', $extraPlugins);
        }

        if(!empty($this->_RemovePlugins)) {
            $this->_Config['removePlugins'] = $this->_RemovePlugins;
        }

        $this->_Config['enterMode'] = 'CKEDITOR.ENTER_P';
        $this->_Config['autoParagraph'] = 'false';

        if ('rtl' == $this->_Direction) {
            $this->_Config['contentsCss'] = 'gadgets/ControlPanel/resources/ckeditor.rtl.css';
        } else {
            $this->_Config['contentsCss'] = 'gadgets/ControlPanel/resources/ckeditor.css';
        }

        $siteURL = $GLOBALS['app']->GetSiteURL();
        if (Jaws_Gadget::IsGadgetInstalled('FileBrowser')) {
            $this->_Config['filebrowserBrowseUrl'] =
                   $siteURL. '/'. BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile';
            $this->_Config['filebrowserFlashBrowseUrl'] =
                   $siteURL. '/'. BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile';
        }

        if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
            $this->_Config['filebrowserImageBrowseUrl'] =
                   $siteURL. '/'. BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo';
        }

        $sParams = '';
        $bFirst = true;
        foreach ($this->_Config as $sKey => $sValue) {
            if (!$bFirst) {
                $sParams .= ", \n";
            } else {
                $bFirst = false;
            }
            if ($sValue === true) {
                $sParams .= $sKey . ': true';
            } elseif ($sValue === false) {
                $sParams .= $sKey . ': false';
            } elseif (is_array($sValue)) {
                $sParams .= $sKey . " : " . Jaws_UTF8::json_encode($sValue);
            } else {
                $sParams .= $sKey . " : '" . $sValue . "'";
            }
        }

        $this->_XHTML .= "<script type=\"text/javascript\">\n";
        $this->_XHTML .= "  CKEDITOR.replace( '" . $this->_Name . "',";
        $this->_XHTML .= '{' . $sParams . '}';
        $this->_XHTML .= "  );\n";
        $this->_XHTML .= "</script>\n";
    }

    /**
     * Set the ID
     *
     * @param   string  $id    ID name
     * @access  public
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
     * Get the value of the textarea
     *
     * @access  public
     * @return  string Value of the TextArea
     */
    function getValue()
    {
        return $this->_Value;
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
     * Gets the label of the textarea
     *
     * @access  public
     * @return  string The label to be displayed with the box.
     */
    function getLabel()
    {
        return $this->_Label->GetValue();
    }

    /**
     * Sets the label displayed with the textarea
     *
     * @access  public
     * @param   string $label The label to display.
     * @return void
     */
    function setLabel($label)
    {
        $this->_Label->SetValue($label);
    }

    /**
     * Set the CKEditor theme
     *
     * @param   string $Theme
     * @return  void
     */
    function setTheme($Theme)
    {
        $this->_Theme = $Theme;
    }

    /**
     * @param   string $Skin
     * @return  void
     */
    function setSkin($Skin)
    {
        $this->_Skin = $Skin;
    }

    /**
     * @param   string $Language
     * @return  void
     */
    function setLanguage($Language)
    {
        $this->_Language = $Language;
    }

    /**
     * @param   bool    $IsEnabled
     * @return  void
     */
    function setIsEnabled($IsEnabled)
    {
        $this->_IsEnabled = $IsEnabled;
    }

    /**
     * @param   bool    $IsResizable
     * @return  void
     */
    function setIsResizable($IsResizable)
    {
        $this->_IsResizable = $IsResizable;
    }

    /**
     * @param   string $Plugins
     * @return  void
     */
    function removePlugins($Plugins)
    {
        $this->_RemovePlugins .= "," . $Plugins;
    }

    /**
     * @param   string $Direction
     * @return  void
     */
    function setDirection($Direction)
    {
        $this->_Direction = $Direction;
    }

    /**
     * @param  $Toolbar
     * @return  void
     */
    function setToolbar($Toolbar)
    {
        $this->_BaseToolbar = $Toolbar;
    }

    /**
     * @param   array $height
     * @return  void
     */
    function setHeight($height)
    {
        $this->_Height = $height;
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