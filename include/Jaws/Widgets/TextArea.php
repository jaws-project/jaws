<?php
/**
 * Jaws Simple editor
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/Container.php';
/**
 * Widget that interacts with piwi to create the Jaws Editor
 */
class Jaws_Widgets_TextArea extends Container
{
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
     * @access  private
     * @var     Label
     * @see     function  GetLabel
     * @see     function  SetLabel
     */
    var $_Label;

    /**
     * Main Constructor
     *
     * @access  public
     * @param   $gadget
     * @param   $name
     * @param   string  $value
     * @param   string  $label
     * @return  void
     */
    function Jaws_Widgets_TextArea($gadget, $name, $value = '', $label = '')
    {
        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;
        $this->_ToolbarControl =& Piwi::CreateWidget('Toolbar');
        $this->_ToolbarControl->SetID('toolbar_'.$name);

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $value, '', '14', '106');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);

        $this->_Container =& Piwi::CreateWidget('Division');
        $this->_Container->SetClass('jaws_textarea_editor');
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
        static $containerID;
        if (!isset($containerID)) {
            parent::setID($id);
            $containerID = $this->getID();
        } else {
            $this->TextArea->setID($id);
        }
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  void
     */
    function buildXHTML()
    {
        $label = $this->_Label->GetValue();
        if (!empty($label)) {
            $this->_Container->PackStart($this->_Label);
        }
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
     * Gets the label of the textarea
     *
     * @access  public
     * @return  string  The label to be displayed with the box.
     */
    function GetLabel()
    {
        return $this->_Label->GetValue();
    }

    /**
     * Sets the label displayed with the textarea
     *
     * @access  public
     * @param   string  $label The label to display.
     * @return  void
     */
    function SetLabel($label)
    {
        $this->_Label->SetValue($label);
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
        $pluginKey = '/plugins/parse_text/admin_enabled_items';
        if (JAWS_SCRIPT != 'admin') {
            $pluginKey = '/plugins/parse_text/enabled_items';
        }
        $availablePlugins = explode(',', $GLOBALS['app']->Registry->Get($pluginKey));
        foreach ($availablePlugins as $plugin) {
            if (empty($plugin)) {
                continue;
            }

            $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
            if (!Jaws_Error::IsError($objPlugin)) {
                $use_in = '/plugins/parse_text/' . $plugin . '/use_in';
                if ($GLOBALS['app']->Registry->Get($use_in) == '*' ||
                    in_array($this->_Gadget, explode(',', $GLOBALS['app']->Registry->Get($use_in))))
                {
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