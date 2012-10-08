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
    var $_Width = '100%';

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
     */
    function Jaws_Widgets_TextArea($gadget, $name, $value = '', $label = '')
    {
        $this->_Name   = $name;
        $this->_Value  = $value;
        $this->_Gadget = $gadget;
        $this->_ToolbarControl =& Piwi::CreateWidget('Toolbar');
        $this->_ToolbarControl->SetID('toolbar_'.$name);

        $this->TextArea =& Piwi::CreateWidget('TextArea', $name, $value, '', '14', '106');
        $this->TextArea->SetStyle('width: 99%;');
        $this->_Label =& Piwi::CreateWidget('Label', $label, $this->TextArea);

        $this->_Container =& Piwi::CreateWidget('VBox');
        $this->_Container->AddFile('include/Jaws/Widgets/TextArea.js');

        parent::init();
        $this->setID($name);
    }

    /**
     * Set the ID
     *
     * @param   string  $id    ID name
     * @access  public
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
     * @return  string  XHTML
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
     * @param   object  $control Control to Add
     * @access  public
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
     * @return  string Value of the TextArea
     */
    function GetValue()
    {
        return $this->_Value;
    }

    /**
     * Gets the label of the textarea
     *
     * @access  public
     * @return  string The label to be displayed with the box.
     */
    function GetLabel()
    {
        return $this->_Label->GetValue();
    }

    /**
     * Sets the label displayed with the textarea
     *
     * @access  public
     * @param   string $label The label to display.
     * @return null
     */
    function SetLabel($label)
    {
        $this->_Label->SetValue($label);
    }

    /**
     * @param   arrayed $width
     */
    function setWidth($width)
    {
        $this->_Width = $width;
    }

    /**
     * Build the complete JawsEditor looking for the WebControls
     *
     * @access  private
     */
    function extraBuild()
    {
        $availablePlugins = explode(',', $GLOBALS['app']->Registry->Get('/plugins/parse_text/enabled_items'));
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