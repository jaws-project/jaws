<?php
/**
 * Directory gadget info
 *
 * @category    GadgetInfo
 * @package     Directory
 */
class Directory_Info extends Jaws_Gadget
{
    const FILE_TYPE_UNKNOWN = 1;
    const FILE_TYPE_TEXT    = 2;
    const FILE_TYPE_IMAGE   = 3;
    const FILE_TYPE_AUDIO   = 4;
    const FILE_TYPE_VIDEO   = 5;
    const FILE_TYPE_ARCHIVE = 6;

    /**
     * Default ACL value of front-end gadget access
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = false;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.4.0';

    /**
     * Recommended gadgets
     *
     * @var     array
     * @access  public
     */
    var $recommended = array('Comments', 'Tags', 'Rating');

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Directory';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Directory';
}