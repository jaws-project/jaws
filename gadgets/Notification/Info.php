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
    const MESSAGE_TYPE_EMAIL = 1;
    const MESSAGE_TYPE_SMS = 2;
    const MESSAGE_TYPE_WEB = 3;
    const MESSAGE_TYPE_APP = 4;

    const MESSAGE_STATUS_PENDING = 1;
    const MESSAGE_STATUS_SENDING = 2;
    const MESSAGE_STATUS_SENT = 3;
    const MESSAGE_STATUS_EXPIRED = 4;
    const MESSAGE_STATUS_REJECTED = 5;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '3.4.0';

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
