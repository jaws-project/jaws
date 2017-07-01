<?php
/**
 * EventsCalendar gadget info
 *
 * @category    GadgetInfo
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Info extends Jaws_Gadget
{
    /**
     * Default ACL value of frontend gadget access
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = false;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.2.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'ViewYear';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'PublicEvents';
}