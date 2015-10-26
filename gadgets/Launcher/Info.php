<?php
/**
 * Launcher Gadget
 *
 * @category    GadgetInfo
 * @package     Launcher
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2006-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Launcher_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Script';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Display';

}