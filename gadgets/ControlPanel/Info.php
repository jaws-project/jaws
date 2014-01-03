<?php
/**
 * ControlPanel Core Gadget
 *
 * @category   GadgetInfo
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Is this gadget core gadget?
     *
     * @var     boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'DefaultAction';

}