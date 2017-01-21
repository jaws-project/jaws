<?php
/*
 * MenuItem.php - MenuItem Class
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('MENUITEM_REQ_PARAMS', 1);
class MenuItem extends Container
{
    /**
     * The action to execute
     *
     * @var     array $_action
     * @access  private
     * @see     setAction
     *
     */
    var $_action;

    /**
     * The icon to use
     *
     * @var     array $_icon
     * @access  private
     * @see     setIcon
     *
     */
    var $_icon;

    /**
     * Comment to display in the statusbar
     *
     * @var     array $_comment
     * @access  private
     * @see     setComment
     *
     */
    var $_comment;

    /**
     * Public constructor
     *
     * @param    string   $value  The Name (title)
     * @param    string   $action The action to execute when it gets a mouse click
     * @param    strng    $icon   If you want to add an image to the MenuItem
     * @access   public
     */
    function __construct($value, $action = '', $icon = '')
    {
        $this->_value  = $value;
        $this->_action = $action;
        $this->_icon   = $icon;
        $this->childs  = array();

        parent::init();
    }

    /**
     * Get the icon
     *
     * @access public
     */
    function getIcon()
    {
        return $this->_icon;
    }

    /**
     * Get the action
     *
     * @access public
     */
    function getAction()
    {
        return $this->_action;
    }

    /**
     * Add a menu item to the items array
     *
     * @access   public
     */
    function add($item, $url, $icon)
    {
        $this->_items[$item] = new MenuItem($item, $url, $icon);
    }
}
?>