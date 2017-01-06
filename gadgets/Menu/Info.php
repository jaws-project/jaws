<?php
/**
 * Menu gadget info
 *
 * @category    GadgetInfo
 * @package     Menu
 */
class Menu_Info extends Jaws_Gadget
{
    const STATUS_ANONYMOUS = 2;
    const STATUS_LOGGED_IN = 3;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.5.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = true;

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Menu';

}