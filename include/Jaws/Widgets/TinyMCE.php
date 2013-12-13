<?php
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';

/**
 * Jaws TinyMCE Wrapper (uses JS and disable plugins)
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
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
     * @see     GetValue()
     */
    var $_Value;

    /**
     * @access  private
     * @var     object
     */
    var $_Container;

    /**
     * @access  private
     * @var     object
     * @see     GetLabel(), SetLabel()
     */
    var $_Label;

    /**
     * @access  private
     * @var     string
     */
    var $_Gadget;

    /**
     * @access  private
     * @var     string
     * @see     http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/mode
     */
    var $_Mode = 'textareas';

    /**
     * @access  private
     * @var     string
     */
    var $_Theme = 'modern';

    /**
     * Width of the editor, examples: 100%, 600
     *
     * @var     string
     * @access  private
     */
    var $_Width = '100%';

    /**
     * TinyMCE base actions
     *
     * @var     array
     * @access  private
     */
    var $toolbars = array();

    /**
     * TinyMCE ompatibile browsers
     *
     * @var     array
     * @access  private
     */
    var $_Browsers = array('msie', 'gecko', 'opera', 'safari');

    /**
     * @access  private
     * @var     string
     * @see     http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/extended_valid_elements
     */
    var $_ExtendedValidElements =
        'iframe[class|id|marginheight|marginwidth|align|frameborder=0|scrolling|align|name|src|height|width]';

    /**
     * @access  private
     * @var     string
     * @see     http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/invalid_elements
     */
    var $_InvalidElements = '';

    /**
     * Main Constructor
     *
     * @access  public
     * @param   string  $gadget
     * @param   string  $name
     * @param   string  $value
     * @param   string  $label
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

        $this->toolbars[] = $GLOBALS['app']->Registry->fetch('editor_tinymce_base_toolbar', 'Settings');
        $this->toolbars[] = $GLOBALS['app']->Registry->fetch('editor_tinymce_extra_toolbar', 'Settings');

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $this->_Value, '', '14');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);
        $this->setClass($name);

        $this->_Container =& Piwi::CreateWidget('Division');
        parent::init();
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  void
     */
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

        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }
        $this->_Container->PackStart($this->TextArea);
        $this->_Container->SetWidth($this->_Width);
        $this->_XHTML .= $this->_Container->Get();

        $ibrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
            $ibrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo';
        }

        $fbrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('FileBrowser')) {
            $fbrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile';
        }

        $GLOBALS['app']->Layout->AddScriptLink('libraries/tinymce/tinymce.js');
        $tpl = new Jaws_Template();
        $tpl->Load('TinyMCE.html', 'include/Jaws/Resources');
        $tpl->SetBlock('tinymce');

        $tpl->SetVariable('ibrowser', $ibrowser);
        $tpl->SetVariable('fbrowser', $fbrowser);
        $tpl->SetVariable('mode',     $this->_Mode);
        $tpl->SetVariable('lang',     $lang);
        $tpl->SetVariable('theme',    $this->_Theme);
        $tpl->SetVariable('plugins',  $plugins);

        // set toolbars
        $index = 0;
        foreach ($this->toolbars as $key => $toolbar) {
            $tpl->SetBlock('tinymce/toolbar');
            $index = $key + 1;
            $tpl->SetVariable('theme',   $this->_Theme);
            $tpl->SetVariable('key',     $index);
            $tpl->SetVariable('toolbar', $toolbar);
            $tpl->ParseBlock('tinymce/toolbar');
        }
        $index = $index + 1;
        $tpl->SetVariable('key', $index);

        $tpl->SetVariable('browsers', implode($this->_Browsers, ','));
        $tpl->SetVariable('dir',      _t('GLOBAL_LANG_DIRECTION'));
        $tpl->SetVariable('valid_elements',   $this->_ExtendedValidElements);
        $tpl->SetVariable('invalid_elements', $this->_InvalidElements);
        $tpl->SetVariable('class', $this->_Class);

        $tpl->ParseBlock('tinymce');
        $this->_XHTML.= $tpl->Get();
    }

    /**
     * Sets the ID for the widget int he first call and in next calls it places the id
     * in the TextArea
     *
     * @access  public
     * @param   string  $id     Widget ID
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
     * @param   string  $class
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

    /**
     * Set default editor toolbar
     *
     * @access  public
     * @param   string  $toolbars   Toolbars
     * @return  void
     */
    function setToolbar($toolbars)
    {
        $this->toolbars = array($toolbars);
    }

    /**
     * Set width of TinyMCE editor
     *
     * @access  public
     * @param   string  $width
     * @return  void
     */
    function setWidth($width)
    {
        $this->_Width = $width;
    }

}