<?php
/**
 * Jaws CKEditor Wrapper
 *
 * @category   Widget
 * @package    Core
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2011-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once ROOT_JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';
class Jaws_Widgets_CKEditor extends Container
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

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
     * @var     int
     */
    var $_Markup = JAWS_MARKUP_HTML;

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
     * Main Constructor
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $name   Name of editor
     * @param   string  $value  Default content of editor
     * @param   int     $markup Markup language type
     * @return  void
     */
    function __construct($gadget, $name, $value = '', $markup = JAWS_MARKUP_HTML)
    {
        $this->app = Jaws::getInstance();

        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name = $name;
        $this->_Value = $value;
        $this->_Gadget = $gadget;
        $this->_Markup = $markup;

        // set toolbar options
        if (JAWS_SCRIPT == 'admin') {
            $toolbars = $this->app->registry->fetch('editor_ckeditor_backend_toolbar', 'Settings');
        } else {
            $toolbars = $this->app->registry->fetch('editor_ckeditor_frontend_toolbar', 'Settings');
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
        $this->TextArea->setRole('editor');
        $this->TextArea->setData('editor', 'ckeditor');

        $this->_Language = $this->app->getLanguage();
        $this->_Direction = Jaws::t('LANG_DIRECTION');

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
        // set editor configuration
        $this->TextArea->setData('direction', $this->_Direction);
        $this->TextArea->setData('language',  $this->_Language);
        $this->TextArea->setData('readonly',  $this->_IsEnabled? 0 : 1);
        $this->TextArea->setData('resizable', (int)$this->_IsResizable);

        $plugins = implode(
            ',',
            array_map('basename', glob(ROOT_JAWS_PATH.'libraries/ckeditor/plugins/*', GLOB_ONLYDIR))
        );
        
        // load plugins related to markup language
        switch ($this->_Markup) {
            case JAWS_MARKUP_HTML:
                $plugins = str_replace('bbcode,', '', $plugins);
                break;

            default:
                break;
        }

        $this->app->define('', 'editorPlugins', $plugins);
        $this->app->define('', 'editorToolbar', $this->toolbars);

        $this->_Container->PackStart($this->TextArea);
        $this->_XHTML .= $this->_Container->Get();

        if (JAWS_SCRIPT == 'index') {
            if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
                $this->app->define(
                    '',
                    'editorImageBrowser',
                    $this->app->map->GetRawURL('Directory', 'DirExplorer', array('type' => '3'))
                );
                $this->app->define(
                    '',
                    'editorFileBrowser',
                    $this->app->map->GetRawURL('Directory', 'DirExplorer', array('type' => '1,6'))
                );
                $this->app->define(
                    '',
                    'editorMediaBrowser',
                    $this->app->map->GetRawURL('Directory', 'DirExplorer', array('type' => '4,5'))
                );
            }
        } else {
            // Phoo
            if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
                $this->app->define(
                    '',
                    'editorImageBrowser',
                    $this->app->map->GetRawURL('Phoo', 'BrowsePhoo')
                );
            }
            // Directory
            if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
                $this->app->define(
                    '',
                    'editorFileBrowser',
                    $this->app->map->GetRawURL('Directory', 'Directory', array('standalone' => '1'))
                );
                $this->app->define(
                    '',
                    'editorMediaBrowser',
                    $this->app->map->GetRawURL('Directory', 'DirExplorer')
                );
            }
        }

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
     * Set editor enabled or disabled
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