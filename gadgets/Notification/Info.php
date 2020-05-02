<?php
/**
 * Notification Gadget
 *
 * @category    GadgetInfo
 * @package     Notification
 */
class Notification_Info extends Jaws_Gadget
{

    /**
     * Constants
     */
    const NOTIFICATION_MESSAGE_TYPE_EMAIL = 1;
    const NOTIFICATION_MESSAGE_TYPE_SMS = 2;
    const NOTIFICATION_MESSAGE_TYPE_WEB = 3;

    const NOTIFICATION_MESSAGE_STATUS_NOT_SEND = 1;
    const NOTIFICATION_MESSAGE_STATUS_SENDING = 2;
    const NOTIFICATION_MESSAGE_STATUS_SENT = 3;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '2.4.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Messages';

}
