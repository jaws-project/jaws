<?php
/**
 * Logs Gadget
 *
 * @category   GadgetInfo
 * @package    Logs
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const LOGS_PRIORITY_INFO = 0;
    const LOGS_PRIORITY_NOTICE = 1;
    const LOGS_PRIORITY_WARNING = 2;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.1.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Logs';

}