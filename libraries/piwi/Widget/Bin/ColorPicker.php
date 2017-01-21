<?php
/**
 * ColorPicker.php - Widget that shows a float div to select colors
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2005
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/Button.php';
require_once PIWI_PATH . '/Widget/Bin/Entry.php';

class ColorPicker extends Bin
{
    /**
     * Popup name
     *
     * @access   private
     * @var      string
     * @see      SetPopupName
     */
    var $_popupName = 'Color';

    /**
     * Color font style
     *
     * @access   private
     * @var      string
     * @see      SetColorFontStyle
     */
    var $_colorFontStyle;

    /**
     * Popup status, should a popup or a flat div should be used?
     *
     * @access   private
     * @var      boolean
     * @see      UsePopup
     */
    var $_usePopup = false;

    /**
     * Button text
     *
     * @access   private
     * @var      string
     * @see      SetButtonText
     */
    var $_buttonText;

    /**
     * Button icon
     *
     * @access   private
     * @var      string
     * @see      SetButtonIcon
     */
    var $_buttonIcon;

    /**
     * Hide the input field
     *
     * @access   private
     * @var      boolean
     * @see      HideInput
     */
    var $_hideInput = false;

    /**
     * ColorPicker entry
     *
     * @access   private
     * @var      Entry
     */
    var $_entry;

    /**
     * Color button
     *
     * @access   private
     * @var      Button
     */
    var $_button;

    /**
     * Event: onSelect color
     *
     * @access   private
     * @var      string
     */
    var $_onSelectEvent = '';

    /**
     * Public constrcutor
     *
     * @param   string   $name   Color picker field name
     * @param   string   $value  Value of field
     * @param   string   $text   Text in the button
     * @param   string   $stock  Stock image (the button image)
     * @access  public
     */
    function __construct($name, $value = '', $text = '', $stock = '')
    {
        $this->_name  = $name;
        $this->_value = $value;
        $this->_button = new Button($name . '_button', $text, $stock);
        $this->_entry  = new Entry($name, $value);
        $this->setButtonText($text);
        $this->setButtonIcon($stock);
        $this->setColorFontStyle('10px Helvetica');
        $this->_availableEvents = array('onselect');
        parent::init();
    }

    /**
     * Set the button text
     *
     * @param  string    $text  Button text
     * @access public
     */
    function setButtonText($text)
    {
        $this->_buttonText = $text;
        $this->_button->setValue($text);
    }

    /**
     * Set the button icon
     *
     * @param  string    $icon Button icon
     * @access public
     */
    function setButtonIcon($icon)
    {
        $this->_buttonIcon = $icon;
        $this->_button->setStock($icon);
    }

    /**
     * Set the font style that appears in the color picker
     * that shows the HTML color.
     *
     * @param  string    $style  Style (CSS)
     * @access public
     */
    function setColorFontStyle($style)
    {
        $this->_colorFontStyle = $style;
    }

    /**
     * Set the popup window name
     *
     * @param  string    $name  Popup name
     * @access public
     */
    function setPopupName($name)
    {
        $this->_popupName = $name;
    }

    /**
     * Use a popup window? by default the color picker appears like a flat div
     *
     * @param  boolean   $status  True/False
     * @access public
     */
    function usePopup($status = true)
    {
        if (is_bool($status)) {
            $this->_usePopup = $status;
        } else {
            $this->_usePopup = true;
        }
    }

    /**
     * Hide the input field?, maybe we just want to have a button to select the colors
     *
     * @param  boolean   $status  True/False
     * @access public
     */
    function hideInput($status = true)
    {
        if (is_bool($status)) {
            $this->_hideInput = $status;
        } else {
            $this->_hideInput = true;
        }
    }

    /**
     * Adds an event
     *
     * The difference between this AddEvent and the
     * one in Bin:: is that it support colorPciker events ;-)
     */
    function addEvent($event)
    {
        if (is_string($event) && func_num_args() == 2) {
            $action = func_get_arg(1);
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                if (in_array($event, $this->_availableEvents)) {
                    switch ($event) {
                    case ON_SELECT:
                        $this->_onSelectEvent = $action;
                        if (substr($this->_onSelectEvent, -1, 1) != ';') {
                            $this->_onSelectEvent = $this->_onSelectEvent . ';';
                        }
                        break;
                    }
                } else {
                    die("[PIWI] - Sorry but you are not permitted to use ".$event." in this widget");
                }
            } else {
                $this->_events[] = new JSEvent($event, $action);
            }
        } elseif (is_object($event) && strtolower(get_class($event)) == 'jsevent') {
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                if (in_array($event->getID(), $this->_availableEvents)) {
                    $id = $event->getID();
                    switch($id) {
                    case ON_SELECT:
                        $this->_onSelectEvent = $event->getCode();
                        if (substr($this->_onSelectEvent, -1, 1) != ';') {
                            $this->_onSelectEvent = $this->_onSelectEvent . ';';
                        }
                        break;
                    }
                } else {
                    die("[PIWI] - Sorry but you are not permitted to use ".$event->GetID()." in this widget");
                }
            }
        } else {
            die("[PIWI] - Events should be objects");
        }
    }

    /**
     * Construct the widget
     *
     * @access  private
     */
    function buildXHTML()
    {
        $colorpicker = PIWI_URL . 'piwidata/js/colorpicker/ColorPicker2.js';
        $popup       = PIWI_URL . 'piwidata/js/colorpicker/PopupWindow.js';
        $anchor      = PIWI_URL . 'piwidata/js/colorpicker/AnchorPosition.js';

        $this->addFile($colorpicker);
        $this->addFile($popup);
        $this->addFile($anchor);

        $pickerName = $this->_id . '_colorpicker';

        $this->_XHTML = "<script type=\"text/javascript\">\n";
        $this->_XHTML.= "var ".$pickerName."_properties = [];\n";
        $this->_XHTML.= $pickerName."_properties['windowname'] = '".$this->_popupName."';\n";
        $this->_XHTML.= $pickerName."_properties['fontStyle'] = '".$this->_colorFontStyle."';\n";
        $this->_XHTML.= $pickerName."_properties['fieldID'] = '".$this->_entry->getID()."';\n";

        if (!empty($this->_onSelectEvent)) {
            $this->_onSelectEvent = " ".$this->_onSelectEvent." ";
        }
        $this->_XHTML.= $pickerName."_properties['onselect'] = '".$this->_onSelectEvent."';\n";

        if ($this->_usePopup) {
            $this->_XHTML.= "var ".$pickerName." = new ColorPicker('window',".$pickerName."_properties);\n";
        } else {
            $this->_XHTML.= "var ".$pickerName." = new ColorPicker('',".$pickerName."_properties);\n";
        }
        $this->_XHTML.= "</script>\n";

        $this->_button->addEvent(ON_CLICK, $pickerName.".select(document.getElementById('".$this->_entry->getID()."'), ".
                                 "'".$pickerName."_ahref'); return false;");

        if (!empty($this->_onSelectEvent)) {
            $this->_entry->addEvent(ON_CHANGE, $this->_onSelectEvent);
        }

        if ($this->_hideInput) {
            $this->_entry->setType("hidden");
        }

        $this->_XHTML.= "<table border=\"0\" style=\"border-spacing: 0px; padding: 0px; border: 0px;\">\n";
        $this->_XHTML.= " <tr>\n";
        $this->_XHTML.= "  <td>\n";
        $this->_XHTML.= "<script type=\"text/javascript\">\n";
        $this->_XHTML.= "function ExecutePingBackOf".$this->_entry->getID()."() {\n";
        $this->_XHTML.= "  ".$this->_onSelectEvent."\n";
        $this->_XHTML.= "}\n";
        $this->_XHTML.= "</script>\n";
        $this->_XHTML.= $this->_entry->get();
        $this->_XHTML.= "  </td>\n";
        $this->_XHTML.= "  <td>\n";
        $this->_XHTML.= "<a id=\"".$pickerName."_ahref\"></a>\n";
        $this->_XHTML.= $this->_button->get();
        $this->_XHTML.= "<script type=\"text/javascript\">\n";
        $this->_XHTML.= $pickerName.".writeDiv();\n";
        $this->_XHTML.= "</script>\n";
        $this->_XHTML.= "  </td>\n";
        $this->_XHTML.= " </tr>\n";
        $this->_XHTML.= "</table>";
    }
}
?>