<?php
/**
 * Jaws CKEditor Wrapper
 *
 * @category   Widget
 * @package    Core
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2011-2015 Jaws Development Group
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
     * This is where additional configuration can be passed.
     * Example:
     * $oCKEditor->Config['EnterMode'] = 'br';
     *
     * @var array
     */
    var $_Config;

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
    function __construct($gadget, $name, $value = '', $label = '')
    {
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name = $name;
        $this->_Value = $value;
        $this->_Gadget = $gadget;

        // set toolbar options
        if (JAWS_SCRIPT == 'admin') {
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_ckeditor_backend_toolbar', 'Settings');
        } else {
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_ckeditor_frontend_toolbar', 'Settings');
        }
        $toolbars = array_filter(explode('|', $toolbars));
        foreach ($toolbars as $key => $items) {
            $items = array_values(array_filter(explode(',', $items)));
            if (!empty($items)) {
                $this->toolbars[] = $items;
            }
        }

        $this->TextArea =& Piwi::CreateWidget('TextArea', $this->_Name, $this->_Value);
        $this->setClass($name);
        $this->TextArea->setID($this->_Name);
        $this->TextArea->setName($this->_Name);
        $this->TextArea->setData('editor', 'ckeditor');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);

        $this->_BasePath = 'libraries/ckeditor/';
        $this->_Language = $GLOBALS['app']->GetLanguage();
        $this->_Direction = _t('GLOBAL_LANG_DIRECTION');

        $this->_Container =& Piwi::CreateWidget('Division');
        $this->_Container->setClass('jaws_editor');
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
        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }
        // set editor configuration
        $this->TextArea->setData('direction', $this->_Direction);
        $this->TextArea->setData('language', $GLOBALS['app']->GetLanguage());
        $this->TextArea->setData('readonly', $this->_IsEnabled? 0 : 1);
        $this->TextArea->setData('resizable', (int)$this->_IsResizable);

        $extraPlugins = array();
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

        $GLOBALS['app']->Layout->setVariable('editorExtraPlugins', implode(',', $extraPlugins));
        $GLOBALS['app']->Layout->setVariable('editorToolbar', $this->toolbars);

        $this->_Container->PackStart($this->TextArea);
        $this->_XHTML .= $this->_Container->Get();
/*
        // FileBrowser
        if (Jaws_Gadget::IsGadgetInstalled('FileBrowser')) {
            $tpl->SetBlock("$block/filebrowser");
            $tpl->SetVariable('filebrowserBrowseUrl', BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile');
            $tpl->SetVariable('filebrowserFlashBrowseUrl', BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile');
            $tpl->ParseBlock("$block/filebrowser");
        }
        // Phoo
        if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
            $tpl->SetBlock("$block/phoo");
            $tpl->SetVariable('filebrowserImageBrowseUrl', BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo');
            $tpl->ParseBlock("$block/phoo");
        }
        // Directory
        if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
            $tpl->SetBlock("$block/directory");
            $tpl->SetVariable('filebrowserFlashBrowseUrl', BASE_SCRIPT. '?gadget=Directory&action=Browse');
            $tpl->ParseBlock("$block/directory");
        }
*/
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
        $this->TextArea->setID($id);
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

}