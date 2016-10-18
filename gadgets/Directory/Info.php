<?php
/**
 * Directory gadget info
 *
 * @category    GadgetInfo
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Info extends Jaws_Gadget
{
    const FILE_TYPE_UNKNOWN = 0;
    const FILE_TYPE_TEXT    = 1;
    const FILE_TYPE_IMAGE   = 2;
    const FILE_TYPE_AUDIO   = 3;
    const FILE_TYPE_VIDEO   = 4;
    const FILE_TYPE_ARCHIVE = 5;

    /**
     * Default ACL value of frontend gadget access
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
    var $version = '1.1.0';

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