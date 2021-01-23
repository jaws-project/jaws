<?php
/**
 * AbuseReporter Information
 *
 * @category    GadgetModel
 * @package     AbuseReporter
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2017-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AbuseReporter_Info extends Jaws_Gadget
{

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