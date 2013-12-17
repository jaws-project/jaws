<?php
/**
 * Comments Installer
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('order_type', '0'),
        array('allow_duplicate', 'no'),
        array('allow_comments', 'true'),
        array('comments_per_page', '10'),
        array('recent_comment_limit', '10'),
        array('default_comment_status', '1'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageComments',
        'ReplyComments',
        'Settings',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @param   bool    $upgrade_from_08x   Upgrade from 0.8.x
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install($upgrade_from_08x = false)
    {
        // Install listener for removing comments related to uninstalled gadget
        $this->gadget->event->insert('UninstallGadget');

        if ($upgrade_from_08x) {
            return $this->Upgrade('0.8.0', '1.0.0');
        } else {
            $result = $this->installSchema('schema.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Uninstall()
    {
        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        // Registry key
        $this->gadget->registry->insert('order_type', '0');
        return true;
    }

}