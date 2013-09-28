<?php
/**
 * Directory gadget info
 *
 * @category    GadgetInfo
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.0.1';

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