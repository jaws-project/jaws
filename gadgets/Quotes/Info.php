<?php
/**
 * Quotes Gadget
 *
 * @category   GadgetInfo
 * @package    Quotes
 */
class Quotes_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const CLASSIFICATION_TYPE_PUBLIC = 1;
    const CLASSIFICATION_TYPE_INTERNAL = 2;
    const CLASSIFICATION_TYPE_RESTRICTED = 3;
    const CLASSIFICATION_TYPE_CONFIDENTIAL = 4;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Required gadgets
     *
     * @var     array
     * @access  private
     */
    var $requirement = array('Categories');

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'recentQuotes';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'quotes';
}