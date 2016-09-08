<?php
/**
 * Poll Gadget
 *
 * @category   GadgetInfo
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Info extends Jaws_Gadget
{

    /**
     * Constants
     */
    const POLL_RESTRICTION_TYPE_FREE = 0;
    const POLL_RESTRICTION_TYPE_IP = 1;
    const POLL_RESTRICTION_TYPE_USER = 2;
    const POLL_RESTRICTION_TYPE_SESSION = 3;

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
    var $default_action = 'Polls';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Polls';
}