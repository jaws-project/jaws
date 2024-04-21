<?php
/**
 * Jaws Simple editor
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once ROOT_JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';
/**
 * Widget that interacts with piwi to create the Jaws Editor
 */
class Jaws_Widgets_TextArea extends Container
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
     * @see     function  AddControl
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
     * @var     int
     */
    var $_Markup = JAWS_MARKUP_HTML;

    /**
     * @access  private
     * @var     object
     */
    var $_Container;

    /**
     * Width of the editor
     * examples: 100%, 600
     *
     * @var mixed
     */
    var $_Width = '';

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

        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;
        $this->_Markup = $markup;

        $this->_ToolbarControl =& Piwi::CreateWidget('Toolbar');
        $this->_ToolbarControl->SetID('toolbar_'.$name);

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $value);
        $this->TextArea->SetClass('xx-large');

        $this->_Container =& Piwi::CreateWidget('Division');
        $this->_Container->SetClass('jaws_editor');
        $this->_Container->AddFile('include/Jaws/Resources/TextArea.js');

        parent::init();
        $this->setID($name);
    }

    /**
     * Set the ID
     *
     * @param   string  $id     ID name
     * @access  public
     * @return  void
     */
    function SetID($id)
    {
        $this->TextArea->setID($id);
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  void
     */
    function buildXHTML()
    {
        $this->_Container->PackStart($this->_ToolbarControl);
        $this->_Container->PackStart($this->TextArea);
        $this->_Container->SetWidth($this->_Width);

        $this->extraBuild();
        $this->_XHTML = $this->_Container->Get();
    }

    /**
     * Add a new plugin Webcontrol to the toolbar
     *
     * @param   object  $control    Control to Add
     * @access  public
     * @return  void
     */
    function AddControl($control)
    {
        if (is_object($control)) {
            $this->_ToolbarControl->Add($control);
        }
    }

    /**
     * Get the value of the textarea
     *
     * @access  public
     * @return  string  Value of the TextArea
     */
    function GetValue()
    {
        return $this->_Value;
    }

    /**
     * Set width of editor
     *
     * @access  public
     * @param   arrayed $width
     * @return  void
     */
    function setWidth($width)
    {
        $this->_Width = $width;
    }

    /**
     * Build the complete JawsEditor looking for the WebControls
     *
     * @access  private
     * @return  void
     */
    function extraBuild()
    {
        $installed_plugins = $this->app->registry->fetch('plugins_installed_items');
        $installed_plugins = array_filter(explode(',', $installed_plugins));
        $pluginKey = 'frontend_gadgets';
        if (JAWS_SCRIPT == 'admin') {
            $pluginKey = 'backend_gadgets';
        }

        foreach ($installed_plugins as $plugin) {
            $gadgets = $this->app->registry->fetch($pluginKey, $plugin);
            if (($gadgets == '*') || in_array($this->_Gadget, explode(',', $gadgets))) {
                $objPlugin = Jaws_Plugin::getInstance($plugin);
                if (!Jaws_Error::IsError($objPlugin) && method_exists($objPlugin, 'GetWebControl')) {
                    $plugincontrol = $objPlugin->GetWebControl($this->_Name);
                    if (is_object($plugincontrol)) {
                        $plugincontrolValue = $plugincontrol->Get();
                        if (!empty($plugincontrolValue)) {
                            $this->AddControl($plugincontrol);
                        }
                    }
                }
            }
        }
    }

}