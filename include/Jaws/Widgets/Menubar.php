<?php
/**
 * Nice menubar for admin stuff
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Widgets_Menubar
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
    var $_CSS_Class_Name = 'gadget_menubar';

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $action Action short-name(NOT URL)
     * @return  void
     */
    function __construct($action = 'menubar')
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
    static function getInstance($action = 'menubar')
    {
        return new Jaws_Widgets_Menubar($action);
    }

    /**
     * Add a new option
     *
     * @access  public
     * @param   string  $action     Action short-name(NOT URL)
     * @param   string  $name       Title to print
     * @param   string  $url        URL to point
     * @param   string  $icon       Icon/Stock to use
     * @param   bool    $permission Display this option?
     * @return  object  Jaws_Widgets_Menubar object
     */
    function AddOption($action, $name, $url = '', $icon = '', $permission = true)
    {
        if ($permission) {
            $this->_Options[$action] = array(
                'action'   => strtolower($action),
                'name'     => $name,
                'url'      => empty($url)? 'javascript:void(0);' : $url,
                'icon'     => $icon,
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
    function Get()
    {
        $result = "\n". '<div id="'.$this->_CSS_Class_Name. '_'. $this->_Name.
            '" class="'. $this->_CSS_Class_Name. '">'. "<ul>\n";
        foreach ($this->_Options as $option) {
            $result.= '<li id="menu_option_'. $option['action']. '"';
            if ($option['selected']) {
                $result.= ' class="'. $this->_CSS_Class_Name. '_selected" ';
            }
            $result.= '><a href="'. $option['url']. '">';
            if (!empty($option['icon'])) {
                $result.= '<img alt="'. $option['name']. '" src="'. $option['icon']. '" width="16" height="16" /> ';
            }
            $result.= $option['name'] . "</a></li>\n";
        }

        $result.= "</ul></div>\n";
        return $result;
    }

}