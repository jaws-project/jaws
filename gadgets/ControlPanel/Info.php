<?php
/**
 * ControlPanel Core Gadget
 *
 * @category   GadgetInfo
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanelInfo extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.8.0';

    /**
     * Is this gadget core gadget?
     *
     * @var     boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * @var     boolean
     * @access  private
     */
    var $_has_layout = false;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'Backup',
    );

}