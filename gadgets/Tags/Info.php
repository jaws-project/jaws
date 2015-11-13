<?php
/**
 * Tags Gadget
 *
 * @category   GadgetInfo
 * @package    Tags
 */
class Tags_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Tags';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Tags';

}