<?php
/**
 * Logs Gadget
 *
 * @category    GadgetInfo
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.4.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'LoginHistory';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Logs';

}