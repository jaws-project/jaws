<?php
require_once ROOT_JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';

/**
 * Jaws TinyMCE Wrapper (uses JS and disable plugins)
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Widgets_TinyMCE extends Container
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

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
        $this->app = Jaws::getInstance();

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
        $this->TextArea->setData('direction', Jaws::t('LANG_DIRECTION'));
        $this->TextArea->setData('language', $this->app->getLanguage());


        $plugins = implode(
            ',',
            array_map('basename', glob(ROOT_JAWS_PATH.'libraries/tinymce/plugins/*', GLOB_ONLYDIR))
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
            $toolbars = $this->app->registry->fetch('editor_tinymce_backend_toolbar', 'Settings');
        } else {
            $toolbars = $this->app->registry->fetch('editor_tinymce_frontend_toolbar', 'Settings');
        }

        $this->app->define('', 'editorPlugins', $plugins);
        $this->app->define('', 'editorToolbar', $toolbars);

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