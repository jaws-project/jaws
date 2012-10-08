<?php
/**
 * Creates a menu like behaviour via div and ul/li, sub menus are supported
 *
 * @category   Widget
 * @package    Core
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Jaws_Widgets_XHTMLMenu
{
    /**
     * @access  private
     * @var     array
     * @see     function  addOption
     */
    var $_options = array();
    var $_Name;
    var $_id;
    var $_style;
    var $_selected = array();

    function Jaws_Widgets_XHTMLMenu($name = '', $id = 'nav', $style = '')
    {
        $this->_Name  = $name;
        $this->_id    = $id;
        $this->_style = $style;
    }

    /**
     * Add a new option
     *
     * @access  public
     * @param   string  ACtion
     * @param   string  Title to print
     * @param   string  Url to point
     * @param   string  Icon/Stock to use
     * @param   bool    If the option is marked as selected
     * @param   strings Access key
     */
    function addOption($action, $name, $url = '', $icon = '', $selected = false, $accesskey = '', $icon_bypass = false)
    {
        $action = strtolower($action);
        $this->_options[$action] = array(
            'name'        => $name,
            'url'         => $url,
            'icon'        => $icon,
            'selected'    => $selected,
            'accesskey'   => $accesskey,
            'icon_bypass' => $icon_bypass
        );
    }

    function addSubOption($action, $subs)
    {
        $action = strtolower($action);
        $this->_options[$action]['sub'] = $subs;
    }

    /**
     * Select an option to make it active "open"
     *
     * @access  public
     * @param   string  $name  Actions's name to activate
     */
    function activate($name, $action = '')
    {
        $name   = strtolower($name);
        $action = strtolower($action);
        if (!empty($action)) {
            $this->_selected[$name][$action] = true;
        }  else {
            $this->_selected[$name] = true;
        }
    }

    /**
     * Processes each item
     *
     * Options include:
     *  - url
     *  - icon
     *  - accesskey
     *  - name
     *  - sub (for sub items) array
     *     Under sub:
     *      - items ... Can contain any of above
     *
     * @access  protected
     */
    function _process($action, $option, $name, $level, $parent = '')
    {
        $name   = strtolower($name);
        $parent = strtolower($parent);

        $menu = '  <li id="' . $action;
        if (isset($this->_selected[$name]) && $this->_selected[$name]) {
            $menu .= ' open';
        }
        $menu .= '">';

        if (!empty($option['url'])) {
            $menu .= '<a href="' . $option['url'] . '"';
        }

        if (
            (isset($this->_selected[$name]) && $this->_selected[$name])
            || (isset($this->_selected[$parent][$name]) && $this->_selected[$parent][$name])
        ) {
            $menu .= ' class="current"';
        }


        if (!empty($option['accesskey'])) {
            $menu .= ' accesskey="' . $option['accesskey'] . '"';
        }

        if (!empty($option['url'])) {
            $menu .= '>';
        }

        if (
            !empty($option['icon'])
            && ($option['icon_bypass'] === true || ($option['icon_bypass'] === false && file_exists($option['icon'])))
        ) {
            $menu .= '<img alt="' . $option['name'] . '" ';
            $menu .= 'src="' . $option['icon'] . '" height="16" width="16" title="' . $option['name'] . '" />&nbsp;';
        }

        $menu .= $option['name'];

        if (!empty($option['url'])) {
            $menu .= '</a>';
        }

        if (isset($option['sub']) && is_array($option['sub'])) {
            $level++;
            $menu .= "\n" . '  <ul>' . "\n";
            if (isset($option['sub']['items'])) {
                $items = $option['sub']['items'];
            } else {
                $items = $option['sub'];
            }

            foreach ($items as $action => $op) {
                $menu .= $this->_process($action, $op, $action, $level, $name);
            }
            $menu .= '  </ul>' . "\n";
        }

        $menu .= "</li>\n";

        return $menu;
    }

    /**
     * Build the menubar with its options
     *
     * @access  public
     */
    function get()
    {
        $menu = "\n" . '<div';
        if (!empty($this->_id)) {
            $menu.= ' id="' . $this->_id . '"';
        }

        if (!empty($this->_style)) {
            $menu.= ' style="' . $this->_style . '"';
        }

        $menu.= '>' . "\n";

        if (!empty($this->_Name)) {
            $menu .= '<h3>' . $this->_Name . "</h3>\n";
        }
        $menu .= ' <ul>' . "\n";

        foreach ($this->_options as $action => $option) {
            if (
                isset($option['sub']) && isset($this->_selected[$action])
                && $this->_selected[$action] === true
            ) {
                $default = strtolower($option['sub']['actions']['default']);
                $this->_selected[$action] = array($default => true);
            }

            $menu .= $this->_process($action, $option, $action, 0);
        }

        $menu .= " </ul>\n</div>\n";

        return $menu;
    }
}