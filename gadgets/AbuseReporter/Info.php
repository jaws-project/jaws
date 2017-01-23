<?php
/**
 * AbuseReporter Information
 *
 * @category    GadgetModel
 * @package     AbuseReporter
 */
class AbuseReporter_Info extends Jaws_Gadget
{

    /**
     * Constants
     */
    const PRIORITY_VERY_HIGH = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 4;
    const PRIORITY_VERY_LOW = 5;

    const STATUS_NOT_CHECKED = 1;
    const STATUS_CHECKED = 2;

    const TYPE_ABUSE = 1;
    const TYPE_FRAUD = 2;
    const TYPE_VIRUS = 3;
    const TYPE_SPAM = 4;
    const TYPE_OTHER = 5;

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
    var $default_action = 'Report';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Reports';

}