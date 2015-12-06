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
    const NOTIFICATION_TYPE_EMAIL = 'email';
    const NOTIFICATION_TYPE_SMS = 'sms';

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Settings';

}
