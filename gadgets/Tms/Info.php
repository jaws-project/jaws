<?php
/**
 * TMS (Theme Management System) Gadget
 *
 * @category   GadgetInfo
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2007-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.1';

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
    var $default_admin_action = 'Themes';

}