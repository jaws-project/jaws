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
    function __construct($gadget, $name, $value = '', $label = '')
    {
        require_once JAWS_PATH . 'include/Jaws/String.php';
        //$value = Jaws_String::AutoParagraph($value);
        $value = str_replace('&lt;', '&amp;lt;', $value);
        $value = str_replace('&gt;', '&amp;gt;', $value);

        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $this->_Value, '', '14');
        $this->TextArea->setRole('editor');
        $this->TextArea->setData('editor', 'tinymce');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);
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
        if (JAWS_SCRIPT == 'admin') {
            $plugins = str_replace('bbcode,', '', $plugins);
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_tinymce_backend_toolbar', 'Settings');
        } else {
            $toolbars = $GLOBALS['app']->Registry->fetch('editor_tinymce_frontend_toolbar', 'Settings');
        }

        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }

        $GLOBALS['app']->Layout->setVariable('editorPlugins', $plugins);
        $GLOBALS['app']->Layout->setVariable('editorToolbar', $toolbars);

        $this->_Container->PackStart($this->TextArea);
        $this->_XHTML .= $this->_Container->Get();
/*
        $ibrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('Phoo')) {
            $ibrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=Phoo&action=BrowsePhoo';
        }

        $fbrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('FileBrowser')) {
            $fbrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile';
        }

        $mbrowser = '';
        if (Jaws_Gadget::IsGadgetInstalled('Directory')) {
            $mbrowser = $GLOBALS['app']->getSiteURL(). '/'. BASE_SCRIPT. '?gadget=Directory&action=Browse';
        }
*/
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

}