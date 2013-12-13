<?php
/**
 * Jaws CKEditor Wrapper
 *
 * @category   Widget
 * @package    Core
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2011-2013 Jaws Development Group
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
    var $_Skin = 'newmoono';

    /**
     * CKEditor base toolbar{Basic, Full, Array of items}
     *
     * @access  private
     */
    var $toolbars = array();

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
     * @var     array   $_DefaultPlugins
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
     * @param   string  $gadget Gadget name
     * @param   string  $name   Name of editor
     * @param   string  $value  Default content of editor
     * @param   string  $label  Label/Title of editor
     * @return  void
     */
    function Jaws_Widgets_CKEditor($gadget, $name, $value = '', $label = '')
    {
        require_once JAWS_PATH . 'include/Jaws/String.php';
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name = $name;
        $this->_Value = $value;
        $this->_Gadget = $gadget;

        $baseToolbars = $GLOBALS['app']->Registry->fetch('editor_ckeditor_base_toolbar', 'Settings');
        $baseToolbars = array_filter(explode('|', $baseToolbars));
        foreach ($baseToolbars as $key => $items) {
            $items = array_values(array_filter(array_map('trim', explode(',', $items))));
            if (!empty($items)) {
                $this->toolbars[] = array('name' => "base$key", 'items' => $items);
            }
        }

        $extraToolbars = $GLOBALS['app']->Registry->fetch('editor_ckeditor_extra_toolbar', 'Settings');
        $extraToolbars = array_filter(explode('|', $extraToolbars));
        foreach ($extraToolbars as $key => $items) {
            $items = array_values(array_filter(array_map('trim', explode(',', $items))));
            if (!empty($items)) {
                $this->toolbars[] = array('name' => "extra$key", 'items' => $items);
            }
        }

        $this->TextArea =& Piwi::CreateWidget('TextArea', $this->_Name, $this->_Value);
        $this->TextArea->setClass($name);
        $this->TextArea->setID($this->_Name);
        $this->TextArea->setName($this->_Name);
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);

        $this->_BasePath = 'libraries/ckeditor/';
        $this->_Language = $GLOBALS['app']->GetLanguage();
        $this->_Direction = _t('GLOBAL_LANG_DIRECTION');

        $this->_Container =& Piwi::CreateWidget('Division');
        parent::init();
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  string  XHTML content
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
        $this->_Config['toolbar'] = $this->toolbars;

        if(!empty($extraPlugins)) {
            $this->_Config['extraPlugins'] = implode(',', $extraPlugins);
        }

        if(!empty($this->_RemovePlugins)) {
            $this->_Config['removePlugins'] = $this->_RemovePlugins;
        }

        $this->_Config['enterMode'] = 'CKEDITOR.ENTER_P';
        $this->_Config['autoParagraph'] = 'false';

        if ('rtl' == $this->_Direction) {
            $this->_Config['contentsCss'] = 'gadgets/ControlPanel/Resources/ckeditor.rtl.css';
        } else {
            $this->_Config['contentsCss'] = 'gadgets/ControlPanel/Resources/ckeditor.css';
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
     * @access  public
     * @param   string  $id ID name
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
     * Get the value of the textarea
     *
     * @access  public
     * @return  string  Value of the TextArea
     */
    function getValue()
    {
        return $this->_Value;
    }

    /**
     * Set the className of the TextArea
     *
     * @access  public
     * @param   string  $class
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
     * @return  string  The label to be displayed with the box.
     */
    function getLabel()
    {
        return $this->_Label->GetValue();
    }

    /**
     * Sets the label displayed with the textarea
     *
     * @access  public
     * @param   string  $label  The label to display.
     * @return  void
     */
    function setLabel($label)
    {
        $this->_Label->SetValue($label);
    }

    /**
     * Set the CKEditor theme
     *
     * @access  public
     * @param   string  $Theme
     * @return  void
     */
    function setTheme($Theme)
    {
        $this->_Theme = $Theme;
    }

    /**
     * Set skin of editor
     *
     * @access  public
     * @param   string  $Skin
     * @return  void
     */
    function setSkin($Skin)
    {
        $this->_Skin = $Skin;
    }

    /**
     * Set default language of editor
     *
     * @access  public
     * @param   string  $Language
     * @return  void
     */
    function setLanguage($Language)
    {
        $this->_Language = $Language;
    }

    /**
     * Set editor edabled or disabled
     *
     * @access  public
     * @param   bool    $IsEnabled
     * @return  void
     */
    function setIsEnabled($IsEnabled)
    {
        $this->_IsEnabled = $IsEnabled;
    }

    /**
     * Set editor to be resizable of not
     *
     * @access  public
     * @param   bool    $IsResizable
     * @return  void
     */
    function setIsResizable($IsResizable)
    {
        $this->_IsResizable = $IsResizable;
    }

    /**
     * Remove plugin
     *
     * @access  public
     * @param   string  $Plugins
     * @return  void
     */
    function removePlugins($Plugins)
    {
        $this->_RemovePlugins .= "," . $Plugins;
    }

    /**
     * Set direction of editor
     *
     * @access  public
     * @param   string  $Direction
     * @return  void
     */
    function setDirection($Direction)
    {
        $this->_Direction = $Direction;
    }

    /**
     * Set default editor toolbar
     *
     * @access  public
     * @param   string  $toolbars   Toolbars
     * @return  void
     */
    function setToolbar($toolbars)
    {
        $this->toolbars = array();
        $toolbars = array_filter(explode('|', $toolbars));
        foreach ($toolbars as $key => $items) {
            $items = array_values(array_filter(array_map('trim', explode(',', $items))));
            if (!empty($items)) {
                $this->toolbars[] = array('name' => "extra$key", 'items' => $items);
            }
        }
    }

    /**
     * Set height of editor
     *
     * @access  public
     * @param   array  $height
     * @return  void
     */
    function setHeight($height)
    {
        $this->_Height = $height;
    }

    /**
     * Set width of editor
     *
     * @access  public
     * @param   array  $width
     * @return  void
     */
    function setWidth($width)
    {
        $this->_Width = $width;
    }

}