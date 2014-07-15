<?php
/**
 * Notification Gadget
 *
 * @category    GadgetInfo
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notification_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const NOTIFICATION_DISABLED = 0;
    const NOTIFICATION_CRITICAL = 1;
    const NOTIFICATION_WARNING = 2;
    const NOTIFICATION_NOTICE = 3;
    const NOTIFICATION_INFO = 4;
    const NOTIFICATION_ROUTIN = 5;

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
    var $default_action = 'Notification';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Settings';

}