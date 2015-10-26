<?php
require_once JAWS_PATH . 'libraries/piwi/Widget/Bin/Bin.php';

/**
 * Buttons with text and some stuff in them. Will be represented as a 'div'
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Widgets_ImageButton extends Bin
{
    /**
     * Image SRC. Will be used for the background
     *
     * @access  private
     * @var     string
     * @see     SetImageSRC
     */
    var $_ImageSRC;

    /**
     * Text to display
     *
     * @access  private
     * @var     string
     * @see     SetText
     */
    var $_Text;

    /**
     * Button's style to use
     *
     * @access  private
     * @var     string
     * @see     SetButtonStyle(), GetButtonStyle()
     */
    var $_ButtonStyle;

    /**
     * Button's class to use
     *
     * @access  private
     * @var     string
     * @see     SetButtonClass(), GetButtonClass()
     */
    var $_ButtonClass;

    /**
     * Text's style to use
     *
     * @access  private
     * @var     string
     * @see     SetTextStyle(), GetTextStyle()
     */
    var $_TextStyle;

    /**
     * Text's class to use
     *
     * @access  private
     * @var     string
     * @see     SetTextClass(), GetTextClass()
     */
    var $_TextClass;

    /**
     * Default action to use
     *
     * @access  private
     * @var     string
     * @see     SetAction()
     */
    var $_Action;

    /**
     * ExtrActions that imagebutton will show bellow(as comments)
     *
     * @access  private
     * @var     array
     * @see     AddAction
     */
    var $_ExtraActions;

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $text   Text of ImageButton
     * @param   string  $img    Image to display
     * @param   string  $action
     * @return  void
     */
    function Jaws_Widgets_ImageButton($text, $img, $action = '')
    {
        $this->_ImageSRC = $img;
        $this->_Text = $text;
        $this->_Action = $action;
        $this->_ExtraActions = array();
        $this->_AvailableEvents = array('onfocus', 'onblur', 'onclick', 'ondblclick',
                                        'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove',
                                        'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup');
        parent::Init();
    }

    /**
     * Set the default action
     *
     * @access  public
     * @param   string   $action  Action to use
     * @return  void
     */
    function SetAction($action)
    {
        $this->_Action = $action;
    }

    /**
     * Set the button style
     *
     * @access  public
     * @param   string  $style  Button Style to use
     * @return  void
     */
    function ButtonStyle($style)
    {
        $this->_ButtonStyle = $style;
    }

    /**
     * Set the button class
     *
     * @access  public
     * @param   string  $class  Button Class to use
     * @return  void
     */
    function SetButtonClass($class)
    {
        $this->_ButtonClass = $class;
    }

    /**
     * Set the text style
     *
     * @access  public
     * @param   string   $style  Text Style to use
     * @return  void
     */
    function TextStyle($style)
    {
        $this->_TextStyle = $style;
    }

    /**
     * Set the text class
     *
     * @access  public
     * @param   string   $class  Text Class to use
     * @return  void
     */
    function SetTextClass($class)
    {
        $this->_TextClass = $class;
    }

    /**
     * Set the text
     *
     * @access  public
     * @param   string   $text   Text of ImageButton
     * @return  void
     */
    function SetText($text)
    {
        $this->_Text = $text;
    }

    /**
     * Set the image src
     *
     * @access  public
     * @param   string  $img    Image to use as background
     * @return  void
     */
    function SetImageSRC($img)
    {
        $this->_ImageSRC = $img;
    }

    /**
     * Adds a new extra action(that will be printed bellow the image)
     *
     * @access  public
     * @param   string  $text   Text of the link
     * @param   string  $action URL of action or javascript
     * @return  void
     */
    function AddExtraAction($text, $action)
    {
        $this->_ExtraActions[] = array('text' => $text,
                                       'action' => $action);
    }

    /**
     * Build the widget and returns its xhtml
     *
     * @access  public
     * @return  string  XHTML of the Widget
     */
    function BuildXHTML()
    {
        $id = 'imagebutton_' . $this->GetID();
        $class = $this->getClass();
        $style = $this->getStyle();

        $this->_XHTML = '<div id="' . $id . '" align="center"';

        if (!empty($style)) {
            $this->_XHTML.= ' style="' . $style . '"';
        }

        if (!empty($class)) {
            $this->_XHTML.= ' class="' . $class . '"';
        }
        $this->_XHTML.= ">\n";

        $this->_XHTML.= '<div id="' . $id . '_button"';
        if (!empty($this->_ButtonStyle)) {
            $this->_XHTML.= ' style="' . $this->_ButtonStyle . '"';
        }

        if (!empty($this->_ButtonClass)) {
            $this->_XHTML.= ' class="' . $this->_ButtonClass . '"';
        }
        $this->_XHTML.= ">\n";

        $this->_XHTML.= '<a ';
        $this->_XHTML.= $this->BuildJSEvents();
        if (strpos($this->_Action, 'javascript: ') === false) {
            $this->_XHTML.= 'href="' . $this->_Action . '">';
        } else {
            $this->_XHTML.= 'href="javascript:void(0);" onclick="' . $this->_Action . '">';
        }
        $this->_XHTML.= '<img alt="' . $this->_Text . '" src="' . $this->_ImageSRC . '" width="48" height="48" />';
        $this->_XHTML.= "</a>\n";
        $this->_XHTML.= "</div>\n";

        $this->_XHTML.= '<span';
        if (!empty($this->_TextStyle)) {
            $this->_XHTML.= ' style="' . $this->_TextStyle . '"';
        }

        if (!empty($this->_TextClass)) {
            $this->_XHTML.= ' class="' . $this->_TextClass . '"';
        }
        $this->_XHTML.= '>';
        $this->_XHTML.= $this->_Text;
        $this->_XHTML.= "</span>\n";

        //      if (count($this->_ExtraActions) > 0) {
        //          $this->_XHTML.= "<br />\n";
        //          $this->_XHTML.= "<select>\n";
        //          foreach($this->_ExtraActions as $action)
        //              $this->_XHTML.= "<option value=\"{$action['action']}\">{$action['text']}</option>\n";
        //          //              $this->_XHTML.= "<a href=\"{$action['action']}\">{$action['text']}</a>&nbsp;";
        //          $this->_XHTML.= "</select>\n";
        //          $this->_XHTML.= "\n";
        //      }
        $this->_XHTML.= "</div>\n";
    }

}