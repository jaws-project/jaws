<?php
/**
 * ComboImage.php - Combo Class that can manage images in it
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Combo.php';
require_once PIWI_PATH . '/Widget/Bin/ComboOption.php';

define('COMBOIMAGE_REQ_PARAMS', 2);
class ComboImage extends Combo
{
    /**
     * Combo data. The options of the Combo
     *
     * @var    array $_options
     * @access private
     * @see    addOption()
     */
    var $_options;

    /**
     * Default Width of the Image
     *
     * @var integer $_width
     * @access private
     * @see setImageSize()
     */
    var $_width;

    /**
     * Default Height of the Image
     *
     * @var integer $_height
     * @access private
     * @see setImageSize()
     */
    var $_height;

    /**
     * Inner combo padding
     *
     * @var integer $_padding
     * @access private
     */
    var $_padding;

    /**
     * Public constructor
     *
     * @param   string Name of the combo
     * @param   string Data of the combo
     * @param   string Title of the combo
     * @param   string Widget ID
     * @access  public
     */
    function __construct($name, $title = '', $id = '')
    {
        $this->_name    = $name;
        $this->_title   = $title;
        $this->_id      = $id;
        $this->_options = array();
        $this->setImageSize(16, 16);

        $this->_availableEvents = array('onchange', 'onclick', 'ondblclick', 'onmousedown',
                                        'onmouseup', 'onmouseover', 'onmousemove',
                                        'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup');
        
        $oddClass = Piwi::getVarConf('CLASS_ODD');
        if (empty($oddClass)) {
            $oddClass = 'piwi_option_odd';
        }
        $this->setOddClass($oddClass);

        $evenClass = Piwi::getVarConf('CLASS_EVEN');
        if (empty($evenClass)) {
            $evenClass = 'piwi_option_even';
        }
        $this->setEvenClass($evenClass);

        parent::init();
    }

    /**
     * Set Image Width and Height
     *
     * @param integer $width Image width
     * @param integer $height Image height
     *
     * @access public
     */
    function setImageSize($width, $height)
    {
        $this->_width   = $width;
        $this->_height  = $height;
        $this->_padding = round($this->_height/8);
    }

    /**
     * Add a new Option to the combo
     *
     * @param   string $text  The text of the option
     * @param   string $value The value of the option
     * @param   string $image The image we want to be displayed with it
     * @param   boolean $isdisabled Sometimes a option can be disabled by default
     * @param   string $class The class of the option
     * @param   string $style The style of the option
     *
     * @access  public
     */
    function addOption($text, $value, $image, $isdisabled = false, $class = '', $style = '')
    {
        $imagestyle = "background-image: url('".$image."'); background-repeat: no-repeat; ".
            "background-position: center left; padding: ".$this->_padding."px; padding-left:".($this->_width + 4)."px;";

        if (empty($style)) {
            $style = $imagestyle;
        } else {
            $style = $imagestyle . ' ' . $style;
        }

        if ($this->_style == '') {
            $this->SetStyle ("background-image: url('".$image."'); background-repeat: no-repeat; ".
                         "background-position: center left; padding-left:".($this->_width + 4)."px;");
        }


        $this->_options[$value] = new ComboOption($value, $text, null, false, $isdisabled, $class, $style);
    }

    /**
     * Set a key as the selected one
     *
     * @param   string $default Set the default key as the selected one
     * @access  public
     */
    function setDefault($key)
    {
        if (isset($this->_options[$key])) {
            $this->_options[$key]->select();
            $this->setStyle(str_replace(' padding: ' . $this->_padding . 'px;', '', $this->_options[$key]->getStyle()));
        } else {
            if (count($this->_options) > 0) {
                foreach ($this->_options as $option) {
                    $value = $option->getValue();
                    if ($option->getText() == $key) {
                        $this->_options[$value]->select();
                        $this->setStyle(str_replace(' padding: ' . $this->_padding . 'px;', '', $option->getStyle()));
                    } else {
                        $this->_options[$value]->select (false);
                    }
                }
            }
        }
    }

    /**
     * Set the value of the widget
     *
     * @aram    string   $value  Widget value
     * @access   public
     */
    function setValue($value)
    {
        $this->setDefault($value);
    }

    /**
     * Build the XHTML data
     *
     * @access  private
     */
    function buildXHTML()
    {
        $this->addEvent(new JSEvent(ON_CHANGE, "this.style.backgroundImage = this[this.selectedIndex].style.backgroundImage"));
        parent::buildXHTML();
/*
        $changeStyleScript = "<script type=\"text/javascript\">\n";
        $changeStyleScript .= "Combo_{$this->_id} = document.getElementById('{$this->_id}');\n";
        $changeStyleScript .= "Combo_{$this->_id}.style.backgroundImage = Combo_{$this->_id}[Combo_{$this->_id}.selectedIndex].style.backgroundImage;\n";
        $changeStyleScript .= "</script>\n";
        $this->_XHTML .= $changeStyleScript;
*/
    }
}
?>
