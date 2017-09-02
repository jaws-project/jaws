<?php
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';

/**
 * Jaws TinyMCE Wrapper (uses JS and disable plugins)
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
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
     * @var     int
     */
    var $_Markup = JAWS_MARKUP_HTML;

    /**
     * @access  private
     * @var     object
     */
    var $_Container;

    /**
     * @access  private
     * @var     string
     */
    var $_Gadget;

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
     * @param   string  $gadget Gadget name
     * @param   string  $name   Name of editor
     * @param   string  $value  Default content of editor
     * @param   int     $markup Markup language type
     * @return  void
     */
    function __construct($gadget, $name, $value = '', $markup = JAWS_MARKUP_HTML)
    {
        require_once JAWS_PATH . 'include/Jaws/String.php';
        //$value = Jaws_String::AutoParagraph($value);
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;
        $this->_Markup = $markup;

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $this->_Value, '', '14');
        $this->TextArea->setRole('editor');
        $this->TextArea->setData('editor', 'tinymce');
        $this->setClass($name);

        $this->_Container =& Piwi::CreateWidget('Division');
        $this->_Container->SetClass('jaws_editor');
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

        // set editor configuration
        $this->TextArea->setData('direction', _t('GLOBAL_LANG_DIRECTION'));
        $this->TextArea->setData('language', $GLOBALS['app']->GetLanguage());


        $plugins = implode(
            ',',
            array_map('basename', glob(JAWS_PATH.'libraries/tinymce/plugins/*', GLOB_ONLYDIR))
        );

        // load plugins related to markup language
        switch ($this->_Markup) {
            case JAWS_MARKUP_HTML:
                $plugins = str_replace('bbcode,', '', $plugins);
                break;

            default:
                break;
        }

        if (JAWS_SCRIPT == 'admin') {
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_tinymce_backend_toolbar', 'Settings');
        } else {
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_tinymce_frontend_toolbar', 'Settings');
        }

        $GLOBALS['app']->define('', 'editorPlugins', $plugins);
        $GLOBALS['app']->define('', 'editorToolbar', $toolbars);

        $this->_Container->PackStart($this->TextArea);
        $this->_XHTML .= $this->_Container->Get();

        if (JAWS_SCRIPT == 'index') {
            if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
                $GLOBALS['app']->define(
                    '',
                    'editorImageBrowser',
                    BASE_SCRIPT. '?gadget=Directory&action=DirExplorer&type=3'
                );
                $GLOBALS['app']->define(
                    '',
                    'editorFileBrowser',
                    BASE_SCRIPT. '?gadget=Directory&action=DirExplorer&type=1,6'
                );
                $GLOBALS['app']->define(
                    '',
                    'editorMediaBrowser',
                    BASE_SCRIPT. '?gadget=Directory&action=DirExplorer&type=4,5'
                );
            }
        } else {
            // Phoo
            if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
                $GLOBALS['app']->define('', 'editorImageBrowser', BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo');
            }
            // Directory
            if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
                $GLOBALS['app']->define('', 'editorFileBrowser', BASE_SCRIPT. '?gadget=Directory&action=Browse');
                $GLOBALS['app']->define('', 'editorMediaBrowser', BASE_SCRIPT. '?gadget=Directory&action=DirExplorer');
            }
        }

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
        $this->TextArea->setID($id);
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

}