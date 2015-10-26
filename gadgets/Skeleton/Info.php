<?php
/**
 * SkeletonInfo.php Skeleton Information
 *
 * @category   GadgetModel
 * @package    Skeleton
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.6.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Display';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Display';

}