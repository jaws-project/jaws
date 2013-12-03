<?php
/**
 * Nice ButtonBar for admin stuff
 *
 * @category   Widget
 * @package    Core
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Zehne Ziba Sabz Co.
 * @license    http://www.zehneziba.ir/page/license.html
 */
class Jaws_Widgets_ButtonBar
{
    /**
     * @access  private
     * @var     array
     * @see     function  AddOption
     */
    var $_Options;

    /**
     * Menu bar name
     *
     * @access  private
     * @var     string
     */
    var $_Name;

    /**
     * css class name
     *
     * @access  private
     * @var     string
     */
    var $_CSS_Class_Name = 'gadget_buttonbar';

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $action Action short-name(NOT URL)
     * @return  void
     */
    function __construct($action = 'buttonbar')
    {
        $this->_Name = strtolower($action);
        $this->_Options = array();
    }

    /**
     * Creates the Jaws_Widgets_Menubar instance
     *
     * @access  public
     * @param   string  $action Action short-name(NOT URL)
     * @return  object  returns the instance
     */
    function getInstance($action = 'buttonbar')
    {
        return new Jaws_Widgets_Menubar($action);
    }

    /**
     * Add a new option
     *
     * @access  public
     * @param   string  $action         Action short-name(NOT URL)
     * @param   string  $name           Title to print
     * @param   string  $url            URL to point
     * @param   string  $icon           Icon/Stock to use
     * @param   int     $size           Icon size
     * @param   bool    $permission     Display this option?
     * @return  object  Jaws_Widgets_Buttonbar object
     */
    function AddOption($action, $name, $url = '', $icon = '', $size = 3, $permission = true)
    {
        if ($permission) {
            $this->_Options[$action] = array(
                'action' => strtolower($action),
                'name' => $name,
                'url' => empty($url) ? 'javascript:void(0);' : $url,
                'icon' => $icon,
                'size' => $size,
                'selected' => false
            );
        }

        return $this;
    }

    /**
     * Add a new options
     *
     * @access  public
     * @param   array   $options    Options array
     * @return  object  Jaws_Widgets_Menubar object
     */
    function AddOptions($options = array())
    {
        foreach ($options as $option) {
            call_user_func_array(array($this, 'AddOption'), $option);
        }

        return $this;
    }

    /**
     * Select an option to make it active and others inactive
     *
     * @access  public
     * @param   string  $name  Actions's name to activate
     * @return  object  Jaws_Widgets_Menubar object
     */
    function Activate($name)
    {
        if (isset($this->_Options[$name])) {
            $this->_Options[$name]['selected'] = true;
        }

        return $this;
    }

    /**
     * Set prefix css class name
     *
     * @access  public
     * @param   string  $class Prefix class's name
     * @return  object  Jaws_Widgets_Menubar object
     */
    function SetClass($class)
    {
        $this->_CSS_Class_Name = strtolower($class);
        return $this;
    }

    /**
     * Build the menubar with its options
     *
     * @access  private
     * @return  string
     */
    private function GetJavaScript()
    {
        $result = '<script type="text/javascript">';

        $result .= "jQuery('.gadget_buttonbar a').mouseenter(function(e) {";
        $result .= "jQuery(this).children('div .over-img').animate({opacity: 0}, 500);";
        $result .= "jQuery(this).find('.over-items i').animate({fontSize: '+=1em'}, 400);";
        $result .= "jQuery(this).find('.over-items i').animate({color: '#3465A4'}, 400);";
        $result .= "});";

        $result .= "jQuery('.gadget_buttonbar a').mouseleave(function (e) {";
        $result .= "jQuery(this).children('div .over-img').animate({opacity: 1}, 500);";
        $result .= "jQuery(this).find('.over-items i').animate({fontSize: '-=1em'}, 400);";
        $result .= "jQuery(this).find('.over-items i').animate({color: '#8D7E7E'}, 400);";
        $result .= "});";

        $result .= '</script>';
        return $result;

    }
    /**
     * Build the menubar with its options
     *
     * @access  private
     * @return  string
     */
    function Get()
    {
        $result = "\n". '<div id="'.$this->_CSS_Class_Name. '_'. $this->_Name.
            '" class="'. $this->_CSS_Class_Name. '">'. "<ul>\n";

        foreach ($this->_Options as $option) {
            $result.= '<li id="menu_option_'. $option['action']. '"';
            if ($option['selected']) {
                $result .= ' class="' . $this->_CSS_Class_Name . '_selected" ';
            }
            $result.= '><a href="'. $option['url']. '">';
            $result.= '<div class="over-img"></div>';
            $result.= '<div class="over-items">';
            if (!empty($option['icon'])) {
                $result.= '<i class="icon-' . $option['icon'] .' icon-' . $option['size'] .'x button-icon"></i>';
            } else {
                $result.= '<i class="button-icon"></i>';
            }

            $result.= '<div class="text">' . $option['name'] . "</div></div></a></li>\n";
        }
//        foreach ($this->_Options as $option) {
//            $result.= '<li id="menu_option_'. $option['action']. '"';
//            if ($option['selected']) {
//                $result.= ' class="'. $this->_CSS_Class_Name. '_selected" ';
//            }
//            $result.= '><a href="'. $option['url']. '">';
//            if (!empty($option['icon'])) {
//                $result.= '<img alt="'. $option['name']. '" src="'. $option['icon']. '" width="16" height="16" /> ';
//            }
//            $result.= $option['name'] . "</a></li>\n";
//        }

        $result.= "</ul></div>\n";
        $result .= $this->GetJavaScript();

        return $result;
    }

}