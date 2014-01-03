<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetInfo
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Info extends Jaws_Gadget
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
    var $default_action = 'Display';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Emblems';

}