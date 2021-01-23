<?php
/**
 * Comments Information
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const COMMENTS_STATUS_APPROVED = 1;
    const COMMENTS_STATUS_WAITING  = 2;
    const COMMENTS_STATUS_SPAM     = 3;
    const COMMENTS_STATUS_PRIVATE  = 4;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.6.0';

    /**
     * Is this gadget core gadget?
     *
     * @var     boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'RecentComments';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Comments';

}