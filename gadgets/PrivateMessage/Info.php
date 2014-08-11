<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetInfo
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const PRIVATEMESSAGE_FOLDER_INBOX = 1;
    const PRIVATEMESSAGE_FOLDER_DRAFT = 2;
    const PRIVATEMESSAGE_FOLDER_OUTBOX = 3;
    const PRIVATEMESSAGE_FOLDER_ARCHIVED = 4;
    const PRIVATEMESSAGE_FOLDER_TRASH = 5;
    const PRIVATEMESSAGE_FOLDER_NOTIFICATIONS = 6;

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
    var $default_action = 'PrivateMessage';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Properties';

}
