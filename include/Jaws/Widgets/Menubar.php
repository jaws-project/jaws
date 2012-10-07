<?php
/**
 * Nice menubar for admin stuff
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
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
    var $_CSS_Class_Name = 'jaws-menubar';

    /**
     * Main Constructor
     *
     * @access  public
     */
    function Jaws_Widgets_Menubar($name = 'menu')
    {
        $this->_Name = strtolower($name);
        $this->_Options = array();
    }

    /**
     * Add a new option
     *
     * @access   public
     * @param    string  $action Action's shorname(NOT URL)
     * @param    string  $name Title to print
     * @param    string  $url  Url to point
     * @param    string  $icon Icon/Stock to use
     * @param    string  $onclick Javascript OnClick function
     * @param    boolean $selected If the option is marked as selected
     */
    function AddOption($action, $name, $url = '', $icon = '', $selected = false, $onclick = null)
    {
        if (strpos($url, 'javascript:') !== false) {
            $onclick = str_replace('javascript:', '', $url);
            $url = '';
        }

        if (empty($url)) {
            $url = 'javascript:void(0);';
        }

        $this->_Options[$action] = array(
                                         'action'   => strtolower($action),
                                         'name'     => $name,
                                         'url'      => $url,
                                         'icon'     => $icon,
                                         'selected' => $selected,
                                         'onclick'  => $onclick
                                         );
    }

    /**
     * Select an option to make it active and others inactive
     *
     * @access  public
     * @param   string  $name  Actions's name to activate
     */
    function Activate($name)
    {
        if (isset($this->_Options[$name])) {
            $this->_Options[$name]['selected'] = true;
        }
    }

    /**
     * Set prefix css class name
     *
     * @access  public
     * @param   string  $class Prefix class's name
     */
    function SetClass($class)
    {
        $this->_CSS_Class_Name = strtolower($class);
    }

    /**
     * Build the menubar with its options
     *
     * @access  private
     */
    function Get()
    {
        $result = "\n". '<div id="'.$this->_CSS_Class_Name. '-'. $this->_Name. '" class="'. $this->_CSS_Class_Name. '">'. "<ul>\n";
        foreach ($this->_Options as $option) {
            $result.= '<li id="menu-option-'. $option['action']. '"';
            if ($option['selected']) {
                $result.= ' class="'. $this->_CSS_Class_Name. '-selected" ';
            }

            $result.= '>';
            if (empty($option['onclick'])) {
                $result.= '<a href="'. $option['url']. '">';
            } else {
                $result.= '<a href="'. $option['url']. '" onclick="'. $option['onclick']. '">';
            }

            if (!empty($option['icon'])) {
                $result.= '<img alt="'. $option['name']. '" src="'. $option['icon']. '" width="16" height="16" /> ';
            }

            $result.= $option['name'] . "</a></li>\n";
        }

        $result.= "</ul></div>\n";
        return $result;
    }

}